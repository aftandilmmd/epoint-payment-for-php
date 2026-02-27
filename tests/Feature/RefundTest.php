<?php

use Aftandilmmd\EpointPayment\DTOs\Response\ReverseResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SavedCardPaymentResponse;

it('processes a refund', function () {
    $mock = createMockService([mockResponse(loadFixture('refund'))]);

    $response = $mock->service->refund([
        'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'order_id' => 'order-789',
        'amount' => 50.00,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    expect($response)->toBeInstanceOf(SavedCardPaymentResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->transaction)->toBe('tw0000000401')
        ->and($response->amount)->toBe(50.0);
});

it('sends request to refund-request endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('refund'))]);

    $mock->service->refund([
        'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'order_id' => 'order-789',
        'amount' => 50.00,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/refund-request');
});

it('reverses a transaction', function () {
    $mock = createMockService([mockResponse(loadFixture('reverse'))]);

    $response = $mock->service->reverse([
        'transaction' => 'tw0000000101',
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    expect($response)->toBeInstanceOf(ReverseResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->isSuccessful())->toBeTrue();
});

it('reverses with partial amount', function () {
    $mock = createMockService([mockResponse(loadFixture('reverse'))]);

    $response = $mock->service->reverse([
        'transaction' => 'tw0000000101',
        'amount' => 15.50,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    $request = $mock->history[0]['request'];
    $body = (string) $request->getBody();
    parse_str($body, $params);
    $decodedData = json_decode(base64_decode($params['data']), true);

    expect($decodedData['amount'])->toBe(15.50)
        ->and($response->isSuccessful())->toBeTrue();
});
