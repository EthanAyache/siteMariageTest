<?php
// POST { email } → envoie un lien de réinitialisation valable 1 h.
//
// La réponse est la même que l'adresse existe ou non : sinon ce formulaire
// permettrait de tester quelles adresses ont un compte.
require __DIR__ . '/_lib.php';

methode('POST');
$donnees = corps_json();
$email = trim((string) ($donnees['email'] ?? ''));

$siteUrl = rtrim(cfg('SITE_URL'), '/');
if ($siteUrl === '') {
    // pas de repli sur l'en-tête Host : il est choisi par le visiteur, et un
    // attaquant pourrait faire pointer le lien envoyé vers son propre site
    throw new RuntimeException('SITE_URL manquant dans le .env');
}

$st = db()->prepare('SELECT id, nom, email FROM comptes WHERE email = ?');
$st->execute([$email]);
$compte = $st->fetch();

if ($compte) {
    // un seul e-mail toutes les 2 minutes par compte, contre le spam
    $st = db()->prepare(
        'SELECT COUNT(*) FROM reinitialisations_mdp
         WHERE compte_id = ? AND created_at > NOW() - INTERVAL 2 MINUTE'
    );
    $st->execute([$compte['id']]);

    if ((int) $st->fetchColumn() === 0) {
        $token = bin2hex(random_bytes(32));

        // un nouveau lien annule les précédents
        db()->prepare('DELETE FROM reinitialisations_mdp WHERE compte_id = ? AND utilise_le IS NULL')
            ->execute([$compte['id']]);
        db()->prepare(
            'INSERT INTO reinitialisations_mdp (compte_id, token_hash, expire_le)
             VALUES (?, ?, NOW() + INTERVAL 1 HOUR)'
        )->execute([$compte['id'], hash('sha256', $token)]);

        $lien = $siteUrl . '/reinitialiser.html?token=' . $token;
        $texte = "Bonjour {$compte['nom']},\n\n"
            . "Une demande de nouveau mot de passe a été faite pour l'administration du site de mariage.\n\n"
            . "Pour choisir un nouveau mot de passe, ouvrez ce lien (valable 1 heure) :\n$lien\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail : votre mot de passe ne change pas.\n";

        if (!envoyer_mail($compte['email'], 'Réinitialisation de votre mot de passe', $texte)) {
            // en local (WAMP sans SMTP), le lien n'est visible que dans le log PHP
            error_log("[api mariage] e-mail non envoyé à {$compte['email']}, lien : $lien");
        }
    }
}

reponse(200, ['ok' => true]);
