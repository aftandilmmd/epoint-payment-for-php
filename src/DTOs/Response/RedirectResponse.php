<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class RedirectResponse extends EpointResponse
{
    public function __construct(
        public ?string $transaction = null,
        public ?string $redirectUrl = null,
        public ?string $cardId = null,
        ?string $status = null,
        ?string $message = null,
        ?array $rawData = null,
    ) {
        parent::__construct($status, $message, $rawData);
    }

    public static function fromArray(array $response): static
    {
        return new static(
            transaction: $response['transaction'] ?? null,
            redirectUrl: $response['redirect_url'] ?? null,
            cardId: $response['card_id'] ?? null,
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
