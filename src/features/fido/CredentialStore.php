<?php

declare(strict_types=1);

use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\CredentialRecord;

/**
 * Minimal JSON-file backed persistence for WebAuthn credential records, keyed by username.
 * Good enough for this playground; swap for a real database table in production.
 */
final class CredentialStore
{
    public function __construct(
        private readonly string $filePath,
        private readonly SerializerInterface $serializer
    ) {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        if (!file_exists($filePath)) {
            file_put_contents($filePath, '{}');
        }
    }

    /**
     * @return CredentialRecord[]
     */
    public function findAllForUsername(string $username): array
    {
        $data = $this->read();
        $records = $data[$username] ?? [];

        return array_map(
            fn(array $record): CredentialRecord => $this->serializer->denormalize(
                $record,
                CredentialRecord::class,
                'json'
            ),
            $records
        );
    }

    public function findByCredentialId(string $username, string $credentialId): ?CredentialRecord
    {
        foreach ($this->findAllForUsername($username) as $record) {
            if (hash_equals($record->publicKeyCredentialId, $credentialId)) {
                return $record;
            }
        }

        return null;
    }

    public function save(string $username, CredentialRecord $record): void
    {
        $handle = fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Não foi possível abrir o armazenamento de credenciais.');
        }

        flock($handle, LOCK_EX);

        $contents = stream_get_contents($handle);
        $data = $contents !== false && $contents !== '' ? (json_decode($contents, true) ?: []) : [];

        $normalized = $this->serializer->normalize($record, 'json');
        $userRecords = $data[$username] ?? [];

        $found = false;
        foreach ($userRecords as $index => $existing) {
            if (($existing['publicKeyCredentialId'] ?? null) === $normalized['publicKeyCredentialId']) {
                $userRecords[$index] = $normalized;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $userRecords[] = $normalized;
        }
        $data[$username] = $userRecords;

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function read(): array
    {
        $contents = file_get_contents($this->filePath);
        if ($contents === false || $contents === '') {
            return [];
        }
        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }
}
