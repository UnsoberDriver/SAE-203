<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAÉ 203</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">

</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="./index.php" class="navbar-brand navbar-logo">Mouvix</a>
            <div class="d-flex align-items-center">
                <a href="./inscription.php" class="navbar-brand py-3">Inscription</a>
                <div class="dropdown me-2">
                    <button type="button" id="btn-filtre"
                        title="Filtrer par genre" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <ul class="dropdown-menu" id="dropdown-filtres" style="min-width:200px;">
                        <li><a class="dropdown-item active" href="#" data-type="lieu" data-value="">Tous les lieux</a></li>
                        <li><hr class="dropdown-divider"></li>
                    </ul>
                </div>
                <input class="search-input me-2" type="search" id="recherche" placeholder="Rechercher un film..."
                    aria-label="Search" />
                <button class="btn-search" type="button" id="btn-recherche">Rechercher</button>
            </div>
        </div>
    </nav>

    <section class="container-fluid" id="principale">
        
        <div class="modal-content col-lg-6 col-md-8 col-12 mx-auto mt-4">

            <div class="modal-header border-0">
                <h5 class="modal-title mx-auto">Inscription</h5>
            </div>
            <div class="modal-body text-center">
                <p style="color:var(--text-muted); font-size:.9rem;">Remplissez le formulaire d'authentification</p>

                <form action="traitement.php" method="POST" class="mt-3" id="form-inscription" novalidate>

                    <div id="erreur-global" class="alert alert-danger d-none mb-3"></div>

                    <div class="row">
                        <div class="mb-3 text-start col-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                placeholder="Votre prénom" minlength="2" maxlength="100" required />
                            <div class="invalid-feedback">Champ requis.</div>
                        </div>
                        <div class="mb-3 text-start col-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom"
                                minlength="2" maxlength="100" required />
                            <div class="invalid-feedback">Champ requis.</div>
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="ex : Utilisateur@gmail.com" required />
                        <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="message" class="form-label">Mot de passe</label>
                        <div class="input-group has-validation">
                            <input type="password" class="form-control" id="mdp" name="mot de passe"
                                placeholder="••••••••" minlength="6" required />
                            <button class="btn btn-outline-secondary" type="button" id="toggleMdp">
                                <i class="bi bi-eye-slash" id="eyeIcon"></i>
                            </button>
                            <div class="invalid-feedback">Le mot de passe doit contenir au moins 6 caractères.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="submit" class="btn-vote-submit">S'inscrire</button>
                        <a href="login.php" class="btn-vote-reset" style="text-decoration:none;">Se connecter</a>
                    </div>
                </form>
                <p class="mt-3 mb-0"><a href="admin_login.php" style="color:var(--text-muted); text-decoration: none; font-size:.85rem;">🔐 Accès
                        administrateur</a></p>
            </div>
        </div>
        
        


        <script>
            $(document).ready(function () {

                // Gestion des onglets
                $('[data-tab]').on('click', function (e) {
                    e.preventDefault();
                    var tabId = $(this).data('tab');
                    $('[data-tab]').removeClass('active');
                    $(this).addClass('active');
                    $('.tab-content-section').removeClass('active');
                    $('#tab-' + tabId).addClass('active');

                    var titres = {
                        'programmation': 'Les Soirées',
                        'vote': 'Les Votes',
                        'inscription': 'Inscription'
                    };
                    $('h1').text(titres[tabId] || 'Les Soirées');

                    if (tabId === 'vote') {
                        $('#vote-interface').hide();
                        $('#choix-soiree').show();
                        chargerSoireesVote();
                    }
                });

                function chargerSoireesVote() {
                    $.ajax({
                        url: 'soirees_vote.php',
                        method: 'GET',
                        success: function (data) {
                            var soirees = JSON.parse(data);
                            $('#liste-soirees-vote').html('');
                            soirees.forEach(function (s) {
                                $('#liste-soirees-vote').append(
                                    '<button class="btn btn-light btn-soiree px-4 py-3" data-id="' + s.id_soiree + '" data-nom="' + s.nom_soiree + '">' +
                                    '🎬 <strong>' + s.nom_soiree + '</strong><br><small class="text-muted">' + s.date + ' — ' + s.lieu + '</small>' +
                                    '</button>'
                                );
                            });
                        }
                    });
                }

                $(document).on('click', '.btn-soiree', function () {
                    var id = $(this).data('id');
                    var nom = $(this).data('nom');
                    $.ajax({
                        url: 'films_vote.php?id_soiree=' + id,
                        method: 'GET',
                        success: function (data) {
                            FILMS.length = 0;
                            var films = JSON.parse(data);
                            films.forEach(function(f) { FILMS.push(f); });
                            var total = FILMS.length;
                            COLS['col-envie'].max = Math.ceil(total * 0.3) || 1;
                            COLS['col-avis'].max  = Math.ceil(total * 0.4) || 1;
                            COLS['col-non'].max   = Math.floor(total * 0.3) || 1;
                            Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                            $('#vote-soiree-titre').text('🎬 ' + nom);
                            $('#choix-soiree').hide();
                            $('#vote-interface').show();
                            renderAll();
                        }
                    });
                });

                // Chargement des soirées via AJAX
                $.ajax({
                    url: 'formulaire.php',
                    method: 'GET',
                    success: function (data) {
                        $('#liste-soirees').html(data);
                    },
                    error: function () {
                        $('#liste-soirees').html('<p class="text-danger">Erreur de chargement</p>');
                    }
                });

                // Chargement des lieux dans le dropdown
                $.ajax({
                    url: 'lieu.php',
                    method: 'GET',
                    success: function (data) {
                        var lieux = JSON.parse(data);
                        lieux.forEach(function (lieu) {
                            $('#dropdown-filtres').append(
                                '<li><a class="dropdown-item" href="#" data-type="lieu" data-value="' + lieu + '">' + lieu + '</a></li>'
                            );
                        });
                    }
                });

                // Sélection d'un lieu
                $(document).on('click', '#dropdown-filtres .dropdown-item', function (e) {
                    e.preventDefault();
                    $('#dropdown-filtres .dropdown-item').removeClass('active');
                    $(this).addClass('active');
                    lancerRecherche();
                });

                // Fonction de recherche
                function lancerRecherche() {
                    var recherche = $('#recherche').val();
                    var lieu = $('#dropdown-filtres .dropdown-item.active').data('value') || '';
                    $.ajax({
                        url: 'formulaire.php',
                        method: 'GET',
                        data: { search: recherche, lieu: lieu },
                        success: function (data) {
                            $('#liste-soirees').html(data);
                            $('[data-tab]').removeClass('active');
                            $('[data-tab="programmation"]').addClass('active');
                            $('.tab-content-section').removeClass('active');
                            $('#tab-programmation').addClass('active');
                        }
                    });
                }

                $('#btn-recherche').on('click', function () {
                    lancerRecherche();
                });

                $('#recherche').on('keypress', function (e) {
                    if (e.which == 13) {
                        lancerRecherche();
                    }
                });

                $('#recherche').on('search', function () {
                    if ($(this).val() === '') {
                        lancerRecherche();
                    }
                });

                // Drag & drop
                let dragged = null;

                document.querySelectorAll('[draggable="true"]').forEach(function (item) {
                    item.addEventListener('dragstart', function () {
                        dragged = item;
                    });
                });

                const zone = document.getElementById('zone-competences');

                zone.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    zone.classList.add('bg-info-subtle');
                });

                zone.addEventListener('dragleave', function () {
                    zone.classList.remove('bg-info-subtle');
                });

                zone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    zone.classList.remove('bg-info-subtle');
                    zone.appendChild(dragged);
                });

            });

            $('#form-inscription').on('submit', function (e) {
                var form = this;
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            const params = new URLSearchParams(window.location.search);
            const erreur = params.get('erreur');

            $('#toggleMdp').on('click', function () {
                var input = $('#message');
                var icon = $('#eyeIcon');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                }
            });

            document.querySelectorAll('input').forEach(function (input) {
                var originalPlaceholder = input.placeholder;
                input.addEventListener('focus', function () {
                    this.placeholder = '';
                });
                input.addEventListener('blur', function () {
                    if (this.value === '') {
                        this.placeholder = originalPlaceholder;
                    }
                });
            });

        </script>

        <script>

            let FILMS = [];

            function retourChoixSoiree() {
                Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                FILMS.length = 0;
                $('#vote-interface').hide();
                $('#choix-soiree').show();
            }

            // ────────────────────────────────────────────────────────
            //  SYSTÈME DE POINTS par colonne
            // ────────────────────────────────────────────────────────
            const POINTS = { 'col-envie': 3, 'col-avis': 1, 'col-non': 0 };

            const COLS = {
                'col-envie': { label: '✅ Envie de voir', max: 3, items: [] },
                'col-avis': { label: '😐 Pas d\'avis', max: 4, items: [] },
                'col-non': { label: '❌ Pas envie', max: 3, items: [] },
            };

            let dragged = null;
            let dragSource = null;
            let dragSlotIdx = null;

            // ────────────────────────────────────────────────────────
            //  Créer une carte film
            // ────────────────────────────────────────────────────────
            function makeFilmCard(film, parentId) {
                const div = document.createElement('div');
                div.className = 'film-card';
                div.draggable = true;
                div.dataset.id = film.id;

                // Affiche ou placeholder
                const thumb = document.createElement('div');
                thumb.className = 'film-thumb-placeholder';
                thumb.textContent = '🎬';
                div.appendChild(thumb);

                // Infos
                const info = document.createElement('div');
                info.className = 'film-info';
                const title = document.createElement('div');
                title.className = 'film-title';
                title.textContent = film.titre;
                const meta = document.createElement('div');
                meta.className = 'film-meta';
                meta.textContent = film.genre + ' · ' + film.annee;
                info.appendChild(title);
                info.appendChild(meta);
                div.appendChild(info);

                // Bouton retirer (dans une colonne)
                if (parentId !== 'bank') {
                    const rm = document.createElement('span');
                    rm.className = 'film-remove';
                    rm.textContent = '×';
                    rm.title = 'Retirer';
                    rm.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const idx = COLS[parentId].items.findIndex(i => i && i.id === film.id);
                        if (idx !== -1) COLS[parentId].items[idx] = null;
                        renderAll();
                    });
                    div.appendChild(rm);
                }

                div.addEventListener('dragstart', () => {
                    dragged = film;
                    dragSource = parentId;
                    if (parentId !== 'bank') {
                        dragSlotIdx = COLS[parentId].items.findIndex(i => i && i.id === film.id);
                    }
                    setTimeout(() => div.classList.add('dragging'), 0);
                });
                div.addEventListener('dragend', () => div.classList.remove('dragging'));

                return div;
            }

            function renderBank() {
                const bank = document.getElementById('films-bank');
                bank.innerHTML = '';
                const used = new Set(Object.values(COLS).flatMap(c => c.items.filter(Boolean).map(f => f.id)));
                const avail = FILMS.filter(f => !used.has(f.id));
                if (avail.length === 0) {
                    bank.innerHTML = '<span style="font-size:.78rem;color:rgba(255,255,255,.45)">Tous les films ont été classés ✓</span>';
                } else {
                    avail.forEach(f => bank.appendChild(makeFilmCard(f, 'bank')));
                }
                document.getElementById('vote-counter').textContent = avail.length + ' restant' + (avail.length > 1 ? 's' : '');
            }

            function renderCol(colId) {
                const col = document.getElementById(colId);
                const data = COLS[colId];
                col.innerHTML = '';

                while (data.items.length < data.max) data.items.push(null);
                data.items = data.items.slice(0, data.max);

                const filled = data.items.filter(Boolean).length;
                const capKey = colId.replace('col-', 'cap-');
                document.getElementById(capKey).textContent = filled + ' / ' + data.max;

                for (let i = 0; i < data.max; i++) {
                    const slot = document.createElement('div');
                    slot.className = 'vote-slot';
                    slot.dataset.col = colId;
                    slot.dataset.slot = i;

                    const film = data.items[i];
                    if (film) {
                        slot.classList.add('filled');
                        slot.appendChild(makeFilmCard(film, colId));
                    } else {
                        const ph = document.createElement('span');
                        ph.className = 'slot-placeholder';
                        ph.textContent = 'film ' + (i + 1);
                        slot.appendChild(ph);
                    }

                    slot.addEventListener('dragover', e => { e.preventDefault(); e.stopPropagation(); slot.classList.add('dragover-slot'); });
                    slot.addEventListener('dragleave', () => slot.classList.remove('dragover-slot'));
                    slot.addEventListener('drop', e => {
                        e.preventDefault();
                        e.stopPropagation();
                        slot.classList.remove('dragover-slot');
                        if (!dragged || data.items[i] !== null) return;
                        if (dragSource !== 'bank') COLS[dragSource].items[dragSlotIdx] = null;
                        data.items[i] = dragged;
                        dragged = null; dragSource = null; dragSlotIdx = null;
                        renderAll();
                    });

                    col.appendChild(slot);
                }

                // Drop sur toute la colonne → premier slot libre
                col.addEventListener('dragover', e => { e.preventDefault(); col.classList.add('dragover'); });
                col.addEventListener('dragleave', e => { if (!col.contains(e.relatedTarget)) col.classList.remove('dragover'); });
                col.addEventListener('drop', e => {
                    e.preventDefault();
                    col.classList.remove('dragover');
                    if (!dragged) return;
                    const freeIdx = data.items.findIndex(v => v === null);
                    if (freeIdx === -1) return;
                    if (dragSource !== 'bank') COLS[dragSource].items[dragSlotIdx] = null;
                    data.items[freeIdx] = dragged;
                    dragged = null; dragSource = null; dragSlotIdx = null;
                    renderAll();
                });
            }

            function renderAll() {
                renderBank();
                Object.keys(COLS).forEach(renderCol);
                document.getElementById('vote-result').classList.remove('visible');
            }

            const bank = document.getElementById('films-bank');
            bank.addEventListener('dragover', e => { e.preventDefault(); bank.classList.add('dragover'); });
            bank.addEventListener('dragleave', () => bank.classList.remove('dragover'));
            bank.addEventListener('drop', e => {
                e.preventDefault();
                bank.classList.remove('dragover');
                if (!dragged || dragSource === 'bank') return;
                COLS[dragSource].items[dragSlotIdx] = null;
                dragged = null; dragSource = null; dragSlotIdx = null;
                renderAll();
            });

            // ────────────────────────────────────────────────────────
            //  Soumission du vote
            //  → Construit l'objet à envoyer en AJAX à vote.php
            // ────────────────────────────────────────────────────────
            function submitVote() {
                // Construction du payload
                // Format envoyé : { votes: [ { film_id, points }, ... ] }
                const payload = [];
                Object.entries(COLS).forEach(([colId, data]) => {
                    data.items.filter(Boolean).forEach(film => {
                        payload.push({ film_id: film.id, points: POINTS[colId] });
                    });
                });

                // Affichage du résumé
                const rows = document.getElementById('vote-result-rows');
                rows.innerHTML = '';
                Object.entries(COLS).forEach(([colId, data]) => {
                    const films = data.items.filter(Boolean);
                    const row = document.createElement('div');
                    row.className = 'result-row';
                    const key = document.createElement('span');
                    key.className = 'result-key';
                    key.textContent = data.label + ' (' + POINTS[colId] + ' pts)';
                    const val = document.createElement('span');
                    val.textContent = films.length ? films.map(f => f.titre).join(' · ') : '(vide)';
                    row.appendChild(key);
                    row.appendChild(val);
                    rows.appendChild(row);
                });
                document.getElementById('vote-result').classList.add('visible');

                // ── AJAX vers vote.php ──────────────────────────────
                // Décommenter en production :
                //
                // $.ajax({
                //   url: 'vote.php',
                //   method: 'POST',
                //   contentType: 'application/json',
                //   data: JSON.stringify({ votes: payload }),
                //   success: function(resp) { console.log('Vote enregistré', resp); },
                //   error:   function()     { alert('Erreur lors de l\'envoi du vote.'); }
                // });
                //
                // ────────────────────────────────────────────────────
                console.log('Payload vote :', JSON.stringify({ votes: payload }, null, 2));
            }

            function resetVote() {
                Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                renderAll();
            }

            // Init
            renderAll();
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<footer></footer>

</html>