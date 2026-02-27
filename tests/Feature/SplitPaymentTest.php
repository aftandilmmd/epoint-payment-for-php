<?php

use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SplitPaymentResponse;

it('creates a split payment request', function () {
    $mock = createMockService([mockResponse(loadFixture('split-request'))]);

    $response = $mock->service->createSplitPayment([
        'amount' => 100.00,
        'split_user' => 'i000000002',
        'split_amount' => 30.00,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-789',
    ]);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->transaction)->toBe('tw0000000501')
        ->and($response->redirectUrl)->toBe('https://epoint.az/pay/tw0000000501');
});

it('pays split payment with saved card', function () {
    $mock = createMockService([mockResponse(loadFixture('split-saved-card-payment'))]);

    $response = $mock->service->splitPayWithSavedCard([
        'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'order_id' => 'order-999',
        'amount' => 100.00,
        'split_user' => 'i000000002',
        'split_amount' => 30.00,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    expect($response)->toBeInstanceOf(SplitPaymentResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->amount)->toBe(100.0)
        ->and($response->splitAmount)->toBe(30.0);
});

it('creates split card registration with payment', function () {
    $mock = createMockService([mockResponse(loadFixture('card-registration-with-pay'))]);

    $response = $mock->service->splitRegisterCardWithPay([
        'order_id' => 'order-111',
        'amount' => 100.00,
        'split_user' => 'i000000002',
        'split_amount' => 30.00,
        'currency' => 'AZN',
        'language' => 'az',
    ]);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->cardId)->toBe('cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
});

it('sends split request to correct endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('split-request'))]);

    $mock->service->createSplitPayment([
        'amount' => 100.00,
        'split_user' => 'i000000002',
        'split_amount' => 30.00,
        'currency' => 'AZN',
        'language' => 'az',
        'order_id' => 'order-789',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/split-request');
});
