<?php

use Aftandilmmd\EpointPayment\Exceptions\EpointException;

it('throws exception on connection failure', function () {
    $mock = createMockService([
        new GuzzleHttp\Exception\ConnectException('Connection refused', new GuzzleHttp\Psr7\Request('POST', '/test')),
        new GuzzleHttp\Exception\ConnectException('Connection refused', new GuzzleHttp\Psr7\Request('POST', '/test')),
        new GuzzleHttp\Exception\ConnectException('Connection refused', new GuzzleHttp\Psr7\Request('POST', '/test')),
        new GuzzleHttp\Exception\ConnectException('Connection refused', new GuzzleHttp\Psr7\Request('POST', '/test')),
    ]);

    $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-fail',
    ]);
})->throws(EpointException::class, 'Epoint API connection failed');

it('throws exception on 4xx error response', function () {
    $mock = createMockService([
        mockResponse(['message' => 'Bad Request'], 400),
    ]);

    $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-bad',
    ]);
})->throws(EpointException::class, 'Bad Request');

it('retries on 5xx error and succeeds', function () {
    $mock = createMockService([
        mockResponse(['message' => 'Server Error'], 500),
        mockResponse(loadFixture('payment-request')),
    ]);

    $response = $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-retry',
    ]);

    expect($response->status)->toBe('success')
        ->and($mock->history)->toHaveCount(2);
});

it('automatically injects public_key into requests', function () {
    $mock = createMockService([mockResponse(loadFixture('payment-request'))]);

    $mock->service->createPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-auto-pk',
    ]);

    $request = $mock->history[0]['request'];
    $body = (string) $request->getBody();
    parse_str($body, $params);
    $decodedData = json_decode(base64_decode($params['data']), true);

    expect($decodedData)->toHaveKey('public_key')
        ->and($decodedData['public_key'])->toBe('i000000001');
});
