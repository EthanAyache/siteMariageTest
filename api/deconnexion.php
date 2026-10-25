<?php
// POST → ferme la session.
require __DIR__ . '/_lib.php';

methode('POST');
corps_json();
demarrer_session();
$_SESSION = [];
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/']);
session_destroy();

reponse(200, ['ok' => true]);
