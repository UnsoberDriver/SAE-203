<?php
require_once 'connexion.php';

$result = mysqli_query($conn, "SELECT DISTINCT lieu FROM soiree WHERE lieu IS NOT NULL AND lieu != '' ORDER BY lieu");

$lieux = [];
while ($row = mysqli_fetch_assoc($result)) {
    $l = trim($row['lieu']);
    if ($l != '' && !in_array($l, $lieux)) {
        $lieux[] = $l;
    }
}

sort($lieux);
echo json_encode($lieux);

mysqli_close($conn);
?>