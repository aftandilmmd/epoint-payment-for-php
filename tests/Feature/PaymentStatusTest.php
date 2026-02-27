<?php

use Aftandilmmd\EpointPayment\DTOs\Response\PaymentResponse;

it('checks payment status', function () {
    $mock = createMockService([mockResponse(loadFixture('payment-status'))]);

    $response = $mock->service->getPaymentStatus([
        'transaction' => 'tw0000000101',
    ]);

    expect($response)->toBeInstanceOf(PaymentResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->code)->toBe('000')
        ->and($response->orderId)->toBe('order-123')
        ->and($response->transaction)->toBe('tw0000000101')
        ->and($response->rrn)->toBe('123456789012')
        ->and($response->cardMask)->toBe('422865******5765')
        ->and($response->amount)->toBe(30.75);
});

it('sends request to correct endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('payment-status'))]);

    $mock->service->getPaymentStatus([
        'transaction' => 'tw0000000101',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/get-status');
});
