# Projet Soirées / Vote

## Structure du serveur

La racine web (document root) est **`www/public/`**. Tout ce qui doit être
accessible depuis le navigateur doit se trouver dans ce dossier.

```
www/
├── admin/
├── includes/
│   └── db.php          # connexion à la base de données (lit le .env)
├── public/              # ← racine web (document root)
│   ├── assets/
│   │   └── css/
│   │       └── styles.css
│   ├── img/
│   ├── vote/
│   │   ├── films_vote.php
│   │   ├── lieu_vote.php
│   │   ├── lieux_candidats.php
│   │   ├── lieux_soiree.php
│   │   └── soirees_vote.php
│   ├── formulaire.php
│   ├── index.php
│   ├── inscription.php
│   ├── lieu.php
│   ├── logout.php
│   ├── profil.php
│   ├── quitter_soiree.php
│   ├── session_user.php
│   ├── soiree_card.php
│   ├── traitement.php
│   └── vote_results.php
└── .env                  # variables de connexion BDD (racine du serveur)
```

## Règle générale des chemins

- Tous les liens/appels AJAX dans `index.php` sont **relatifs à `public/`**
  (ex: `assets/css/styles.css`, `vote/films_vote.php`).
- Tous les fichiers PHP qui ont besoin de la BDD doivent inclure
  `includes/db.php` avec un chemin **relatif à leur propre emplacement** via
  `__DIR__`, par exemple :
  - depuis `public/lieu.php` → `__DIR__ . '/../includes/db.php'`
  - depuis `public/vote/films_vote.php` → `__DIR__ . '/../../includes/db.php'`

## Corrections effectuées

1. **CSS non chargé (404)** — le lien `<link rel="stylesheet">` dans
   `index.php` pointait vers un mauvais chemin / avait un attribut `rel`
   corrompu. Corrigé pour pointer vers `assets/css/styles.css`.
2. **Bootstrap CSS non chargé** — l'attribut `rel` de la balise `<link>`
   Bootstrap était `rel="/assets/css/stylesheet"` au lieu de
   `rel="stylesheet"`, ce qui empêchait les modals de rester cachées par
   défaut (elles s'affichaient en pleine page).
3. **`films_vote.php` en 404** — le dossier `vote/` se trouvait à
   `www/vote/`, hors de la racine web (`www/public/`). Déplacé dans
   `www/public/vote/` et tous les appels AJAX dans `index.php` mis à jour
   avec le préfixe `vote/`.
4. **Bouton "Voter films" inactif** — l'attribut `data-bs-dismiss="modal"`
   sur les boutons `#btn-modal-voter` et `#btn-modal-voter-lieu` fermait le
   modal `modalParticiper` en même temps que le JS tentait d'ouvrir le
   modal suivant (`modalVote` / `modalVoteLieu`), créant un conflit de
   backdrop Bootstrap. Retiré l'attribut et ajouté un `hide()` manuel de
   l'ancien modal avant l'ouverture du suivant.
5. **`connexion.php` introuvable** — `films_vote.php` utilisait
   `require_once 'connexion.php'` (fichier obsolète, remplacé par un
   système `.env` + `includes/db.php`). Corrigé en
   `require_once __DIR__ . '/../../includes/db.php';`.

## À vérifier / reste à faire

- [ ] Vérifier si `lieu_vote.php`, `lieux_candidats.php`, `lieux_soiree.php`
      et `soirees_vote.php` (dans `public/vote/`) utilisent encore
      `require_once 'connexion.php'` et les corriger de la même façon si
      besoin.
- [ ] Confirmer que `includes/db.php` lit bien le `.env` à la racine du
      serveur pour les identifiants de connexion à la base de données.
- [ ] Vider le cache navigateur / forcer un rechargement après chaque
      upload pour éviter de tester une version obsolète des fichiers.

## Déploiement

1. Modifier les fichiers en local.
2. Réuploader via FTP dans le bon dossier de `www/public/` (respecter
   l'arborescence ci-dessus).
3. Tester avec la console navigateur (F12 → Console / Network) pour
   repérer d'éventuelles erreurs 404 ou PHP.
