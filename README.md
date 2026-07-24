# Voting System
SAE203 is a school project built to organize themed movie nights with a group: propose a date, a location, a film lineup, and let everyone vote to decide what actually gets picked. Built in PHP with MySQL, no framework.

## What it does
Recipe list filterable by category, with a detailed page per recipe (ingredients, steps, time, difficulty)
Live serving adjustment on the recipe page (quantities recalculated in JS)
User accounts: sign up / log in, with a "stay logged in" option (remember-me secured by token)
Admin dashboard to create, edit, and delete recipes
Image upload, automatically converted to AVIF + thumbnail generation
Contact form in a popup (AJAX, protected by a CSRF token)
Bilingual FR/EN site, auto-detected based on browser language

## Stack

Native PHP, MySQL/PDO, vanilla HTML/CSS/JS. No framework, no build tool. Images go through GD for AVIF conversion (requires PHP 8.1+).

## Server structure

The web root is **`www/public/`**. Anything that needs to be
reachable from the browser must live inside this folder.

## 🏗️ Architecture du projet

```
/
├── .env                        # Variables d'environnement (DB, secrets) — non versionné
├── admin/                      # Espace d'administration
│
└── www/
    └── public/
        ├── index.php            # Page d'accueil / point d'entrée principal
        │
        ├── assets/               # Ressources statiques
        │   ├── css/
        │   │   └── styles.css
        │   └── img/
        │
        ├── includes/             # Fichiers techniques partagés
        │   └── db.php             # Connexion à la base de données (PDO)
        │
        ├── auth/                 # Authentification
        │   ├── login.php
        │   └── inscription.php
        │
        ├── user/                 # Gestion de session utilisateur
        │   ├── login_auth.php     # Traitement de la connexion
        │   ├── logout.php
        │   └── session_user.php   # Vérification de session
        │
        ├── profil/                # Profil utilisateur
        │   └── profil.php
        │
        ├── lieu/                  # Gestion des lieux
        │   └── lieu.php
        │
        ├── soiree/                 # Gestion des soirées / événements
        │   ├── formulaire.php       # Création / édition d'une soirée
        │   ├── soiree_card.php      # Affichage d'une carte "soirée"
        │   └── quitter_soiree.php   # Sortie d'une soirée
        │
        ├── traitement/              # Traitements divers (formulaires, actions)
        │   └── traitement.php
        │
        └── vote/                    # Système de vote
            ├── films_vote.php
            ├── lieu_vote.php
            ├── lieux_candidats.php
            ├── lieux_soiree.php
            ├── soirees_vote.php
            └── vote_results.php
```

### Description des modules

| Dossier | Rôle |
|---|---|
| `auth/` | Inscription et connexion des utilisateurs |
| `user/` | Gestion de la session utilisateur (login, logout, vérification) |
| `includes/` | Fichiers techniques réutilisés dans tout le projet (connexion DB, etc.) |
| `lieu/` | Gestion des lieux proposés/associés aux soirées |
| `soiree/` | Création, affichage et gestion des soirées |
| `traitement/` | Scripts de traitement d'actions (formulaires, logique métier) |
| `vote/` | Système de vote (lieux, films, soirées) et affichage des résultats |
| `profil/` | Page de profil utilisateur |
| `assets/` | Fichiers statiques (CSS, images) |
| `admin/` | Interface d'administration (hors `public/`, non exposée directement) |

### Notes techniques

- **Séparation public / privé** : le dossier `admin/` et le fichier `.env` sont placés **hors du dossier `public/`** (racine du site), donc non accessibles directement via une URL — bonne pratique de sécurité.
- **Connexion DB centralisée** : `includes/db.php` centralise l'accès PDO, évitant la duplication des identifiants de connexion.
- **Découpage fonctionnel** : chaque fonctionnalité (auth, vote, soirée, lieu...) possède son propre dossier avec ses fichiers PHP dédiés, ce qui facilite la maintenance et la lisibilité du code.

## Security

A few things I put in place while learning about the topic:

* Passwords hashed with `password_hash` / `password_verify`
* Prepared SQL statements (PDO) everywhere, no query concatenation
* CSRF protection on sensitive forms (contact, recipe creation/editing)
* "Remember me" cookie based on a hashed selector/validator pair (no plaintext token stored server-side), rotated on every use

## Live

* [https://sae203-nb.alwaysdata.net/index.php](https://sae203-nb.alwaysdata.net/index.php)

## Author

Nicolas Boulloud — [LinkedIn](https://www.linkedin.com/in/nicolas-boulloud/)

## License

© 2026 Nicolas Boulloud. All rights reserved.
