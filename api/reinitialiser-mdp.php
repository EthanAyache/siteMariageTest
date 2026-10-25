<?php
// POST { token, mot_de_passe } → remplace le mot de passe via le lien reçu par e-mail.
require __DIR__ . '/_lib.php';

methode('POST');
$donnees = corps_json();
$token = (string) ($donnees['token'] ?? '');
$mdp   = (string) ($donnees['mot_de_passe'] ?? '');

if (mb_strlen($mdp) < 8) erreur(400, 'Le mot de passe doit faire au moins 8 caractères.');

$st = db()->prepare(
    'SELECT compte_id FROM reinitialisations_mdp
     WHERE token_hash = ? AND utilise_le IS NULL AND expire_le > NOW()'
);
$st->execute([hash('sha256', $token)]);
$compteId = $st->fetchColumn();

if (!$compteId) erreur(400, 'Ce lien est invalide ou a expiré. Faites une nouvelle demande.');

$pdo = db();
$pdo->beginTransaction();
$pdo->prepare('UPDATE comptes SET mot_de_passe = ? WHERE id = ?')
    ->execute([password_hash($mdp, PASSWORD_DEFAULT), $compteId]);
// le lien ne resservira pas, ni les autres liens encore valides de ce compte
$pdo->prepare('UPDATE reinitialisations_mdp SET utilise_le = NOW() WHERE compte_id = ? AND utilise_le IS NULL')
    ->execute([$compteId]);
$pdo->commit();

reponse(200, ['ok' => true]);
