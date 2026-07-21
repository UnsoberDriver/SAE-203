<?php
require_once __DIR__ . '/../includes/db.php';

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$email = $_POST['email'];
$mdp = password_hash($_POST['message'], PASSWORD_DEFAULT);

// Vérifie si l'email est déjà utilisé
$check = mysqli_prepare($conn, "SELECT id_utilisateur FROM utilisateur WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($check, "s", $email);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    header("Location: index.php?erreur=email_existe");
    exit();
}

$sql = "INSERT INTO utilisateur (prenom, nom, email, mdp) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $prenom, $nom, $email, $mdp);

if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php");
    exit();
} else {
    echo "❌ Erreur : " . mysqli_error($conn);
}

mysqli_close($conn);
?>