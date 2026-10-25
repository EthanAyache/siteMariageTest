<?php
// ============================================================================
//  Fonctions partagées par l'API : configuration (.env), connexion MySQL,
//  session des comptes admin / mariés et réponses JSON.
// ============================================================================

// Toute erreur imprévue devient un 500 générique : on ne montre jamais le
// détail (requête SQL, identifiants) au navigateur, il part dans les logs.
set_exception_handler(function (Throwable $e) {
    error_log('[api mariage] ' . $e);
    erreur(500, 'Erreur serveur');
});

// ── Configuration ───────────────────────────────────────────────────────────
// Les variables d'environnement de l'hébergeur passent avant le fichier .env.
function cfg(string $cle, string $defaut = ''): string
{
    static $fichier = null;
    if ($fichier === null) {
        $fichier = [];
        $chemin = dirname(__DIR__) . '/.env';
        if (is_readable($chemin)) {
            foreach (file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
                $ligne = trim($ligne);
                if ($ligne === '' || $ligne[0] === '#' || strpos($ligne, '=') === false) continue;
                [$k, $v] = explode('=', $ligne, 2);
                $fichier[trim($k)] = trim(trim($v), "\"'");
            }
        }
    }
    $env = getenv($cle);
    if ($env !== false && $env !== '') return $env;
    return $fichier[$cle] ?? $defaut;
}

// ── Base de données ─────────────────────────────────────────────────────────
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            cfg('DB_HOST', '127.0.0.1'), cfg('DB_PORT', '3306'), cfg('DB_NAME')
        );
        $pdo = new PDO($dsn, cfg('DB_USER'), cfg('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // les entiers reviennent en entiers
        ]);
    }
    return $pdo;
}

// ── Requêtes et réponses ────────────────────────────────────────────────────
function reponse(int $code, array $donnees): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

function erreur(int $code, string $message): void
{
    reponse($code, ['erreur' => $message]);
}

function methode(string ...$autorisees): string
{
    $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($m, $autorisees, true)) {
        header('Allow: ' . implode(', ', $autorisees));
        erreur(405, 'Méthode non autorisée');
    }
    return $m;
}

// Exiger du JSON bloque aussi les envois de formulaires depuis un autre site
// (un formulaire HTML ne peut pas poser ce Content-Type sans CORS).
function corps_json(): array
{
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
        erreur(415, 'JSON attendu');
    }
    $donnees = json_decode(file_get_contents('php://input'), true);
    if (!is_array($donnees)) erreur(400, 'JSON invalide');
    return $donnees;
}

// ── Session des comptes ─────────────────────────────────────────────────────
function demarrer_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('mariage_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// Relit le compte à chaque appel : un compte supprimé perd l'accès aussitôt.
function compte_connecte(): ?array
{
    demarrer_session();
    if (empty($_SESSION['compte_id'])) return null;
    $st = db()->prepare('SELECT id, nom, email, role FROM comptes WHERE id = ?');
    $st->execute([$_SESSION['compte_id']]);
    return $st->fetch() ?: null;
}

function exiger_connexion(): array
{
    $compte = compte_connecte();
    if (!$compte) erreur(401, 'Non connecté');
    return $compte;
}

// ── E-mails ─────────────────────────────────────────────────────────────────
function envoyer_mail(string $a, string $sujet, string $texte): bool
{
    $hote = parse_url(cfg('SITE_URL'), PHP_URL_HOST) ?: 'localhost';
    $entetes = [
        'From'         => cfg('MAIL_FROM', 'no-reply@' . $hote),
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];
    // @ : sous WAMP sans serveur SMTP, mail() émet un warning et renvoie false
    return @mail($a, '=?UTF-8?B?' . base64_encode($sujet) . '?=', $texte, $entetes);
}
