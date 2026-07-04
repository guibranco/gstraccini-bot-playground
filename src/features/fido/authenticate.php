<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Webauthn\PublicKeyCredentialRequestOptions;

$username = trim($_GET['username'] ?? '');
if ($username === '') {
    fido_json_error(400, 'Informe um nome de usuário.');
}

$credentials = $credentialStore->findAllForUsername($username);
if ($credentials === []) {
    fido_json_error(404, 'Nenhum dispositivo FIDO registrado para este usuário.');
}

$allowCredentials = array_map(
    static fn($record) => $record->getPublicKeyCredentialDescriptor(),
    $credentials
);

$requestOptions = PublicKeyCredentialRequestOptions::create(
    random_bytes(32),
    FIDO_RP_ID,
    $allowCredentials,
    PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED
);

$_SESSION['fido_ceremony'] = 'login';
$_SESSION['fido_username'] = $username;
$_SESSION['fido_options'] = $webauthnSerializer->serialize($requestOptions, 'json');

header('Content-Type: application/json');
echo $_SESSION['fido_options'];
