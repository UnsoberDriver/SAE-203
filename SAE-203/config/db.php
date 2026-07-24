<?php
/**
 * Connexion à la base de données.
 *
 * Les identifiants sont lus depuis un fichier .env qui doit rester
 * STRICTEMENT LOCAL : il ne doit jamais être commité sur GitHub, ni
 * uploadé en clair n'importe où d'accessible publiquement.
 *
 * Emplacement attendu : à la racine du site, un niveau au-dessus de
 * includes/ (donc www/.env si ce fichier est dans www/includes/db.php).
 */

$env_path = __DIR__ . '/../../.env';

if (!file_exists($env_path)) {
    die(
        "Erreur : fichier .env introuvable.\n" .
        "Ce fichier doit être créé manuellement en local (jamais versionné sur GitHub) " .
        "et placé à la racine du site, à côté du dossier includes/.\n" .
        "Copie .env.example vers .env et renseigne tes propres identifiants."
    );
}

$env = parse_ini_file($env_path);

if ($env === false) {
    die("Erreur : impossible de lire le fichier .env.");
}

$required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($required as $key) {
    if (!isset($env[$key])) {
        die("Erreur : la clé $key est manquante dans le fichier .env.");
    }
}

$conn = mysqli_connect(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME']
);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
