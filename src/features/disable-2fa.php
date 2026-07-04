<?php

declare(strict_types=1);

session_start();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_SESSION['csrf_token'])
    && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')
) {
    unset($_SESSION['2fa_enabled'], $_SESSION['2fa_secret']);
}

header('Location: setup-2fa.php');
exit;
