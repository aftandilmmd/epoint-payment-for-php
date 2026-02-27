<?php

use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;

it('registers a card', function () {
    $mock = createMockService([mockResponse(loadFixture('card-registration'))]);

    $response = $mock->service->registerCard([
        'language' => 'az',
    ]);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->status)->toBe('success')
        ->and($response->redirectUrl)->toBe('https://epoint.az/pay/cr0000000101')
        ->and($response->cardId)->toBe('cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
});

it('sends request to card-registration endpoint', function () {
    $mock = createMockService([mockResponse(loadFixture('card-registration'))]);

    $mock->service->registerCard([
        'language' => 'az',
    ]);

    $request = $mock->history[0]['request'];
    expect((string) $request->getUri())->toContain('/api/1/card-registration');
});
