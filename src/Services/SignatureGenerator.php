<?php

namespace Aftandilmmd\EpointPayment\Services;

class SignatureGenerator
{
    public function __construct(
        private string $privateKey,
    ) {}

    /**
     * Generate the signature for an Epoint API request.
     *
     * Algorithm: base64_encode(sha1(private_key + data + private_key, true))
     */
    public function generate(string $data): string
    {
        return base64_encode(sha1($this->privateKey.$data.$this->privateKey, true));
    }

    /**
     * Verify a signature received from Epoint callback.
     */
    public function verify(string $data, string $signature): bool
    {
        return hash_equals($this->generate($data), $signature);
    }
}
