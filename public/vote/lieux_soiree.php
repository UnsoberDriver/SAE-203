<?php
require_once 'connexion.php';

$id_soiree = intval($_GET['id_soiree'] ?? 0);

$stmt = mysqli_prepare($conn, "
    SELECT l.id_lieu, l.ville, l.adresse
    FROM soiree_lieu sl
    JOIN lieu l ON l.id_lieu = sl.id_lieu
    WHERE sl.id_soiree = ?
    ORDER BY l.ville
");
mysqli_stmt_bind_param($stmt, "i", $id_soiree);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$lieux = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lieux[] = $row;
}

header('Content-Type: application/json');
echo json_encode($lieux);
mysqli_close($conn);
?>