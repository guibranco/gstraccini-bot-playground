<?php

declare(strict_types=1);

use Webauthn\CredentialRecord;
use Webauthn\Counter\CounterChecker;
use Webauthn\Exception\CounterException;

/**
 * Many platform authenticators (Windows Hello, Touch ID/Face ID, most passkey
 * implementations) never increment the signature counter and always report 0.
 * The library's default checker treats that as a clone attack; this one only
 * enforces strictly-increasing counters once the authenticator has proven it
 * actually uses them.
 */
final class LenientCounterChecker implements CounterChecker
{
    public function check(CredentialRecord $credentialRecord, int $currentCounter): void
    {
        if ($currentCounter === 0 && $credentialRecord->counter === 0) {
            return;
        }

        $currentCounter > $credentialRecord->counter || throw CounterException::create(
            $currentCounter,
            $credentialRecord->counter,
            'Invalid counter — possible cloned authenticator.'
        );
    }
}
