<?php

use Aftandilmmd\EpointPayment\DTOs\Response\EpointResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;

it('creates a pre-auth payment request', function () {
    $mock = createMockService([mockResponse(loadFixture('pre-auth-request'))]);

    $response = $mock->service->createPreAuthPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-preauth-1',
        'description' => 'Test pre-auth payment',
    ]);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->transaction)->toBe('te0000000101')
        ->and($response->redirectUrl)->toBe('https://epoint.az/pay/te0000000101');
});

it('sends pre-auth request to correct endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('pre-auth-request'))]);

    $mock->service->createPreAuthPayment([
        'amount' => 30.75,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-preauth-1',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/pre-auth-request');
});

it('completes a pre-auth payment', function () {
    $mock = createMockService([mockResponse(loadFixture('pre-auth-complete'))]);

    $response = $mock->service->completePreAuth([
        'amount' => 30.75,
        'transaction' => 'te0000000101',
    ]);

    expect($response)->toBeInstanceOf(EpointResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->isSuccessful())->toBeTrue();
});

it('sends complete pre-auth to correct endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('pre-auth-complete'))]);

    $mock->service->completePreAuth([
        'amount' => 30.75,
        'transaction' => 'te0000000101',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/pre-auth-complete');
});
