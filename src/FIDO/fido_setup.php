<?php
require 'vendor/autoload.php';

use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredentialRequestOptions;

session_start();

// FIDO2 Registration Setup
$rpEntity = new PublicKeyCredentialRpEntity(
    'Your Application Name', // Display Name
    'your-app-domain.com'    // ID (domain)
);

$userEntity = new PublicKeyCredentialUserEntity(
    'user@example.com',      // User Email or unique username
    '123456',                // Unique User ID (binary form)
    'User Display Name'      // Display Name
);

$pubKeyCredParams = [
    new PublicKeyCredentialParameters('public-key', -7),  // Alg -7 is for ES256 (Elliptic Curve)
];

// Set registration options
$authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria();
$authenticatorSelectionCriteria->setUserVerification(AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED);

$creationOptions = new PublicKeyCredentialCreationOptions(
    $rpEntity,
    $userEntity,
    random_bytes(16),        // Challenge
    $pubKeyCredParams
);

$_SESSION['challenge'] = $creationOptions->getChallenge();  // Save challenge in session

// JSON encode options and return to frontend
header('Content-Type: application/json');
echo json_encode($creationOptions);
?>
