<?php
// ============================================================================
//  Crée un compte d'accès à l'administration, ou change son mot de passe
//  s'il existe déjà.
//
//    php scripts/creer-compte.php <email> "<Nom>" <admin|marie>
//
//  Le mot de passe est demandé ensuite (au moins 8 caractères).
//
//  Avec --sql à la fin, rien n'est écrit dans la base : le script affiche la
//  requête à coller dans l'onglet SQL du phpMyAdmin de l'hébergeur.
// ============================================================================
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/api/_lib.php';
set_exception_handler(null);

$args = array_slice($argv, 1);
$sql = in_array('--sql', $args, true);
$args = array_values(array_diff($args, ['--sql']));
[$email, $nom, $role] = $args + [null, null, null];

if (!$email || !$nom || !in_array($role, ['admin', 'marie'], true)) {
    fwrite(STDERR, "Usage : php scripts/creer-compte.php <email> \"<Nom>\" <admin|marie> [--sql]\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Adresse e-mail invalide : $email\n");
    exit(1);
}

echo 'Mot de passe (visible à l\'écran) : ';
$mdp = trim((string) fgets(STDIN));
if (mb_strlen($mdp) < 8) {
    fwrite(STDERR, "Le mot de passe doit faire au moins 8 caractères.\n");
    exit(1);
}
$hash = password_hash($mdp, PASSWORD_DEFAULT);

if ($sql) {
    $q = function ($v) { return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $v) . "'"; };
    echo "\nÀ coller dans phpMyAdmin (base « mariage », onglet SQL) :\n\n"
        . 'INSERT INTO comptes (email, nom, role, mot_de_passe) VALUES ('
        . implode(', ', array_map($q, [$email, $nom, $role, $hash])) . ")\n"
        . "  ON DUPLICATE KEY UPDATE nom = VALUES(nom), role = VALUES(role), mot_de_passe = VALUES(mot_de_passe);\n";
    exit;
}

db()->prepare(
    'INSERT INTO comptes (email, nom, role, mot_de_passe) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE nom = VALUES(nom), role = VALUES(role), mot_de_passe = VALUES(mot_de_passe)'
)->execute([$email, $nom, $role, $hash]);

echo "Compte $role prêt pour $email.\n";
