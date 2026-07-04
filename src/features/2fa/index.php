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
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1e40af;
            --light-blue: #3b82f6;
            --accent-blue: #60a5fa;
            --bg-blue: #eff6ff;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border-color: #e2e8f0;
            --success-green: #10b981;
            --danger-red: #ef4444;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-container {
            max-width: 640px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);
        }

        .header-section h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .header-section p {
            margin: 0;
            opacity: 0.9;
        }

        .account-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--light-blue));
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-blue));
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-red), #dc2626);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            font-weight: 500;
        }

        .qr-code-container {
            text-align: center;
            background: var(--bg-blue);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .qr-code-container img {
            border-radius: 0.5rem;
            background: white;
            padding: 0.75rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .secret-key {
            display: inline-block;
            background: white;
            border: 1px dashed var(--primary-blue);
            border-radius: 0.5rem;
            padding: 0.25rem 0.75rem;
            color: var(--dark-blue);
            font-family: monospace;
            letter-spacing: 0.05em;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="header-section">
        <h1>
            <div class="header-icon">
                <i class="fa fa-mobile-alt"></i>
            </div>
            <div>Autenticação de Dois Fatores</div>
        </h1>
        <p>Proteja sua conta com um código gerado pelo seu aplicativo autenticador.</p>
    </div>

    <?php if ($message !== null): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES); ?> mb-4">
            <?php echo htmlspecialchars($message, ENT_QUOTES); ?>
        </div>
    <?php endif; ?>

    <div class="account-card">
        <?php if (empty($_SESSION['2fa_enabled'])): ?>
            <h4 class="mb-3"><i class="fa fa-qrcode me-2 text-primary"></i>Escaneie o QR code com o Google Authenticator</h4>
            <div class="qr-code-container">
                <img src="<?php echo htmlspecialchars(GoogleQrUrl::generate($accountName, $_SESSION['2fa_secret'], $issuer), ENT_QUOTES); ?>" alt="QR Code" class="img-fluid">
            </div>
            <p>Chave secreta: <span class="secret-key"><?php echo htmlspecialchars($_SESSION['2fa_secret'], ENT_QUOTES); ?></span></p>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                <div class="mb-3">
                    <label for="2fa_code" class="form-label">Digite o código do aplicativo:</label>
                    <input type="text" id="2fa_code" name="2fa_code" class="form-control" placeholder="123456" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-shield-alt me-2"></i>Ativar 2FA
                </button>
            </form>
        <?php else: ?>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0"><i class="fa fa-shield-alt me-2 text-success"></i>2FA Ativo</h4>
                <span class="status-badge"><i class="fa fa-circle"></i> Ativo</span>
            </div>
            <div class="alert alert-success">
                O 2FA já está ativado e protegendo sua conta.
            </div>
            <form method="POST" action="disable-2fa.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-times-circle me-2"></i>Desativar 2FA
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
