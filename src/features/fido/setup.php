<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

$username = trim($_GET['username'] ?? '');
if ($username === '') {
    fido_json_error(400, 'Informe um nome de usuário.');
}

$rpEntity = PublicKeyCredentialRpEntity::create(FIDO_RP_NAME, FIDO_RP_ID);
$userEntity = PublicKeyCredentialUserEntity::create($username, fido_user_handle($username), $username);

$excludeCredentials = array_map(
    static fn($record) => $record->getPublicKeyCredentialDescriptor(),
    $credentialStore->findAllForUsername($username)
);

$creationOptions = PublicKeyCredentialCreationOptions::create(
    $rpEntity,
    $userEntity,
    random_bytes(32),
    [
        PublicKeyCredentialParameters::createPk(-7),   // ES256
        PublicKeyCredentialParameters::createPk(-257),  // RS256
    ],
    AuthenticatorSelectionCriteria::create(
        userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED
    ),
    PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
    $excludeCredentials
);

$_SESSION['fido_ceremony'] = 'register';
$_SESSION['fido_username'] = $username;
$_SESSION['fido_options'] = $webauthnSerializer->serialize($creationOptions, 'json');

header('Content-Type: application/json');
echo $_SESSION['fido_options'];
