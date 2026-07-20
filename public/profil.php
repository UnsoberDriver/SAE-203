<?php
session_start();
require_once 'connexion.php';

if (empty($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$id_utilisateur = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT DISTINCT s.id_soiree, s.nom_soiree, s.date, s.lieu, s.places_dispo
    FROM soiree_utilisateur su
    JOIN soiree s ON su.id_soiree = s.id_soiree
    WHERE su.id_utilisateur = ?
    GROUP BY s.nom_soiree, s.date, s.lieu
    ORDER BY s.date ASC
");
mysqli_stmt_bind_param($stmt, "i", $id_utilisateur);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$soirees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $soirees[] = $row;
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Mouvix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="index.php" class="navbar-brand navbar-logo">Mouvix</a>
            <div class="d-flex align-items-center">
                <a href="profil.php" class="btn-search me-1">👤 <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></a>
                <a href="logout.php" class="btn-search me-2" style="text-decoration:none;">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <!-- Carte profil -->
                <div class="card shadow mb-4" style="background-color: rgb(214, 184, 102);">
                    <div class="card-body p-4 text-white text-center">
                        <div style="font-size: 4rem;">👤</div>
                        <h3 class="fw-bold mt-2"><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h3>
                        <hr style="border-color: rgba(255,255,255,0.3);">
                        <div class="text-start mt-3">
                            <p><i class="bi bi-person me-2"></i><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['user_prenom']) ?></p>
                            <p><i class="bi bi-person-fill me-2"></i><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user_nom']) ?></p>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <a href="index.php" class="btn btn-dark">← Retour au site</a>
                            <a href="logout.php" class="btn btn-outline-light">Se déconnecter</a>
                        </div>
                    </div>
                </div>

                <!-- Soirées inscrites -->
                <div class="card shadow" style="background-color: rgb(214, 184, 102);">
                    <div class="card-body p-4 text-white">
                        <h5 class="fw-bold mb-3">🎬 Mes soirées</h5>
                        <?php if (empty($soirees)): ?>
                            <p class="text-white-50">Vous n'êtes inscrit à aucune soirée pour l'instant.</p>
                        <?php else: ?>
                            <?php foreach ($soirees as $s): ?>
                                <div class="soiree-item mb-3 p-3 rounded d-flex justify-content-between align-items-center" 
                                     data-id="<?= $s['id_soiree'] ?>" data-nom="<?= htmlspecialchars($s['nom_soiree']) ?>"
                                     style="background-color: rgba(0,0,0,0.15); cursor:pointer;">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($s['nom_soiree']) ?></div>
                                        <div class="small mt-1">
                                            <i class="bi bi-calendar me-1"></i><?= htmlspecialchars($s['date']) ?>
                                            &nbsp;·&nbsp;
                                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['lieu']) ?>
                                            &nbsp;·&nbsp;
                                            <i class="bi bi-ticket-perforated me-1"></i><?= $s['places_dispo'] ?> places
                                        </div>
                                    </div>
                                    <a href="quitter_soiree.php?id=<?= $s['id_soiree'] ?>" 
                                       onclick="return confirm('Quitter cette soirée ?')"
                                       class="text-white ms-3" style="font-size:1.3rem; text-decoration:none;">✕</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Résultats des votes -->
                <div class="card shadow mt-4" style="background-color: rgb(214, 184, 102);">
                    <div class="card-body p-4 text-white">
                        <h5 class="fw-bold mb-3">🗳️ Résultats votes</h5>
                        <div id="vote-results-content">
                            <p class="text-white-50">Veuillez sélectionner une soirée.</p>
                        </div>
                    </div>
                </div>

                <!-- Résultats des votes lieux -->
                <div class="card shadow mt-4" style="background-color: rgb(214, 184, 102);">
                    <div class="card-body p-4 text-white">
                        <h5 class="fw-bold mb-3">📍 Résultats lieux</h5>
                        <div id="vote-results-lieux-content">
                            <p class="text-white-50">Veuillez sélectionner une soirée.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.soiree-item').forEach(function(el) {
            el.addEventListener('click', function() {
                document.querySelectorAll('.soiree-item').forEach(e => e.style.outline = 'none');
                this.style.outline = '2px solid white';

                const idSoiree = this.dataset.id;
                const nomSoiree = this.dataset.nom;

                fetch('vote_results.php?id_soiree=' + idSoiree)
                    .then(r => r.json())
                    .then(data => {
                        const films = data.films || [];
                        const lieux = data.lieux || [];

                        // Affichage des films
                        const divFilms = document.getElementById('vote-results-content');
                        if (!films.length) {
                            divFilms.innerHTML = '<p class="text-white-50">Aucun vote pour "' + nomSoiree + '".</p>';
                        } else {
                            let html = '<p class="fw-bold mb-2">' + nomSoiree + '</p>';
                            html += '<table class="table table-bordered mb-0" style="background-color:rgba(0,0,0,0.1);color:white;">';
                            html += '<thead><tr><th>Film</th><th class="text-center">Note moyenne</th></tr></thead><tbody>';
                            films.forEach(function(v, idx) {
                                const rowStyle = idx === 0 ? ' style="background-color:rgba(40,167,69,0.45);font-weight:bold;"' : '';
                                const badge = (idx === 0 && v.tirage_au_sort) ? ' <span title="Film choisi aléatoirement">🎲</span>' : '';
                                const trophy = idx === 0 ? ' <span title="Film gagnant">🏆</span>' + badge : '';
                                html += '<tr' + rowStyle + '><td>' + v.film + trophy + '</td><td class="text-center">' + v.moyenne + '</td></tr>';
                            });
                            html += '</tbody></table>';
                            divFilms.innerHTML = html;
                        }

                        // Affichage des lieux
                        const divLieux = document.getElementById('vote-results-lieux-content');
                        if (!lieux.length) {
                            divLieux.innerHTML = '<p class="text-white-50">Aucun vote de lieu pour "' + nomSoiree + '".</p>';
                        } else {
                            let html = '<p class="fw-bold mb-2">' + nomSoiree + '</p>';
                            html += '<table class="table table-bordered mb-0" style="background-color:rgba(0,0,0,0.1);color:white;">';
                            html += '<thead><tr><th>Lieu</th><th>Adresse</th><th class="text-center">Note moyenne</th></tr></thead><tbody>';
                            lieux.forEach(function(v) {
                                html += '<tr><td>' + v.ville + '</td><td>' + v.adresse + '</td><td class="text-center">' + v.moyenne + '</td></tr>';
                            });
                            html += '</tbody></table>';
                            divLieux.innerHTML = html;
                        }
                    });
            });
        });
    </script>
</body>
</html>