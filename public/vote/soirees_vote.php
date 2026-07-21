<?php
require_once 'connexion.php';

$result = mysqli_query($conn, "SELECT id_soiree, nom_soiree, date, lieu FROM soiree ORDER BY date ASC");
$soirees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $soirees[] = $row;
}
echo json_encode($soirees);
mysqli_close($conn);
?>