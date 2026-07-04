<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

header('Content-Type: application/json');

$ceremony = $_SESSION['fido_ceremony'] ?? null;
$username = $_SESSION['fido_username'] ?? null;
$optionsJson = $_SESSION['fido_options'] ?? null;

if ($ceremony === null || $username === null || $optionsJson === null) {
    fido_json_error(400, 'Nenhuma cerimônia FIDO em andamento. Reinicie o processo.');
}

try {
    $body = file_get_contents('php://input');
    $publicKeyCredential = $webauthnSerializer->deserialize($body, PublicKeyCredential::class, 'json');

    if ($ceremony === 'register') {
        $creationOptions = $webauthnSerializer->deserialize(
            $optionsJson,
            PublicKeyCredentialCreationOptions::class,
            'json'
        );

        $response = $publicKeyCredential->response;
        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new RuntimeException('Resposta inesperada para o registro do dispositivo.');
        }

        $credentialRecord = $attestationResponseValidator->check($response, $creationOptions, FIDO_RP_ID);
        $credentialStore->save($username, $credentialRecord);

        echo json_encode(['success' => true, 'message' => 'Dispositivo FIDO registrado com sucesso!']);
    } elseif ($ceremony === 'login') {
        $requestOptions = $webauthnSerializer->deserialize(
            $optionsJson,
            PublicKeyCredentialRequestOptions::class,
            'json'
        );

        $response = $publicKeyCredential->response;
        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new RuntimeException('Resposta inesperada para a autenticação.');
        }

        $storedRecord = $credentialStore->findByCredentialId($username, $publicKeyCredential->rawId);
        if ($storedRecord === null) {
            throw new RuntimeException('Credencial não reconhecida para este usuário.');
        }

        $updatedRecord = $assertionResponseValidator->check(
            $storedRecord,
            $response,
            $requestOptions,
            FIDO_RP_ID,
            fido_user_handle($username)
        );
        $credentialStore->save($username, $updatedRecord);

        echo json_encode(['success' => true, 'message' => 'Autenticado com sucesso!']);
    } else {
        throw new RuntimeException('Cerimônia FIDO desconhecida.');
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    unset($_SESSION['fido_ceremony'], $_SESSION['fido_username'], $_SESSION['fido_options']);
}
