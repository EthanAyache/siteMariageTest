<?php
// GET ?token=XXX → l'invité correspondant, pour le RSVP du site public.
// Sans connaître le token, impossible de lire quoi que ce soit.
require __DIR__ . '/_lib.php';

methode('GET');
$st = db()->prepare(
    'SELECT nom, places, rsvp, mairie, nb_mairie, houppa, nb_houppa, message
     FROM invites WHERE token = ?'
);
$st->execute([(string) ($_GET['token'] ?? '')]);
$invite = $st->fetch();

if (!$invite) erreur(404, 'Invitation introuvable');
reponse(200, ['invite' => $invite]);
