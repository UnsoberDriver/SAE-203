<?php
session_start();
require_once 'connexion.php';

// ─── GET : liste tous les lieux disponibles + vote existant de l'utilisateur ──
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lieux = [];
    $result = mysqli_query($conn, "SELECT id_lieu, ville, adresse FROM lieu ORDER BY ville ASC");
    while ($row = mysqli_fetch_assoc($result)) {
        $lieux[] = $row;
    }

    $vote_existant = null;
    if (!empty($_SESSION['user_id']) && isset($_GET['id_soiree'])) {
        $id_soiree = intval($_GET['id_soiree']);
        $id_utilisateur = intval($_SESSION['user_id']);
        $stmt = mysqli_prepare($conn, "SELECT id_lieu FROM note WHERE id_soiree = ? AND id_utilisateur = ? AND id_lieu IS NOT NULL LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ii", $id_soiree, $id_utilisateur);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if ($row) $vote_existant = intval($row['id_lieu']);
    }

    header('Content-Type: application/json');
    echo json_encode(['lieux' => $lieux, 'vote_existant' => $vote_existant]);
    mysqli_close($conn);
    exit();
}

// ─── POST : enregistre le vote ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body || !isset($body['id_soiree'], $body['id_lieu'])) {
        echo json_encode(['success' => false, 'error' => 'Données invalides.']);
        exit();
    }

    $id_utilisateur = !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    if ($id_utilisateur === 0) {
        echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté.']);
        exit();
    }

    $id_soiree = intval($body['id_soiree']);
    $id_lieu   = intval($body['id_lieu']);

    $check = mysqli_prepare($conn, "SELECT id_note FROM note WHERE id_soiree = ? AND id_utilisateur = ? AND id_lieu IS NOT NULL LIMIT 1");
    mysqli_stmt_bind_param($check, "ii", $id_soiree, $id_utilisateur);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE note SET id_lieu = ? WHERE id_soiree = ? AND id_utilisateur = ? AND id_lieu IS NOT NULL");
        mysqli_stmt_bind_param($stmt, "iii", $id_lieu, $id_soiree, $id_utilisateur);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO note (id_soiree, id_utilisateur, id_lieu, note_lieu, note_film) VALUES (?, ?, ?, 0, 0)");
        mysqli_stmt_bind_param($stmt, "iii", $id_soiree, $id_utilisateur, $id_lieu);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }

    mysqli_close($conn);
    exit();
}