<?php

use Aftandilmmd\EpointPayment\DTOs\Response\WidgetResponse;

it('creates a widget URL for Apple Pay / Google Pay', function () {
    $mock = createMockService([mockResponse(loadFixture('widget'))]);

    $response = $mock->service->createWidget([
        'amount' => 2.50,
        'order_id' => 'order-widget-1',
        'description' => 'Test payment',
    ]);

    expect($response)->toBeInstanceOf(WidgetResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->widgetUrl)->toBe('https://epoint.az/api/1/token/widget/000001');
});

it('sends widget request to correct endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('widget'))]);

    $mock->service->createWidget([
        'amount' => 2.50,
        'order_id' => 'order-widget-1',
        'description' => 'Test payment',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/token/widget');
});
