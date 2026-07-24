<?php
session_start();
require_once 'connexion.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $stmt = mysqli_prepare($conn, "DELETE FROM soiree WHERE id_soiree = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_dashboard.php?msg=supprime");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    $id = intval($_POST['id_soiree']);
    $nom_soiree = $_POST['nom_soiree'];
    $date = $_POST['date'];
    $lieu = $_POST['lieu'];
    $places = intval($_POST['places_dispo']);
    $films = array_filter(array_map('trim', explode(',', $_POST['film'])));

    $stmt = mysqli_prepare($conn, "UPDATE soiree SET nom_soiree=?, date=?, lieu=?, places_dispo=? WHERE id_soiree=?");
    mysqli_stmt_bind_param($stmt, "sssii", $nom_soiree, $date, $lieu, $places, $id);
    mysqli_stmt_execute($stmt);

    // Mettre à jour les films
    $del = mysqli_prepare($conn, "DELETE FROM soiree_film WHERE id_soiree = ?");
    mysqli_stmt_bind_param($del, "i", $id);
    mysqli_stmt_execute($del);
    $ins = mysqli_prepare($conn, "INSERT IGNORE INTO soiree_film (id_soiree, film) VALUES (?, ?)");
    foreach ($films as $f) {
        mysqli_stmt_bind_param($ins, "is", $id, $f);
        mysqli_stmt_execute($ins);
    }

    header("Location: admin_dashboard.php?msg=modifie");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom_soiree = $_POST['nom_soiree'];
    $date = $_POST['date'];
    $lieu = $_POST['lieu'];
    $places = intval($_POST['places_dispo']);
    $films = array_filter(array_map('trim', explode(',', $_POST['film'])));

    $stmt = mysqli_prepare($conn, "INSERT INTO soiree (nom_soiree, date, lieu, places_dispo) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "sssi", $nom_soiree, $date, $lieu, $places);
    mysqli_stmt_execute($stmt);
    $new_id = mysqli_insert_id($conn);

    $ins = mysqli_prepare($conn, "INSERT IGNORE INTO soiree_film (id_soiree, film) VALUES (?, ?)");
    foreach ($films as $f) {
        mysqli_stmt_bind_param($ins, "is", $new_id, $f);
        mysqli_stmt_execute($ins);
    }

    header("Location: admin_dashboard.php?msg=ajoute");
    exit();
}

$soirees = mysqli_query($conn, "
    SELECT s.*, 
           GROUP_CONCAT(DISTINCT sf.film ORDER BY sf.film SEPARATOR ', ') as films,
           GROUP_CONCAT(DISTINCT l.ville ORDER BY l.ville SEPARATOR ', ') as noms_lieux
    FROM soiree s
    LEFT JOIN soiree_film sf ON sf.id_soiree = s.id_soiree
    LEFT JOIN soiree_lieu sl ON sl.id_soiree = s.id_soiree
    LEFT JOIN lieu l ON l.id_lieu = sl.id_lieu
    GROUP BY s.id_soiree
    ORDER BY s.date ASC
");
if (!$soirees) {
    die("Erreur SQL : " . mysqli_error($conn));
}

// Gestion ajout lieu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_lieu'])) {
    $ville = trim($_POST['ville']);
    $dept  = intval($_POST['departement']);
    $adr   = trim($_POST['adresse']);
    $stmt  = mysqli_prepare($conn, "INSERT INTO lieu (ville, departement, adresse) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sis", $ville, $dept, $adr);
    mysqli_stmt_execute($stmt);
    header("Location: admin_dashboard.php?msg=lieu_ajoute");
    exit();
}

// Gestion suppression lieu
if (isset($_GET['supprimer_lieu'])) {
    $id   = intval($_GET['supprimer_lieu']);
    $stmt = mysqli_prepare($conn, "DELETE FROM lieu WHERE id_lieu = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_dashboard.php?msg=lieu_supprime");
    exit();
}

