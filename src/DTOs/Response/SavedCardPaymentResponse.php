<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class SavedCardPaymentResponse extends EpointResponse
{
    public function __construct(
        public ?string $transaction = null,
        public ?string $bankTransaction = null,
        public ?string $bankResponse = null,
        public ?string $rrn = null,
        public ?string $cardName = null,
        public ?string $cardMask = null,
        public ?float $amount = null,
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
            bankTransaction: $response['bank_transaction'] ?? null,
            bankResponse: $response['bank_response'] ?? null,
            rrn: $response['rrn'] ?? null,
            cardName: $response['card_name'] ?? null,
            cardMask: $response['card_mask'] ?? null,
            amount: isset($response['amount']) ? (float) $response['amount'] : null,
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
