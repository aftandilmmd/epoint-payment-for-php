<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class CardRegistrationWithPayResponse extends EpointResponse
{
    public function __construct(
        public ?string $code = null,
        public ?string $cardId = null,
        public ?string $orderId = null,
        public ?string $transaction = null,
        public ?string $bankTransaction = null,
        public ?string $bankResponse = null,
        public ?string $operationCode = null,
        public ?string $rrn = null,
        public ?string $cardMask = null,
        public ?string $cardName = null,
        public ?float $amount = null,
        public ?array $otherAttr = null,
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
            orderId: $response['order_id'] ?? null,
            transaction: $response['transaction'] ?? null,
            bankTransaction: $response['bank_transaction'] ?? null,
            bankResponse: $response['bank_response'] ?? null,
            operationCode: $response['operation_code'] ?? null,
            rrn: $response['rrn'] ?? null,
            cardMask: $response['card_mask'] ?? null,
            cardName: $response['card_name'] ?? null,
            amount: isset($response['amount']) ? (float) $response['amount'] : null,
            otherAttr: $response['other_attr'] ?? null,
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
