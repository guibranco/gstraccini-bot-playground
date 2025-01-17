<?php
require 'vendor/autoload.php';

use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialDescriptor;

session_start();

// Dummy user credential source repository (replace with your DB logic)
class SimpleCredentialSourceRepository implements PublicKeyCredentialSourceRepository {
    public function findAllForUserEntity($userEntity) {
        // You need to fetch all credentials for the user from your DB/storage
        // Example:
        return [new PublicKeyCredentialDescriptor('public-key', base64_decode('credential_id'))];
    }
}

// Fetch stored credentials for the user (replace '123456' with your user's unique ID)
$credentialRepository = new SimpleCredentialSourceRepository();
$userEntity = '123456';  // Replace with the current user's unique ID

$storedCredentials = $credentialRepository->findAllForUserEntity($userEntity);

if (!$storedCredentials) {
    echo json_encode(['error' => 'No credentials registered for this user']);
    exit();
}

// Create request options for FIDO authentication
$challenge = random_bytes(32);
$requestOptions = new PublicKeyCredentialRequestOptions(
    $challenge,         // Challenge to be verified with the authenticator
    60000,              // Timeout in milliseconds
    'your-app-domain.com',  // The relying party ID (usually your domain)
    $storedCredentials  // List of credential descriptors (user's registered credentials)
);

$_SESSION['challenge'] = $requestOptions->getChallenge();  // Save challenge in session

// Return request options to frontend
header('Content-Type: application/json');
echo json_encode($requestOptions);
?>
