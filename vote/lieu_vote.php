<?php
session_start();
require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body || !isset($body['id_soiree'], $body['votes']) || !is_array($body['votes'])) {
        echo json_encode(['success' => false, 'error' => 'Données invalides.']);
        exit();
    }

    $id_utilisateur = intval($_SESSION['user_id'] ?? 0);
    if (!$id_utilisateur) {
        echo json_encode(['success' => false, 'error' => 'Non connecté.']);
        exit();
    }

    $id_soiree = intval($body['id_soiree']);
    $errors = [];

    // Supprimer les anciens votes lieu (film LIKE 'lieu_%' pour ne pas toucher aux votes films)
    $del = mysqli_prepare($conn, "DELETE FROM note WHERE id_soiree = ? AND id_utilisateur = ? AND film LIKE 'lieu_%'");
    mysqli_stmt_bind_param($del, "ii", $id_soiree, $id_utilisateur);
    mysqli_stmt_execute($del);

    // Insérer les nouveaux votes lieu
    foreach ($body['votes'] as $vote) {
        $id_lieu   = intval($vote['id_lieu']);
        $note_lieu = intval($vote['note_lieu']);
        $film_key  = 'lieu_' . $id_lieu; // clé unique par lieu ex: "lieu_4"

        $stmt = mysqli_prepare($conn, "INSERT INTO note (id_soiree, id_utilisateur, film, id_lieu, note_lieu, note_film) VALUES (?, ?, ?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt, "iisii", $id_soiree, $id_utilisateur, $film_key, $id_lieu, $note_lieu);
        if (!mysqli_stmt_execute($stmt)) {
            $errors[] = mysqli_error($conn);
        }
    }

    if (empty($errors)) {
        // Inscrire l'utilisateur à la soirée s'il ne l'est pas déjà (sans décrémenter les places ici,
        // car films_vote.php gère déjà ce cas ; on évite juste de perdre le vote lieu si fait en premier)
        $ins = mysqli_prepare($conn, "INSERT IGNORE INTO soiree_utilisateur (id_soiree, id_utilisateur) VALUES (?, ?)");
        mysqli_stmt_bind_param($ins, "ii", $id_soiree, $id_utilisateur);
        mysqli_stmt_execute($ins);

        if (mysqli_stmt_affected_rows($ins) > 0) {
            $upd = mysqli_prepare($conn, "UPDATE soiree SET places_dispo = places_dispo - 1 WHERE id_soiree = ? AND places_dispo > 0");
            mysqli_stmt_bind_param($upd, "i", $id_soiree);
            mysqli_stmt_execute($upd);
        }

        echo json_encode(['success' => true, 'message' => '✅ Votre vote pour les lieux a bien été enregistré !']);
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
    }

    mysqli_close($conn);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Méthode non supportée.']);
mysqli_close($conn);
?>