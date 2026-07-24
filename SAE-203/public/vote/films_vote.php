<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// ─── GET : récupère la liste des films d'une soirée ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_soiree'])) {
    $id = intval($_GET['id_soiree']);

    $stmt = mysqli_prepare($conn, "
        SELECT GROUP_CONCAT(sf.film ORDER BY sf.film SEPARATOR ',') as tous_les_films
        FROM soiree_film sf
        WHERE sf.id_soiree = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    $films = [];
    $i = 1;
    if ($row && $row['tous_les_films']) {
        foreach (explode(',', $row['tous_les_films']) as $f) {
            $f = trim($f);
            if ($f !== '') {
                $films[] = ['id' => $i++, 'titre' => $f, 'genre' => '', 'annee' => ''];
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($films);
    mysqli_close($conn);
    exit();
}

// ─── POST : enregistre les votes dans la table note ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // 1. Lire le body EN PREMIER
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body || !isset($body['id_soiree'], $body['votes']) || !is_array($body['votes'])) {
        echo json_encode(['success' => false, 'error' => 'Données invalides.']);
        mysqli_close($conn);
        exit();
    }

    // 2. Identifier l'utilisateur (session prioritaire, sinon body)
    $id_utilisateur = !empty($_SESSION['user_id'])
        ? intval($_SESSION['user_id'])
        : intval($body['id_utilisateur'] ?? 0);

    if ($id_utilisateur === 0) {
        echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté.']);
        mysqli_close($conn);
        exit();
    }

    $id_soiree = intval($body['id_soiree']);
    $votes     = $body['votes'];
    $errors    = [];

    // 3. Supprimer les anciens votes FILM de cet utilisateur pour cette soirée
    //    (on exclut les lignes lieu_% pour ne pas effacer le vote lieu)
    $del = mysqli_prepare($conn, "DELETE FROM note WHERE id_soiree = ? AND id_utilisateur = ? AND film NOT LIKE 'lieu_%'");
    mysqli_stmt_bind_param($del, "ii", $id_soiree, $id_utilisateur);
    mysqli_stmt_execute($del);

    // 4. Insérer un enregistrement par film
    foreach ($votes as $vote) {
        $film_nom  = trim($vote['film'] ?? '');
        $note_film = floatval($vote['note_film'] ?? 0);

        if ($film_nom === '') continue;

        $stmt = mysqli_prepare($conn, "INSERT INTO note (id_soiree, id_utilisateur, film, note_film, id_lieu, note_lieu) VALUES (?, ?, ?, ?, 0, 0)");
        mysqli_stmt_bind_param($stmt, "iisi", $id_soiree, $id_utilisateur, $film_nom, $note_film);
        if (!mysqli_stmt_execute($stmt)) {
            $errors[] = 'Erreur insertion "' . $film_nom . '" : ' . mysqli_error($conn);
        }
    }

    // 5. Inscrire l'utilisateur à la soirée s'il ne l'est pas déjà + décrémenter places_dispo
    $ins = mysqli_prepare($conn, "INSERT IGNORE INTO soiree_utilisateur (id_soiree, id_utilisateur) VALUES (?, ?)");
    mysqli_stmt_bind_param($ins, "ii", $id_soiree, $id_utilisateur);
    mysqli_stmt_execute($ins);

    // Si une ligne a été insérée (nouvelle inscription), décrémenter places_dispo
    if (mysqli_stmt_affected_rows($ins) > 0) {
        $upd = mysqli_prepare($conn, "UPDATE soiree SET places_dispo = places_dispo - 1 WHERE id_soiree = ? AND places_dispo > 0");
        mysqli_stmt_bind_param($upd, "i", $id_soiree);
        mysqli_stmt_execute($upd);
    }

    echo json_encode(empty($errors)
        ? ['success' => true]
        : ['success' => false, 'errors' => $errors]
    );

    mysqli_close($conn);
    exit();
}

header('Content-Type: application/json');
echo json_encode([]);
mysqli_close($conn);
?>