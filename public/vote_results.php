<?php
require_once 'connexion.php';
$id_soiree = intval($_GET['id_soiree'] ?? 0);

// Résultats films (on exclut les lignes lieu_ qui ne sont pas de vrais films)
$stmt = mysqli_prepare($conn, "
    SELECT film, AVG(note_film) as moyenne
    FROM note
    WHERE id_soiree = ? AND film NOT LIKE 'lieu_%' AND film != ''
    GROUP BY film
    ORDER BY moyenne DESC
");
mysqli_stmt_bind_param($stmt, "i", $id_soiree);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$votes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $votes[] = [
        'film'    => $row['film'],
        'moyenne' => number_format($row['moyenne'], 1)
    ];
}

// Gestion de l'égalité au 1er rang : tirage au sort parmi les films ayant la moyenne max
if (!empty($votes)) {
    $max_moyenne = $votes[0]['moyenne'];
    $candidats_gagnants = array_filter($votes, fn($v) => $v['moyenne'] === $max_moyenne);

    if (count($candidats_gagnants) > 1) {
        // Tirage au sort parmi les ex-aequo
        $candidats_gagnants = array_values($candidats_gagnants);
        $gagnant = $candidats_gagnants[array_rand($candidats_gagnants)];

        // On replace le gagnant tiré au sort en première position, le reste garde l'ordre initial
        $autres = array_values(array_filter($votes, fn($v) => $v['film'] !== $gagnant['film']));
        $votes = array_merge([$gagnant], $autres);

        // Marquer qu'il y a eu égalité/tirage au sort
        $votes[0]['tirage_au_sort'] = true;
    } else {
        $votes[0]['tirage_au_sort'] = false;
    }
}

// Résultats lieux
$stmt2 = mysqli_prepare($conn, "
    SELECT l.ville, l.adresse, AVG(n.note_lieu) as moyenne
    FROM note n
    JOIN lieu l ON l.id_lieu = n.id_lieu
    WHERE n.id_soiree = ? AND n.film LIKE 'lieu_%'
    GROUP BY n.id_lieu, l.ville, l.adresse
    ORDER BY moyenne DESC
");
mysqli_stmt_bind_param($stmt2, "i", $id_soiree);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

$lieux = [];
while ($row = mysqli_fetch_assoc($result2)) {
    $lieux[] = [
        'ville'   => $row['ville'],
        'adresse' => $row['adresse'],
        'moyenne' => number_format($row['moyenne'], 1)
    ];
}

header('Content-Type: application/json');
echo json_encode(['films' => $votes, 'lieux' => $lieux]);
mysqli_close($conn);
?>