<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$lieu   = isset($_GET['lieu'])   ? mysqli_real_escape_string($conn, $_GET['lieu'])   : '';

$conditions = ["1=1"];
if ($search != '') $conditions[] = "(sf.film LIKE '%$search%' OR s.nom_soiree LIKE '%$search%')";
if ($lieu   != '') $conditions[] = "s.lieu = '$lieu'";

$where = "WHERE " . implode(' AND ', $conditions);

$sql = "SELECT s.nom_soiree, s.date, s.lieu, s.places_dispo,
        GROUP_CONCAT(sf.film ORDER BY sf.film SEPARATOR ', ') as films
        FROM soiree s
        LEFT JOIN soiree_film sf ON sf.id_soiree = s.id_soiree
        $where
        GROUP BY s.id_soiree
        ORDER BY s.date ASC";

$result = mysqli_query($conn, $sql);

echo "<table class='table table-striped table-bordered text-dark'>
        <thead class='table-dark' style='position: sticky; top: 0; z-index: 1;'>
            <tr>
                <th>Nom</th><th>Date</th>
                <th>Lieu</th><th>Places</th>
                <th>Films</th>
            </tr>
        </thead>
        <tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    $films = explode(',', $row['films'] ?? '');
    $films = array_chunk($films, 3);
    $films_html = '';
    foreach ($films as $groupe) {
        $films_html .= implode(', ', $groupe) . '<br>';
    }

    echo "<tr>
            <td>{$row['nom_soiree']}</td>
            <td>{$row['date']}</td>
            <td>{$row['lieu']}</td>
            <td>{$row['places_dispo']}</td>
            <td>{$films_html}</td>
          </tr>";
}

echo "</tbody></table>";

if (mysqli_num_rows($result) == 0) {
    echo "<p class='text-danger'>Aucun résultat trouvé.</p>
          <button onclick=\"location.reload()\" class='btn btn-outline-secondary btn-sm'>
              <i class='bi bi-arrow-clockwise'></i> Réinitialiser
          </button>";
}

mysqli_close($conn);
?>