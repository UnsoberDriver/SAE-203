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

```
www/
├── admin/
│   ├── admin_auth.php
│   ├── admin_dashboard.php
│   └── admin_login.php
├── includes/
│   ├── db.php
│   ├── login.php
│   └── login_auth.php
├── public/
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
└── .env
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