// Gestion modification lieu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_lieu'])) {
    $id   = intval($_POST['id_lieu']);
    $ville = trim($_POST['ville']);
    $dept  = intval($_POST['departement']);
    $adr   = trim($_POST['adresse']);
    $stmt  = mysqli_prepare($conn, "UPDATE lieu SET ville=?, departement=?, adresse=? WHERE id_lieu=?");
    mysqli_stmt_bind_param($stmt, "sisi", $ville, $dept, $adr, $id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_dashboard.php?msg=lieu_modifie");
    exit();
}

$lieux = mysqli_query($conn, "SELECT * FROM lieu ORDER BY ville ASC");
if (!$lieux) {
    die("Erreur SQL lieux : " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - SAÉ 203</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: rgb(238, 213, 144)">

    <nav class="navbar px-4" style="background-color: rgb(214, 184, 102);">
        <a href="index.php" class="navbar-brand text-white fw-bold" style="text-decoration: none;">Admin - SAÉ 203</a>
        <div>
            <span class="text-white me-3">👤 <?= htmlspecialchars($_SESSION['admin_nom']) ?></span>
            <a href="admin_logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
            <a href="index.php" class="btn btn-outline-light btn-sm ms-2">← Site</a>
        </div>
    </nav>

    <div class="container mt-4">

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?= [
                    'supprime'      => '✅ Soirée supprimée.',
                    'modifie'       => '✅ Soirée modifiée.',
                    'ajoute'        => '✅ Soirée ajoutée.',
                    'lieu_ajoute'   => '✅ Lieu ajouté.',
                    'lieu_supprime' => '✅ Lieu supprimé.',
                    'lieu_modifie'  => '✅ Lieu modifié.',
                ][$_GET['msg']] ?? '' ?>
            </div>
        <?php endif; ?>

        <!-- Barre de recherche -->
        <div class="input-group mb-4 shadow">
            <span class="input-group-text" style="background-color: rgb(214, 184, 102); border-color: #b8972a;">🔍</span>
            <input type="text" id="searchBar" class="form-control" placeholder="Rechercher par nom, lieu, date…"
                oninput="filtrerSoirees()" style="border-color: #b8972a;" />
            <button class="btn btn-outline-secondary" onclick="document.getElementById('searchBar').value=''; filtrerSoirees();">✕</button>
        </div>

        <!-- Ajouter une soirée -->
        <div class="card mb-4 shadow" style="background-color: rgb(214, 184, 102);">
            <div class="card-header text-white fw-bold">➕ Ajouter une soirée</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-2">
                        <div class="col-md-4"><input type="text" class="form-control" name="nom_soiree"
                                placeholder="Nom de la soirée" required /></div>
                        <div class="col-md-2"><input type="date" class="form-control" name="date" required /></div>
                        <div class="col-md-3"><input type="text" class="form-control" name="lieu" placeholder="Lieu"
                                required /></div>
                        <div class="col-md-1"><input type="number" class="form-control" name="places_dispo"
                                placeholder="Places" required /></div>
                        <div class="col-md-3"><input type="text" class="form-control" name="film"
                                placeholder="Films (séparés par virgule)" /></div>
                        <div class="col-md-2">
                            <button type="submit" name="ajouter" class="btn btn-success w-100">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des soirées -->
        <div class="card shadow" style="background-color: rgb(214, 184, 102);">
            <div class="card-header text-white fw-bold">📋 Liste des soirées</div>
            <div class="card-body p-0">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Date</th>
                            <th>Lieux</th>
                            <th>Places</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = mysqli_fetch_assoc($soirees)): ?>
                            <tr id="row-<?= $s['id_soiree'] ?>">
                                <td><?= htmlspecialchars($s['nom_soiree']) ?></td>
                                <td><?= htmlspecialchars($s['date']) ?></td>
                                <td><?= htmlspecialchars($s['noms_lieux'] ?? $s['lieu']) ?></td>
                                <td><?= $s['places_dispo'] ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm"
                                        data-soiree="<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="ouvrirFilms(JSON.parse(this.dataset.soiree))">🎬 Films</button>
                                    <button class="btn btn-warning btn-sm"
                                        data-soiree="<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="ouvrirModif(JSON.parse(this.dataset.soiree))">✏️</button>
                                    <a href="?supprimer=<?= $s['id_soiree'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Supprimer cette soirée ?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    </div>

    <!-- Modal modification lieu -->
    <div class="modal fade" id="modalModifLieu" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Modifier le lieu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="formModifLieu">
                        <input type="hidden" name="id_lieu" id="ml_id">
                        <div class="mb-2"><label>Ville</label><input type="text" class="form-control" name="ville" id="ml_ville" required /></div>
                        <div class="mb-2"><label>Département</label><input type="number" class="form-control" name="departement" id="ml_dept" required /></div>
                        <div class="mb-2"><label>Adresse</label><input type="text" class="form-control" name="adresse" id="ml_adr" required /></div>
                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" name="modifier_lieu" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalFilms" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🎬 Films de la soirée : <span id="f_nom_soiree"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Liste des films -->
                    <div id="f_liste_films" class="mb-3">
                        <!-- Films injectés ici par JS -->
                    </div>
                    <!-- Formulaire ajout/modif film -->
                    <form method="POST" id="formFilms">
                        <input type="hidden" name="id_soiree" id="f_id">
                        <div class="input-group">
                            <input type="text" class="form-control" name="film" id="f_film"
                                placeholder="Films (séparés par virgule)" />
                            <button type="submit" name="modifier" class="btn btn-primary">💾 Enregistrer</button>
                        </div>
                        <small class="text-muted">Saisir tous les films séparés par des virgules, puis enregistrer.</small>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal modification -->
    <div class="modal fade" id="modalModif" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Modifier la soirée</h5>
                </div>
                <div class="modal-body">
                    <form method="POST" id="formModif">
                        <input type="hidden" name="id_soiree" id="m_id">
                        <div class="row g-2">
                            <div class="col-md-6"><label>Nom</label><input type="text" class="form-control"
                                    name="nom_soiree" id="m_nom" required /></div>
                            <div class="col-md-3"><label>Date</label><input type="date" class="form-control" name="date"
                                    id="m_date" required /></div>
                            <div class="col-md-3"><label>Lieu</label><input type="text" class="form-control" name="lieu"
                                    id="m_lieu" required /></div>
                            <div class="col-md-2"><label>Places</label><input type="number" class="form-control"
                                    name="places_dispo" id="m_places" required /></div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" name="modifier" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filtrerSoirees() {
            const q = document.getElementById('searchBar').value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        function ouvrirFilms(s) {
            document.getElementById('f_id').value = s.id_soiree;
            document.getElementById('f_nom_soiree').textContent = s.nom_soiree;
            document.getElementById('f_film').value = s.films ?? '';

            // Afficher la liste des films
            const liste = document.getElementById('f_liste_films');
            const films = s.films ? s.films.split(', ') : [];
            if (films.length > 0) {
                liste.innerHTML = '<p class="fw-bold mb-1">Films actuels :</p>'
                    + films.map(f => `<span class="badge bg-secondary me-1 mb-1 fs-6">${f}</span>`).join('');
            } else {
                liste.innerHTML = '<p class="text-muted fst-italic">Aucun film associé à cette soirée.</p>';
            }

            new bootstrap.Modal(document.getElementById('modalFilms')).show();
        }

        function ouvrirModif(s) {
            document.getElementById('m_id').value = s.id_soiree;
            document.getElementById('m_nom').value = s.nom_soiree;
            document.getElementById('m_date').value = s.date;
            document.getElementById('m_lieu').value = s.lieu;
            document.getElementById('m_places').value = s.places_dispo;
            new bootstrap.Modal(document.getElementById('modalModif')).show();
        }
        function ouvrirModifLieu(l) {
            document.getElementById('ml_id').value    = l.id_lieu;
            document.getElementById('ml_ville').value = l.ville;
            document.getElementById('ml_dept').value  = l.departement;
            document.getElementById('ml_adr').value   = l.adresse;
            new bootstrap.Modal(document.getElementById('modalModifLieu')).show();
        }
    </script>
</body>

</html>