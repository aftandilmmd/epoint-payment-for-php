<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class EpointResponse
{
    public function __construct(
        public ?string $status = null,
        public ?string $message = null,
        public ?array $rawData = null,
    ) {}

    public static function fromArray(array $response): static
    {
        return new static(
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed' || $this->status === 'error';
    }
}
