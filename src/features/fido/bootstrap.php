<?php

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/CredentialStore.php';
require __DIR__ . '/LenientCounterChecker.php';

use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

session_start();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('FIDO_ORIGIN', $scheme . '://' . $host);
define('FIDO_RP_ID', (string) (parse_url(FIDO_ORIGIN, PHP_URL_HOST) ?: 'localhost'));
define('FIDO_RP_NAME', 'GStraccini-bot Playground');

$attestationStatementSupportManager = AttestationStatementSupportManager::create();

$ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
$ceremonyStepManagerFactory->setAllowedOrigins([FIDO_ORIGIN]);
$ceremonyStepManagerFactory->setAttestationStatementSupportManager($attestationStatementSupportManager);
$ceremonyStepManagerFactory->setCounterChecker(new LenientCounterChecker());

$attestationResponseValidator = AuthenticatorAttestationResponseValidator::create(
    $ceremonyStepManagerFactory->creationCeremony()
);
$assertionResponseValidator = AuthenticatorAssertionResponseValidator::create(
    $ceremonyStepManagerFactory->requestCeremony()
);

$webauthnSerializer = (new WebauthnSerializerFactory($attestationStatementSupportManager))->create();

$credentialStore = new CredentialStore(__DIR__ . '/storage/credentials.json', $webauthnSerializer);

function fido_user_handle(string $username): string
{
    return hash('sha256', $username, true);
}

function fido_json_error(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}
