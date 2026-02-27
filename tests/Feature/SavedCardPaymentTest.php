<?php

use Aftandilmmd\EpointPayment\DTOs\Response\SavedCardPaymentResponse;

it('pays with a saved card', function () {
    $mock = createMockService([mockResponse(loadFixture('saved-card-payment'))]);

    $response = $mock->service->payWithSavedCard([
        'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'order_id' => 'order-456',
        'amount' => 50.00,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    expect($response)->toBeInstanceOf(SavedCardPaymentResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->transaction)->toBe('tw0000000201')
        ->and($response->rrn)->toBe('987654321098')
        ->and($response->amount)->toBe(50.0);
});

it('sends request to execute-pay endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('saved-card-payment'))]);

    $mock->service->payWithSavedCard([
        'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'order_id' => 'order-456',
        'amount' => 50.00,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/execute-pay');
});
