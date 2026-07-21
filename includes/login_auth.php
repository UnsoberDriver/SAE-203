<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$email = $_POST['email'];
$mdp   = $_POST['mdp'];

$stmt = mysqli_prepare($conn, "SELECT * FROM utilisateur WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if ($user && password_verify($mdp, $user['mdp'])) {
    $_SESSION['user']        = true;
    $_SESSION['user_id']     = $user['id_utilisateur'];
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_nom']    = $user['nom'];

    if (isset($user['role']) && $user['role'] === 'admin') {
        $_SESSION['admin']     = true;
        $_SESSION['admin_nom'] = $user['nom'];
        header("Location: admin_dashboard.php");
        exit();
    }

    header("Location: index.php");
    exit();
} else {
    $_SESSION['login_error'] = true;
    $_SESSION['login_error_shown'] = true;
    header("Location: index.php");
    exit();
}

mysqli_close($conn);
?>