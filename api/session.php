<?php
// GET → le compte connecté, ou 401 si personne n'est connecté.
require __DIR__ . '/_lib.php';

methode('GET');
$compte = exiger_connexion();
reponse(200, ['nom' => $compte['nom'], 'email' => $compte['email'], 'role' => $compte['role']]);
