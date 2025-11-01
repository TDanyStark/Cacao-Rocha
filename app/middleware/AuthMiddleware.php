<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectIfAuthenticated() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SERVER['REQUEST_URI'] === "/cacaorocha/") {
        header("Location: /cacaorocha/transactions");
        exit;
    }
}

function redirectIfNotAuthenticated() {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['role']) && $_SERVER['REQUEST_URI'] === "/cacaorocha/transactions") {
        session_destroy();
        header("Location: /cacaorocha/");
        exit;
    }
}
