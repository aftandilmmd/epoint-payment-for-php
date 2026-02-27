<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class ReverseResponse extends EpointResponse
{
    public function __construct(
        ?string $status = null,
        ?string $message = null,
        ?array $rawData = null,
    ) {
        parent::__construct($status, $message, $rawData);
    }

    public static function fromArray(array $response): static
    {
        return new static(
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
