<?php

use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;

it('creates a payment request', function () {
    $mock = createMockService([mockResponse(loadFixture('payment-request'))]);

    $response = $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-123',
        'description' => 'Test payment',
    ]);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->transaction)->toBe('tw0000000101')
        ->and($response->redirectUrl)->toBe('https://epoint.az/pay/tw0000000101')
        ->and($response->isSuccessful())->toBeTrue();
});

it('sends correct data and signature in payment request', function () {
    $mock = createMockService([mockResponse(loadFixture('payment-request'))]);

    $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-123',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/request');
    expect($request->getMethod())->toBe('POST');

    $body = (string) $request->getBody();
    parse_str($body, $params);
    expect($params)->toHaveKeys(['data', 'signature']);

    $decodedData = json_decode(base64_decode($params['data']), true);
    expect($decodedData['public_key'])->toBe('i000000001')
        ->and($decodedData['amount'])->toBe(30.75)
        ->and($decodedData['currency'])->toBe('AZN');
});

it('handles payment error response', function () {
    $mock = createMockService([mockResponse(loadFixture('error'))]);

    $response = $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-123',
    ]);

    expect($response->status)->toBe('error')
        ->and($response->isSuccessful())->toBeFalse()
        ->and($response->isFailed())->toBeTrue();
});
