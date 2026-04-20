<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    echo "NO SESSION";
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    echo "NOT ADMIN";
    exit();
}

echo "WELCOME ADMIN";
?>