<?php

use Aftandilmmd\EpointPayment\Contracts\EpointServiceInterface;
use Aftandilmmd\EpointPayment\EpointFactory;

it('creates an EpointService instance', function () {
    $service = EpointFactory::create([
        'public_key' => 'i000000001',
        'private_key' => 'test-private-key',
    ]);

    expect($service)->toBeInstanceOf(EpointServiceInterface::class);
});

it('accepts a custom Guzzle client', function () {
    $client = new GuzzleHttp\Client(['timeout' => 60]);

    $service = EpointFactory::create([
        'public_key' => 'i000000001',
        'private_key' => 'test-private-key',
    ], $client);

    expect($service)->toBeInstanceOf(EpointServiceInterface::class);
});

it('accepts a custom logger', function () {
    $logger = new class implements Psr\Log\LoggerInterface
    {
        use Psr\Log\LoggerTrait;

        public function log($level, string|\Stringable $message, array $context = []): void {}
    };

    $service = EpointFactory::create([
        'public_key' => 'i000000001',
        'private_key' => 'test-private-key',
        'logging' => ['enabled' => true],
    ], logger: $logger);

    expect($service)->toBeInstanceOf(EpointServiceInterface::class);
});
