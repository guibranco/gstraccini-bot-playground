<?php
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

session_start();

// Initialize Google Authenticator
$gAuth = new GoogleAuthenticator();

if (!isset($_SESSION['2fa_secret'])) {
    $_SESSION['2fa_secret'] = $gAuth->generateSecret();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCode = $_POST['2fa_code'] ?? null;

    if ($gAuth->checkCode($_SESSION['2fa_secret'], $userCode)) {
        $_SESSION['2fa_enabled'] = true;
        $message = '2FA enabled successfully!';
    } else {
        $message = 'Invalid code. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Setup</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Two-Factor Authentication Setup</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-info">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <?php if (!isset($_SESSION['2fa_enabled']) || !$_SESSION['2fa_enabled']): ?>
                <h4>Scan the QR code below with Google Authenticator</h4>
                <p>Secret: <strong><?php echo $_SESSION['2fa_secret']; ?></strong></p>
                <img src="<?php echo GoogleQrUrl::generate('YourAppName', $_SESSION['2fa_secret'], 'YourAppDomain'); ?>" alt="QR Code" class="img-fluid mb-4">

                <form method="POST">
                    <div class="mb-3">
                        <label for="2fa_code" class="form-label">Enter the code from your app:</label>
                        <input type="text" id="2fa_code" name="2fa_code" class="form-control" placeholder="123456" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-shield-alt"></i> Enable 2FA
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success">
                    2FA is already enabled.
                </div>
                <form method="POST" action="disable_2fa.php">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times-circle"></i> Disable 2FA
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
