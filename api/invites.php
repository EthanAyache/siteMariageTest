<?php
// Gestion des invités depuis le tableau de bord (comptes admin et mariés).
//   GET                          → liste complète
//   POST   { nom, places }       → ajout (le token est créé ici)
//   PUT    { token, nom, places }→ modification
//   DELETE { token }             → suppression
require __DIR__ . '/_lib.php';

$m = methode('GET', 'POST', 'PUT', 'DELETE');
exiger_connexion();

if ($m === 'GET') {
    $invites = db()->query('SELECT * FROM invites ORDER BY nom')->fetchAll();
    reponse(200, ['invites' => $invites]);
}

$donnees = corps_json();

function lire_nom_places(array $donnees): array
{
    $nom = trim((string) ($donnees['nom'] ?? ''));
    $places = filter_var($donnees['places'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
    if ($nom === '') erreur(400, 'Le nom est obligatoire.');
    if ($places === false) erreur(400, 'Le nombre de places doit être entre 1 et 50.');
    return [$nom, $places];
}

if ($m === 'POST') {
    [$nom, $places] = lire_nom_places($donnees);
    $token = bin2hex(random_bytes(8));
    db()->prepare('INSERT INTO invites (nom, places, token) VALUES (?, ?, ?)')
        ->execute([$nom, $places, $token]);
    reponse(201, ['token' => $token]);
}

$token = (string) ($donnees['token'] ?? '');

if ($m === 'PUT') {
    [$nom, $places] = lire_nom_places($donnees);
    $st = db()->prepare('UPDATE invites SET nom = ?, places = ? WHERE token = ?');
    $st->execute([$nom, $places, $token]);
    reponse(200, ['ok' => true]);
}

// DELETE
db()->prepare('DELETE FROM invites WHERE token = ?')->execute([$token]);
reponse(200, ['ok' => true]);
