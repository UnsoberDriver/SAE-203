<?php
require_once 'connexion.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$lieu   = isset($_GET['lieu'])   ? mysqli_real_escape_string($conn, $_GET['lieu'])   : '';

$conditions = ["1=1"];
if ($search != '') $conditions[] = "(sf.film LIKE '%$search%' OR s.nom_soiree LIKE '%$search%')";
if ($lieu   != '') $conditions[] = "s.lieu = '$lieu'";

$where = "WHERE " . implode(' AND ', $conditions);

$sql = "SELECT
            s.id_soiree, s.nom_soiree, s.date, s.lieu, s.theme, s.places_dispo,
            GROUP_CONCAT(sf.film ORDER BY sf.film SEPARATOR '|||') AS films
        FROM soiree s
        LEFT JOIN soiree_film sf ON sf.id_soiree = s.id_soiree
        $where
        GROUP BY s.id_soiree
        ORDER BY s.date ASC";

$result = mysqli_query($conn, $sql);

$soirees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['films'] = $row['films'] ? explode('|||', $row['films']) : [];
    $soirees[] = $row;
}

header('Content-Type: application/json');

$json = json_encode($soirees);
if ($json === false) {
    echo json_encode(["error" => json_last_error_msg()]);
} else {
    echo $json;
}

mysqli_close($conn);
?>