# Voting System
SAE203 is a school project built to organize themed movie nights with a group: propose a date, a location, a film lineup, and let everyone vote to decide what actually gets picked. Built in PHP with MySQL, no framework.

## What it does


## Stack

Native PHP, MySQL/PDO, vanilla HTML/CSS/JS. No framework, no build tool.

## Server structure

The web root is **`www/public/`**. Anything that needs to be
reachable from the browser must live inside this folder.

## Architecture du projet

```
/
├── .env                             # Variables d'environnement (DB, secrets) — non versionné
├── admin/                           # Espace d'administration
├── includes/                        # Fichiers techniques partagés
│   └── db.php                       # Connexion à la base de données (PDO)
│
└── www/
    └── public/
        ├── index.php                # Page d'accueil / point d'entrée principal
        │
        ├── assets/                  # Ressources statiques
        │   ├── css/
        │   │   └── styles.css
        │   └── img/
        │
        ├── auth/                    # Authentification
        │   ├── login.php
        │   └── inscription.php
        │
        ├── user/                    # Gestion de session utilisateur
        │   ├── login_auth.php       # Traitement de la connexion
        │   ├── logout.php
        │   └── session_user.php     # Vérification de session
        │
        ├── profil/                  # Profil utilisateur
        │   └── profil.php
        │
        ├── lieu/                    # Gestion des lieux
        │   └── lieu.php
        │
        ├── soiree/                  # Gestion des soirées / événements
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
