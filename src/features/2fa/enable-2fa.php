<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$gAuth = new GoogleAuthenticator();

if (!isset($_SESSION['2fa_secret'])) {
    $_SESSION['2fa_secret'] = $gAuth->generateSecret();
}

$message = null;
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $message = 'Sessão expirada. Recarregue a página e tente novamente.';
        $messageType = 'danger';
    } else {
        $userCode = trim((string) ($_POST['2fa_code'] ?? ''));

        if ($userCode === '' || !ctype_digit($userCode)) {
            $message = 'Informe o código de 6 dígitos gerado pelo aplicativo autenticador.';
            $messageType = 'warning';
        } elseif ($gAuth->checkCode($_SESSION['2fa_secret'], $userCode)) {
            $_SESSION['2fa_enabled'] = true;
            $message = 'Autenticação de dois fatores ativada com sucesso!';
            $messageType = 'success';
        } else {
            $message = 'Código inválido. Tente novamente.';
            $messageType = 'danger';
        }
    }
}

$issuer = 'GStraccini-bot Playground';
$accountName = 'sessao-' . substr(session_id(), 0, 8);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração de 2FA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Configuração de Autenticação de Dois Fatores</h2>

    <?php if ($message !== null): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <?php if (empty($_SESSION['2fa_enabled'])): ?>
                <h4>Escaneie o QR code abaixo com o Google Authenticator</h4>
                <p>Chave secreta: <strong><?php echo htmlspecialchars($_SESSION['2fa_secret'], ENT_QUOTES); ?></strong></p>
                <img src="<?php echo htmlspecialchars(GoogleQrUrl::generate($accountName, $_SESSION['2fa_secret'], $issuer), ENT_QUOTES); ?>" alt="QR Code" class="img-fluid mb-4">

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                    <div class="mb-3">
                        <label for="2fa_code" class="form-label">Digite o código do aplicativo:</label>
                        <input type="text" id="2fa_code" name="2fa_code" class="form-control" placeholder="123456" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-shield-alt"></i> Ativar 2FA
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success">
                    O 2FA já está ativado.
                </div>
                <form method="POST" action="disable-2fa.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times-circle"></i> Desativar 2FA
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
