<?php
session_start();
require_once 'connexion.php';

$email = $_POST['email'];
$mdp   = $_POST['mdp'];

$stmt = mysqli_prepare($conn, "SELECT * FROM utilisateur WHERE email = ? AND role = 'admin' LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if ($user && password_verify($mdp, $user['mdp'])) {
    $_SESSION['admin']     = true;
    $_SESSION['admin_nom'] = $user['nom'];
    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: admin_login.php?error=1");
    exit();
}

mysqli_close($conn);
?>