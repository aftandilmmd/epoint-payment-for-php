<?php

use Aftandilmmd\EpointPayment\DTOs\Response\CardRegistrationResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\CardRegistrationWithPayResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\EpointResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\PaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\ReverseResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SavedCardPaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SplitPaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\WidgetResponse;

it('creates EpointResponse from array', function () {
    $response = EpointResponse::fromArray(['status' => 'success', 'message' => 'OK']);

    expect($response->status)->toBe('success')
        ->and($response->message)->toBe('OK')
        ->and($response->isSuccessful())->toBeTrue()
        ->and($response->isFailed())->toBeFalse();
});

it('detects failed EpointResponse', function () {
    $response = EpointResponse::fromArray(['status' => 'failed']);

    expect($response->isSuccessful())->toBeFalse()
        ->and($response->isFailed())->toBeTrue();
});

it('creates RedirectResponse from array', function () {
    $data = loadFixture('payment-request');
    $response = RedirectResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->transaction)->toBe('tw0000000101')
        ->and($response->redirectUrl)->toBe('https://epoint.az/pay/tw0000000101')
        ->and($response->isSuccessful())->toBeTrue();
});

it('creates PaymentResponse from array', function () {
    $data = loadFixture('payment-status');
    $response = PaymentResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->code)->toBe('000')
        ->and($response->orderId)->toBe('order-123')
        ->and($response->transaction)->toBe('tw0000000101')
        ->and($response->bankTransaction)->toBe('BT123456')
        ->and($response->operationCode)->toBe('100')
        ->and($response->rrn)->toBe('123456789012')
        ->and($response->cardName)->toBe('John Doe')
        ->and($response->cardMask)->toBe('422865******5765')
        ->and($response->amount)->toBe(30.75);
});

it('creates CardRegistrationResponse from array', function () {
    $data = loadFixture('card-registration-callback');
    $response = CardRegistrationResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->code)->toBe('000')
        ->and($response->cardId)->toBe('cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
        ->and($response->operationCode)->toBe('001')
        ->and($response->cardMask)->toBe('422865******5765');
});

it('creates CardRegistrationWithPayResponse from array', function () {
    $data = loadFixture('card-registration-with-pay-callback');
    $response = CardRegistrationWithPayResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->code)->toBe('000')
        ->and($response->cardId)->toBe('cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
        ->and($response->orderId)->toBe('order-456')
        ->and($response->operationCode)->toBe('200')
        ->and($response->amount)->toBe(100.0);
});

it('creates SavedCardPaymentResponse from array', function () {
    $data = loadFixture('saved-card-payment');
    $response = SavedCardPaymentResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->transaction)->toBe('tw0000000201')
        ->and($response->rrn)->toBe('987654321098')
        ->and($response->amount)->toBe(50.0);
});

it('creates SplitPaymentResponse from array', function () {
    $data = loadFixture('split-payment-callback');
    $response = SplitPaymentResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->amount)->toBe(100.0)
        ->and($response->splitAmount)->toBe(30.0)
        ->and($response->orderId)->toBe('order-789');
});

it('creates ReverseResponse from array', function () {
    $data = loadFixture('reverse');
    $response = ReverseResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->message)->toBe('Transaction reversed successfully')
        ->and($response->isSuccessful())->toBeTrue();
});

it('creates WidgetResponse from array', function () {
    $data = loadFixture('widget');
    $response = WidgetResponse::fromArray($data);

    expect($response->status)->toBe('success')
        ->and($response->widgetUrl)->toBe('https://epoint.az/api/1/token/widget/000001');
});
