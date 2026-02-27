<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class CardRegistrationResponse extends EpointResponse
{
    public function __construct(
        public ?string $code = null,
        public ?string $cardId = null,
        public ?string $bankTransaction = null,
        public ?string $bankResponse = null,
        public ?string $operationCode = null,
        public ?string $rrn = null,
        public ?string $cardName = null,
        public ?string $cardMask = null,
        ?string $status = null,
        ?string $message = null,
        ?array $rawData = null,
    ) {
        parent::__construct($status, $message, $rawData);
    }

    public static function fromArray(array $response): static
    {
        return new static(
            code: $response['code'] ?? null,
            cardId: $response['card_id'] ?? null,
            bankTransaction: $response['bank_transaction'] ?? null,
            bankResponse: $response['bank_response'] ?? null,
            operationCode: $response['operation_code'] ?? null,
            rrn: $response['rrn'] ?? null,
            cardName: $response['card_name'] ?? null,
            cardMask: $response['card_mask'] ?? null,
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
