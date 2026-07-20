<?php
require_once 'connexion.php';

session_start();
if (empty($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$id_soiree      = intval($_GET['id'] ?? 0);
$id_utilisateur = intval($_SESSION['user_id']);

if ($id_soiree > 0) {
    // Supprimer l'inscription
    $del = mysqli_prepare($conn, "DELETE FROM soiree_utilisateur WHERE id_soiree = ? AND id_utilisateur = ?");
    mysqli_stmt_bind_param($del, "ii", $id_soiree, $id_utilisateur);
    mysqli_stmt_execute($del);

    // Remettre une place disponible
    if (mysqli_affected_rows($conn) > 0) {
        $upd = mysqli_prepare($conn, "UPDATE soiree SET places_dispo = places_dispo + 1 WHERE id_soiree = ?");
        mysqli_stmt_bind_param($upd, "i", $id_soiree);
        mysqli_stmt_execute($upd);
    }
}

mysqli_close($conn);
header("Location: profil.php");
exit();
?>