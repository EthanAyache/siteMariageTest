<?php
// POST { email, mot_de_passe } → ouvre la session d'un compte admin ou marié.
require __DIR__ . '/_lib.php';

methode('POST');
$donnees = corps_json();
$email = trim((string) ($donnees['email'] ?? ''));
$mdp   = (string) ($donnees['mot_de_passe'] ?? '');

$st = db()->prepare('SELECT id, nom, role, mot_de_passe FROM comptes WHERE email = ?');
$st->execute([$email]);
$compte = $st->fetch();

if (!$compte || !password_verify($mdp, $compte['mot_de_passe'])) {
    usleep(500000);   // ralentit les essais de mots de passe à la chaîne
    erreur(401, 'Identifiants incorrects');
}

if (password_needs_rehash($compte['mot_de_passe'], PASSWORD_DEFAULT)) {
    db()->prepare('UPDATE comptes SET mot_de_passe = ? WHERE id = ?')
        ->execute([password_hash($mdp, PASSWORD_DEFAULT), $compte['id']]);
}
db()->prepare('UPDATE comptes SET derniere_connexion = NOW() WHERE id = ?')->execute([$compte['id']]);

demarrer_session();
session_regenerate_id(true);
$_SESSION['compte_id'] = (int) $compte['id'];

reponse(200, ['nom' => $compte['nom'], 'role' => $compte['role']]);
