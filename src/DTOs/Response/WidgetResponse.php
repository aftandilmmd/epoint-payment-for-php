<?php

namespace Aftandilmmd\EpointPayment\DTOs\Response;

readonly class WidgetResponse extends EpointResponse
{
    public function __construct(
        public ?string $widgetUrl = null,
        ?string $status = null,
        ?string $message = null,
        ?array $rawData = null,
    ) {
        parent::__construct($status, $message, $rawData);
    }

    public static function fromArray(array $response): static
    {
        return new static(
            widgetUrl: $response['widget_url'] ?? null,
            status: $response['status'] ?? null,
            message: $response['message'] ?? null,
            rawData: $response,
        );
    }
}
