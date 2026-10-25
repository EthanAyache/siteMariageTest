<?php
// POST { token, mairie, nb_mairie, houppa, nb_houppa, message } → réponse de l'invité.
require __DIR__ . '/_lib.php';

methode('POST');
$d = corps_json();
$token  = (string) ($d['token'] ?? '');
$mairie = $d['mairie'] ?? null;
$houppa = $d['houppa'] ?? null;

$st = db()->prepare('SELECT places FROM invites WHERE token = ?');
$st->execute([$token]);
$places = $st->fetchColumn();
if ($places === false) erreur(404, 'Invitation introuvable');

if (!in_array($mairie, ['oui', 'non'], true) || !in_array($houppa, ['oui', 'non'], true)) {
    erreur(400, 'Réponse invalide');
}

// on borne le nombre de personnes au quota de l'invité
$borner = function ($reponse, $nb) use ($places) {
    return $reponse === 'oui' ? min(max((int) $nb, 1), (int) $places) : 0;
};

db()->prepare(
    'UPDATE invites SET mairie = ?, nb_mairie = ?, houppa = ?, nb_houppa = ?, message = ?, rsvp = ?
     WHERE token = ?'
)->execute([
    $mairie, $borner($mairie, $d['nb_mairie'] ?? 0),
    $houppa, $borner($houppa, $d['nb_houppa'] ?? 0),
    mb_substr((string) ($d['message'] ?? ''), 0, 1000),
    ($mairie === 'non' && $houppa === 'non') ? 'decline' : 'confirme',
    $token,
]);

reponse(200, ['ok' => true]);
