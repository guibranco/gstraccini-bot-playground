<?php
require 'vendor/autoload.php';

use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialLoader;

session_start();

// Dummy user source repository (implement in your app to store/retrieve FIDO keys)
class SimpleCredentialSourceRepository implements PublicKeyCredentialSourceRepository {
    public function findOneByCredentialId($credentialId) {
        // Fetch user credentials from database/storage
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource) {
        // Save the credential in the storage
    }
}

// Load response from the client (WebAuthn)
$publicKeyCredentialLoader = new PublicKeyCredentialLoader();
$publicKeyCredential = $publicKeyCredentialLoader->load(file_get_contents('php://input'));

// Verify the assertion
$assertionResponse = $publicKeyCredential->getResponse();
$assertionResponse->verify($_SESSION['challenge'], $expectedData, $userVerificationRequirement = 'preferred');

// Return success or failure
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
