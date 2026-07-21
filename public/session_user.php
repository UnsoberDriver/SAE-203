<?php
session_start();
header('Content-Type: application/json');

if (!empty($_SESSION['user_id'])) {
    echo json_encode(['connected' => true, 'user_id' => $_SESSION['user_id']]);
} else {
    echo json_encode(['connected' => false]);
}
?>