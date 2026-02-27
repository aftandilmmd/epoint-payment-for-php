<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class SplitPaymentResponse extends EpointResponse
{
    public function __construct(
        public ?string $orderId = null,
        public ?string $code = null,
        public ?string $transaction = null,
        public ?string $bankTransaction = null,
        public ?string $bankResponse = null,
        public ?string $operationCode = null,
        public ?string $rrn = null,
        public ?string $cardName = null,
        public ?string $cardMask = null,
        public ?float $amount = null,
        public ?float $splitAmount = null,
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
            orderId: $response['order_id'] ?? null,
            code: $response['code'] ?? null,
            transaction: $response['transaction'] ?? null,
            bankTransaction: $response['bank_transaction'] ?? null,
            bankResponse: $response['bank_response'] ?? null,
            operationCode: $response['operation_code'] ?? null,
            rrn: $response['rrn'] ?? null,
            cardName: $response['card_name'] ?? null,
            cardMask: $response['card_mask'] ?? null,
            amount: isset($response['amount']) ? (float) $response['amount'] : null,
            splitAmount: isset($response['split_amount']) ? (float) $response['split_amount'] : null,
            otherAttr: $response['other_attr'] ?? null,
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
