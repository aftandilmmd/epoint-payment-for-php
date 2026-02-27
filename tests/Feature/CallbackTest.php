<?php

it('verifies a valid callback signature', function () {
    $mock = createMockService([]);

    $data = base64_encode(json_encode(['status' => 'success', 'order_id' => '123']));
    $privateKey = 'd3hjsl38sd8kdfhbcea0be04eafde9e8e2bad2fb092d';
    $signature = base64_encode(sha1($privateKey.$data.$privateKey, true));

    expect($mock->service->verifyCallback($data, $signature))->toBeTrue();
});

it('rejects an invalid callback signature', function () {
    $mock = createMockService([]);

    $data = base64_encode(json_encode(['status' => 'success', 'order_id' => '123']));

    expect($mock->service->verifyCallback($data, 'invalid-signature'))->toBeFalse();
});

it('decodes callback data', function () {
    $mock = createMockService([]);

    $originalData = ['status' => 'success', 'order_id' => '123', 'amount' => 30.75];
    $encodedData = base64_encode(json_encode($originalData));

    $decoded = $mock->service->decodeCallbackData($encodedData);

    expect($decoded)->toBe($originalData);
});
