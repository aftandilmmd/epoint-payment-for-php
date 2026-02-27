<?php

use Aftandilmmd\EpointPayment\Services\SignatureGenerator;

it('generates a correct signature', function () {
    $generator = new SignatureGenerator('d3hjsl38sd8kdfhbcea0be04eafde9e8e2bad2fb092d');

    $data = base64_encode(json_encode([
        'public_key' => 'i000000001',
        'amount' => '30.75',
        'currency' => 'AZN',
        'description' => 'test payment',
        'order_id' => '1',
    ]));

    $signature = $generator->generate($data);

    expect($signature)->toBeString()
        ->not->toBeEmpty();

    // Verify the signature matches what we'd expect from the algorithm
    $expected = base64_encode(sha1('d3hjsl38sd8kdfhbcea0be04eafde9e8e2bad2fb092d'.$data.'d3hjsl38sd8kdfhbcea0be04eafde9e8e2bad2fb092d', true));
    expect($signature)->toBe($expected);
});

it('verifies a valid signature', function () {
    $generator = new SignatureGenerator('test-private-key');

    $data = base64_encode(json_encode(['test' => 'data']));
    $signature = $generator->generate($data);

    expect($generator->verify($data, $signature))->toBeTrue();
});

it('rejects an invalid signature', function () {
    $generator = new SignatureGenerator('test-private-key');

    $data = base64_encode(json_encode(['test' => 'data']));

    expect($generator->verify($data, 'invalid-signature'))->toBeFalse();
});
