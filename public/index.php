<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mouvix</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">

</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="index.php" class="navbar-brand navbar-logo">Mouvix</a>
            <div class="d-flex align-items-center">
                <?php if (!empty($_SESSION['user'])): ?>
                    <a href="profil.php" class="btn-search me-1" style="text-decoration:none;">👤
                        <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></a>
                    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
                        <a href="admin_dashboard.php" class="btn-search me-1" style="text-decoration:none;">⚙️ Dashboard
                            Admin</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="#" class="btn-search me-1" data-bs-toggle="modal"
                        data-bs-target="#modalInscription">Inscription</a>
                    <a href="#" class="btn-search me-2" data-bs-toggle="modal"
                        data-bs-target="#modalConnexion">Connexion</a>
                <?php endif; ?>
                <input class="search-input me-2" type="search" id="recherche" placeholder="Rechercher un film..."
                    aria-label="Search" />
                <button class="btn-search" type="button" id="btn-recherche">Rechercher</button>
            </div>
        </div>
    </nav>

    <section class="container-fluid" id="principale">
        <div class="hero">
            <div class="hero-eyebrow">Programme</div>
            <div class="hero-title-row">
                <h1 class="hero-title">Les Soirées</h1>
                <span class="hero-count" id="hero-count"></span>
            </div>
        </div>
        <hr class="hero-divider">

        <!-- Modal Vote -->
        <div class="modal fade" id="modalVote" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="vote-soiree-titre"></h5>
                        <button type="button" class="btn-modal-close ms-auto"
                            onclick="window.location.href='index.php'">← Accueil</button>
                    </div>
                    <div class="modal-body" style="overflow-y: auto;">
                        <p class="vote-subtitle">Glissez chaque film dans la colonne correspondant à votre envie. Votre
                            classement détermine le score.</p>
                        <p class="score-note">Système de points : Très favorable = 10 pts · Neutre = 5 pts · Non
                            favorable = 0 pt</p>
                        <div
                            style="font-size:.68rem; color:var(--text-muted); letter-spacing:.07em; text-transform:uppercase; margin-bottom:4px; text-align:center;">
                            Films disponibles &nbsp;<span id="vote-counter" style="font-family:monospace">0
                                restants</span>
                        </div>
                        <div class="films-bank" id="films-bank"></div>
                        <div class="vote-columns">
                            <div class="vote-col col-envie">
                                <div class="vote-col-header">
                                    <span class="vote-col-label">✅ Très favorable</span>
                                    <span class="vote-col-cap" id="cap-envie">0 / 3</span>
                                </div>
                                <div class="drop-col" id="col-envie" data-max="3"></div>
                            </div>
                            <div class="vote-col col-avis">
                                <div class="vote-col-header">
                                    <span class="vote-col-label">😐 Neutre</span>
                                    <span class="vote-col-cap" id="cap-avis">0 / 4</span>
                                </div>
                                <div class="drop-col" id="col-avis" data-max="4"></div>
                            </div>
                            <div class="vote-col col-non">
                                <div class="vote-col-header">
                                    <span class="vote-col-label">❌ Non favorable</span>
                                    <span class="vote-col-cap" id="cap-non">0 / 3</span>
                                </div>
                                <div class="drop-col" id="col-non" data-max="3"></div>
                            </div>
                        </div>
                        <div class="vote-result" id="vote-result">
                            <h6>✓ Vote enregistré</h6>
                            <div id="vote-result-rows"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn-vote-reset" onclick="resetVote()">Réinitialiser</button>
                        <button class="btn-vote-submit" onclick="submitVote()">Envoyer mon vote</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalVoteLieu" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">📍 Classer les lieux</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="vote-subtitle">Glissez les lieux pour les classer du plus favorable au moins
                            favorable.</p>
                        <div id="lieux-classement"
                            style="display:flex; flex-direction:column; gap:8px; min-height:50px;"></div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn-modal-close" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn-vote-submit" id="btn-soumettre-lieu">Valider</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="soirees-cards">
            <!-- Cards générées dynamiquement via soiree_card.php -->
        </div>

        <!-- Modal Participer -->
        <div class="modal fade" id="modalParticiper" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title" id="modalParticiper-titre"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-info-grid" id="modalParticiper-body"></div>
                        <div class="modal-films-label">Films programmés</div>
                        <div class="modal-films-grid" id="modalParticiper-films"></div>
                        <div class="modal-films-label" id="label-resultats-lieux" style="margin-top:16px;">📍 Résultats
                            lieux</div>
                        <div id="modalParticiper-lieux"></div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn-modal-close" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn-modal-vote" id="btn-modal-voter" data-bs-dismiss="modal">Voter
                            films</button>
                        <button type="button" class="btn-modal-vote" id="btn-modal-voter-lieu" data-bs-dismiss="modal"
                            style="display:none;">Voter lieu</button>
                    </div>
                </div>
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

                // Ouvrir le modal Participer au clic
                $(document).on('click', '.btn-participer', function () {
                    var nom = $(this).data('nom');
                    var date = $(this).data('date');
                    var lieu = $(this).data('lieu');
                    var places = $(this).data('places');
                    var filmsStr = $(this).data('films') || '';
                    var idSoiree = $(this).data('id');

                    $('#modalParticiper-titre').text(nom);
                    $('#modalParticiper-body').html(
                        '<div class="modal-info-cell"><div class="mic-label">Date</div><div class="mic-value">' + date + '</div></div>' +
                        '<div class="modal-info-cell"><div class="mic-label">Lieu</div><div class="mic-value">' + lieu + '</div></div>' +
                        '<div class="modal-info-cell"><div class="mic-label">Places</div><div class="mic-value">' + places + '</div></div>'
                    );

                    var films = filmsStr.split(',').map(function (f) { return f.trim(); }).filter(Boolean);
                    $('#modalParticiper-films').html(
                        films.length
                            ? films.map(function (f) { return '<span class="modal-film-pill">' + f + '</span>'; }).join('')
                            : '<span style="color:var(--text-muted); font-size:.85rem;">Aucun film renseigné</span>'
                    );

                    // Stocker l'id et le nom de la soirée pour le bouton Voter
                    $('#btn-modal-voter').data('id', idSoiree).data('nom', nom);
                    $('#btn-modal-voter-lieu').data('id', idSoiree);

                    var modal = new bootstrap.Modal(document.getElementById('modalParticiper'));
                    modal.show();
                });

                // Bouton Voter dans le modal → ouvrir le modal de vote pour cette soirée
                $(document).on('click', '#btn-modal-voter', function () {
                    var id = $(this).data('id');
                    var nom = $(this).data('nom');
                    ouvrirVoteFilm(id, nom);
                });

                function ouvrirVoteFilm(id, nom) {
                    $('#vote-soiree-titre').text(nom);
                    currentSoireeId = id;

                    $.ajax({
                        url: 'vote/films_vote.php?id_soiree=' + id,
                        method: 'GET',
                        dataType: 'json',
                        success: function (films) {
                            FILMS.length = 0;
                            films.forEach(function (f) { FILMS.push(f); });
                            var total = FILMS.length;
                            COLS['col-envie'].max = Math.min(3, total);
                            COLS['col-avis'].max = Math.min(4, total);
                            COLS['col-non'].max = Math.min(3, total);
                            Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                            renderAll();
                            var modalVote = new bootstrap.Modal(document.getElementById('modalVote'));
                            modalVote.show();
                        }
                    });
                }

                // Bouton "Voter lieu" → ouvre la modale de vote lieu
                $(document).on('click', '#btn-modal-voter-lieu', function () {
                    var id = $(this).data('id');
                    var nom = $('#btn-modal-voter').data('nom') || '';
                    chargerVoteLieu(id);
                    currentLieuSoireeId = id;
                    currentLieuSoireeNom = nom;
                    new bootstrap.Modal(document.getElementById('modalVoteLieu')).show();
                });

                // Soumission vote lieu
                $(document).on('click', '#btn-soumettre-lieu', function () {
                    if (currentLieuSoireeId) soumettreVoteLieu(currentLieuSoireeId);
                });

                function chargerSoireesVote() {
                    $.ajax({
                        url: 'vote/soirees_vote.php',
                        method: 'GET',
                        success: function (data) {
                            var soirees = (typeof data === "string") ? JSON.parse(data) : data;
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
                        url: 'vote/films_vote.php?id_soiree=' + id,
                        method: 'GET',
                        dataType: 'json',
                        success: function (films) {
                            FILMS.length = 0;
                            films.forEach(function (f) { FILMS.push(f); });
                            var total = FILMS.length;
                            COLS['col-envie'].max = Math.min(3, total);
                            COLS['col-avis'].max = Math.min(4, total);
                            COLS['col-non'].max = Math.min(3, total);
                            Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                            $('#vote-soiree-titre').text(nom);
                            currentSoireeId = id;
                            $('#choix-soiree').hide();
                            $('#vote-interface').show();
                            renderAll();
                        }
                    });
                });

                // ── Mapping genre → image ───────────────────────────
                var genreImages = {
                    'horreur': 'img/horreur.jpg',
                    'romance': 'img/romance.jpg',
                    'action': 'img/action.jpg',
                    'comedie': 'img/comedie.jpg',
                    'aventure': '/img/aventure.jpg',
                    'science_fiction': 'img/science_fiction.jpg',
                    'western': 'img/western.jpg',
                    'acteur': 'img/acteur.jpg',
                    'directeur': 'img/directeur.jpg',
                    'default': 'img/romance.jpg'
                };

                function getImageGenre(genre) {
                    if (!genre) return genreImages['default'];
                    var g = genre.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    return genreImages[g] || genreImages['default'];
                }

                // ── Génération des cards depuis la BDD ───────────────
                function chargerSoireesCards(params) {
                    console.log("Appel AJAX avec params:", params);
                    $.ajax({
                        url: 'soiree_card.php',
                        method: 'GET',
                        data: params || {},
                        success: function (data) {
                            console.log("SUCCESS reçu:", data);
                            var soirees = (typeof data === "string") ? JSON.parse(data) : data;
                            var container = $('#soirees-cards');
                            container.html('');

                            $('#hero-count').text(
                                soirees.length + (soirees.length === 1 ? ' soirée' : ' soirées')
                            );

                            if (soirees.length === 0) {
                                container.html('<p class="mt-3" style="color:var(--text-muted)">Aucune soirée trouvée.</p>');
                                return;
                            }

                            soirees.forEach(function (s, idx) {
                                // Scroll auto à l'ouverture de la liste films
                                var collapseId = 'collapse-card-' + s.id_soiree + '-' + idx;
                                var genre = s.theme || '';
                                var img = getImageGenre(genre);
                                var genreLabel = genre ? genre.charAt(0).toUpperCase() + genre.slice(1) : '';
                                var places = s.places_dispo || '-';

                                var filmsArr = (s.films || []).map(function (f) { return f.trim(); }).filter(Boolean);
                                var filmsForModal = filmsArr.join(', ');
                                var filmsTagsHtml = filmsArr.map(function (f) {
                                    return '<span class="film-tag">' + f + '</span>';
                                }).join('');

                                container.append(
                                    '<div class="soiree-card">' +
                                    '<div class="card-img-wrap">' +
                                    '<img src="' + img + '" class="card-img-top" alt="' + genre + '">' +
                                    (genreLabel ? '<span class="card-badge">' + genreLabel + '</span>' : '') +
                                    '<span class="card-badge-places"><i class="bi bi-ticket-perforated"></i>&nbsp;' + places + '</span>' +
                                    '</div>' +
                                    '<div class="card-body">' +
                                    '<div class="card-tag">' + (genreLabel || 'Soirée') + '</div>' +
                                    '<div class="card-title">' + s.nom_soiree + '</div>' +
                                    '<div class="card-subtitle">' + s.date + '</div>' +
                                    '<div class="card-text">' +
                                    '<span><i class="bi bi-geo-alt"></i>' + s.lieu + '</span>' +
                                    '<span><i class="bi bi-people"></i>' + places + ' places</span>' +
                                    '</div>' +
                                    '<div class="d-flex justify-content-between gap-2">' +
                                    '<button class="btn-participer" type="button"' +
                                    ' data-id="' + s.id_soiree + '"' +
                                    ' data-nom="' + s.nom_soiree + '"' +
                                    ' data-date="' + s.date + '"' +
                                    ' data-lieu="' + s.lieu + '"' +
                                    ' data-places="' + places + '"' +
                                    ' data-films="' + filmsForModal.replace(/"/g, '&quot;') + '"' +
                                    '>Participer</button>' +
                                    '<button class="btn-films" type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="false">Films</button>' +
                                    '</div>' +
                                    '<div class="collapse mt-2" id="' + collapseId + '">' +
                                    '<div class="films-grid">' + filmsTagsHtml + '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>'
                                );

                                // Scroll synchronisé avec l'animation Bootstrap (350ms)
                                $('#' + collapseId).on('show.bs.collapse', function () {
                                    // Fermer tous les autres collapses ouverts
                                    $('#soirees-cards .collapse.show').not(this).collapse('hide');

                                    var card = $(this).closest('.soiree-card');
                                    card.addClass('card-films-open');
                                    var cardEl = card[0];
                                    var duration = 350;
                                    var startTime = null;
                                    var startY = window.scrollY;

                                    function getTargetY() {
                                        var rect = cardEl.getBoundingClientRect();
                                        return window.scrollY + rect.bottom - window.innerHeight + 16;
                                    }

                                    function step(timestamp) {
                                        if (!startTime) startTime = timestamp;
                                        var progress = Math.min((timestamp - startTime) / duration, 1);
                                        var ease = 1 - Math.pow(1 - progress, 3);
                                        var targetY = getTargetY();
                                        window.scrollTo(0, startY + (targetY - startY) * ease);
                                        if (progress < 1) requestAnimationFrame(step);
                                    }

                                    requestAnimationFrame(step);
                                });

                                // Fermeture instantanée (seule l'ouverture reste animée)
                                $('#' + collapseId).on('hide.bs.collapse', function () {
                                    $(this).closest('.soiree-card').removeClass('card-films-open');
                                    this.style.setProperty('transition', 'none', 'important');
                                });
                                $('#' + collapseId).on('hidden.bs.collapse', function () {
                                    this.style.removeProperty('transition');
                                });
                            });
                        },
                        error: function (xhr, status, err) {
                            console.log("ERREUR AJAX:", status, err);
                            console.log("Réponse brute reçue:", xhr.responseText);
                            $('#hero-count').text('');
                            $('#soirees-cards').html('<p class="text-danger mt-3">Erreur de chargement des soirées.</p>');
                        }
                    });
                }

                // Chargement initial des cards
                chargerSoireesCards();

                // Chargement des soirées via AJAX (tableau) — désactivé
                // $.ajax({ url: 'formulaire.php', ... });

                // Fonction de recherche
                function lancerRecherche() {
                    var recherche = $('#recherche').val();
                    var params = { search: recherche };
                    // Mettre à jour les cards
                    chargerSoireesCards(params);
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

                if (erreur === 'email_existe' || erreur === 'utilisateur_existe') {
                    $('#form-inscription').prepend(
                        '<div class="alert alert-danger">Cet email est déjà utilisé. Veuillez vous connecter ou utiliser un autre email.</div>'
                    );
                    new bootstrap.Modal(document.getElementById('modalInscription')).show();
                    // Nettoyer l'URL pour éviter que le modal ne réapparaisse au refresh
                    window.history.replaceState({}, document.title, window.location.pathname);
                }

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

                $('#toggleMdpConnexion').on('click', function () {
                    var input = $('#mdpConnexion');
                    var icon = $('#eyeIconConnexion');
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

            });

        </script>

        <script>

            let FILMS = [];
            let USER_ID = null; // chargé depuis session_user.php

            // Récupère l'id utilisateur depuis la session PHP
            $.ajax({
                url: 'session_user.php',
                method: 'GET',
                dataType: 'json',
                success: function (resp) {
                    if (resp.connected) {
                        USER_ID = resp.user_id;
                    }
                }
            });

            function retourChoixSoiree() {
                Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                FILMS.length = 0;
                $('#vote-interface').hide();
                $('#choix-soiree').show();
            }

            // ────────────────────────────────────────────────────────
            //  SYSTÈME DE POINTS par colonne
            // ────────────────────────────────────────────────────────
            const POINTS = { 'col-envie': 10, 'col-avis': 5, 'col-non': 0 };
            let currentSoireeId = null;

            const COLS = {
                'col-envie': { label: '✅ Très favorable', max: 3, items: [] },
                'col-avis': { label: '😐 Neutre', max: 4, items: [] },
                'col-non': { label: '❌ Non favorable', max: 3, items: [] },
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

            // ────────────────────────────────────────────────────────
            //  Rendu banque
            // ────────────────────────────────────────────────────────
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

            // ────────────────────────────────────────────────────────
            //  Rendu colonne avec slots
            // ────────────────────────────────────────────────────────
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
                        if (!dragged) return;

                        let targetIdx = i;
                        if (data.items[i] !== null) {
                            // Slot occupé : on cherche le premier slot libre de cette colonne
                            const freeIdx = data.items.findIndex(v => v === null);
                            if (freeIdx === -1) return; // colonne pleine, rien à faire
                            targetIdx = freeIdx;
                        }

                        if (dragSource !== 'bank') COLS[dragSource].items[dragSlotIdx] = null;
                        data.items[targetIdx] = dragged;
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

            // ────────────────────────────────────────────────────────
            //  Retour banque
            // ────────────────────────────────────────────────────────
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
                const usedIds = new Set(
                    Object.values(COLS).flatMap(col => col.items.filter(Boolean).map(f => f.id))
                );
                const filmsBanque = FILMS.filter(f => !usedIds.has(f.id));
                if (filmsBanque.length > 0) {
                    alert('Veuillez classer tous les films avant d\'envoyer votre vote.');
                    return;
                }

                const votes = [];
                Object.entries(COLS).forEach(([colId, data]) => {
                    data.items.filter(Boolean).forEach(film => {
                        votes.push({ film: film.titre, note_film: POINTS[colId] });
                    });
                });

                // id_utilisateur géré côté serveur via $_SESSION['user_id'] dans films_vote.php

                // Vérification côté client
                if (!USER_ID) {
                    alert('Vous devez être connecté pour voter.');
                    return;
                }

                // Résumé visuel
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

                $.ajax({
                    url: 'vote/films_vote.php',
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        id_soiree: currentSoireeId,
                        id_utilisateur: USER_ID,
                        votes: votes
                    }),
                    success: function (resp) {
                        console.log('Vote réponse:', resp);
                        if (resp.success) {
                            document.getElementById('vote-result').classList.add('visible');

                            // Enchaîner sur le vote des lieux pour la même soirée
                            setTimeout(() => {
                                var modalVoteInstance = bootstrap.Modal.getInstance(document.getElementById('modalVote'));
                                if (modalVoteInstance) modalVoteInstance.hide();
                                var nomSoiree = $('#vote-soiree-titre').text();
                                chargerVoteLieu(currentSoireeId);
                                currentLieuSoireeId = currentSoireeId;
                                currentLieuSoireeNom = nomSoiree;
                                new bootstrap.Modal(document.getElementById('modalVoteLieu')).show();
                            }, 800);
                        } else {
                            alert('Erreur : ' + (resp.error || JSON.stringify(resp.errors)));
                        }
                    },
                    error: function (xhr) {
                        console.error('Vote erreur HTTP:', xhr.status, xhr.responseText);
                        alert('Erreur lors de l\'envoi du vote. Veuillez réessayer.\n\nDétail console (F12).');
                    }
                });
            }

            function resetVote() {
                Object.keys(COLS).forEach(c => { COLS[c].items = []; });
                renderAll();
            }

            // Init
            renderAll();
        </script>
        <script>
            // ── Vote Lieu ─────────────────────────────────────────────────────────────────
            // À appeler quand l'utilisateur ouvre la modale d'une soirée.
            // Remplace `id_soiree` par l'id réel de la soirée sélectionnée.

            async function chargerVoteLieu(id_soiree) {
                const res = await fetch(`vote/lieux_soiree.php?id_soiree=${id_soiree}`);
                const lieux = await res.json();
                const container = document.getElementById('lieux-classement');
                container.innerHTML = '';

                lieux.forEach((l, idx) => {
                    const div = document.createElement('div');
                    div.className = 'lieu-drag-item';
                    div.draggable = true;
                    div.dataset.id = l.id_lieu;
                    div.innerHTML = `
                    <span class="lieu-rank">${idx + 1}</span>
                    <span class="lieu-drag-handle">⠿</span>
                    <span class="lieu-info"><strong>${l.ville}</strong> <small>${l.adresse}</small></span>
                `;
                    container.appendChild(div);
                });

                initLieuDragDrop();
            }

            function initLieuDragDrop() {
                const container = document.getElementById('lieux-classement');
                let dragged = null;

                container.querySelectorAll('.lieu-drag-item').forEach(item => {
                    item.addEventListener('dragstart', () => {
                        dragged = item;
                        setTimeout(() => item.style.opacity = '0.4', 0);
                    });
                    item.addEventListener('dragend', () => {
                        item.style.opacity = '1';
                        updateRanks();
                    });
                    item.addEventListener('dragover', e => {
                        e.preventDefault();
                        const rect = item.getBoundingClientRect();
                        const after = e.clientY > rect.top + rect.height / 2;
                        if (dragged !== item) {
                            after ? item.after(dragged) : item.before(dragged);
                        }
                    });
                });
            }

            function updateRanks() {
                document.querySelectorAll('#lieux-classement .lieu-drag-item').forEach((item, idx) => {
                    item.querySelector('.lieu-rank').textContent = idx + 1;
                });
            }

            async function soumettreVoteLieu(id_soiree) {
                const items = [...document.querySelectorAll('#lieux-classement .lieu-drag-item')];
                if (!items.length) { alert('Aucun lieu à classer.'); return; }

                const total = items.length;
                const votes = items.map((item, idx) => ({
                    id_lieu: parseInt(item.dataset.id),
                    note_lieu: total - idx
                }));

                const res = await fetch('vote/lieu_vote.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_soiree: id_soiree, votes: votes })
                });

                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalVoteLieu')).hide();
                    // Affichage du message de succès
                    const toast = document.createElement('div');
                    toast.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:#2a2a2a;color:#fff;padding:14px 28px;border-radius:10px;font-size:.95rem;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.4);';
                    toast.textContent = data.message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3500);

                    // Enchaîner sur le vote des films pour la même soirée
                    setTimeout(() => {
                        ouvrirVoteFilm(id_soiree, currentLieuSoireeNom);
                    }, 400);
                } else {
                    alert('❌ Erreur : ' + (data.error ?? JSON.stringify(data.errors)));
                }
            }

            // Bouton valider lieu
            let currentLieuSoireeId = null;
            let currentLieuSoireeNom = '';

            // ── Gestion des clics sur les étoiles (conservé pour compatibilité) ──────────
            $(document).on('click', '.etoile-lieu', function () {
                const note = parseInt(this.dataset.note);
                document.querySelectorAll('.etoile-lieu').forEach(e => {
                    const n = parseInt(e.dataset.note);
                    e.textContent = n <= note ? '⭐' : '☆';
                    e.classList.toggle('active', n <= note);
                    e.dataset.selected = (n === note) ? 'true' : '';
                });
            });
            $(document).on('mouseenter', '.etoile-lieu', function () {
                const note = parseInt(this.dataset.note);
                document.querySelectorAll('.etoile-lieu').forEach(e => {
                    e.classList.toggle('hover-preview', parseInt(e.dataset.note) <= note);
                });
            });
            $(document).on('mouseleave', '#etoiles-lieu', function () {
                document.querySelectorAll('.etoile-lieu').forEach(e => e.classList.remove('hover-preview'));
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Modale Inscription -->
        <div class="modal fade" id="modalInscription" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: rgb(214, 184, 102);">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white fw-bold">Inscription</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-white text-center mb-3">Remplissez le formulaire pour créer votre compte</p>

                        <div id="erreur-global" class="alert alert-danger d-none mb-3"></div>

                        <form action="traitement.php" method="POST" id="form-inscription" novalidate autocomplete="off">
                            <div class="row">
                                <div class="mb-3 text-start col-6">
                                    <label for="prenom" class="form-label text-white">Prénom</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom"
                                        placeholder="Votre prénom" minlength="2" maxlength="100" required
                                        autocomplete="off" />
                                    <div class="invalid-feedback">Champ requis.</div>
                                </div>
                                <div class="mb-3 text-start col-6">
                                    <label for="nom" class="form-label text-white">Nom</label>
                                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom"
                                        minlength="2" maxlength="100" required autocomplete="off" />
                                    <div class="invalid-feedback">Champ requis.</div>
                                </div>
                            </div>
                            <div class="mb-3 text-start">
                                <label for="email" class="form-label text-white">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="ex : Utilisateur@gmail.com" required autocomplete="off" />
                                <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                            </div>
                            <div class="mb-3 text-start">
                                <label for="message" class="form-label text-white">Mot de passe</label>
                                <div class="input-group has-validation">
                                    <input type="password" class="form-control" id="message" name="message"
                                        placeholder="••••••••" minlength="6" required autocomplete="new-password" />
                                    <button class="btn btn-outline-secondary" type="button" id="toggleMdp">
                                        <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                    </button>
                                    <div class="invalid-feedback">Le mot de passe doit contenir au moins 6 caractères.
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-dark">S'inscrire</button>
                            </div>
                            <p class="text-center mt-3 mb-0">
                                <a href="./login.php" class="text-white">Déjà un compte ? Se connecter</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale Connexion -->
        <div class="modal fade" id="modalConnexion" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: rgb(214, 184, 102);">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white fw-bold">Connexion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($_SESSION['login_error'])):
                            unset($_SESSION['login_error']); ?>
                            <div class="alert alert-danger">Email ou mot de passe incorrect.</div>
                        <?php endif; ?>
                        <form action="login_auth.php" method="POST" autocomplete="off">
                            <div class="mb-3 text-start">
                                <label class="form-label text-white">Email</label>
                                <input type="email" class="form-control" name="email" required
                                    placeholder="ex : Utilisateur@email.com" autocomplete="off" />
                            </div>
                            <div class="mb-3 text-start">
                                <label class="form-label text-white">Mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="mdpConnexion" name="mdp" required
                                        placeholder="••••••••" autocomplete="new-password" />
                                    <button class="btn btn-outline-secondary" type="button" id="toggleMdpConnexion">
                                        <i class="bi bi-eye-slash" id="eyeIconConnexion"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-dark">Se connecter</button>
                            </div>
                            <p class="text-center mt-3 mb-0">
                                <a href="#" class="text-white" data-bs-dismiss="modal" data-bs-toggle="modal"
                                    data-bs-target="#modalInscription">Pas encore de compte ? S'inscrire</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['login_error_shown'])):
            unset($_SESSION['login_error_shown']); ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new bootstrap.Modal(document.getElementById('modalConnexion')).show();
                });
            </script>
        <?php endif; ?>
</body>
<footer></footer>

</html>