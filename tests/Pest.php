<?php

use Aftandilmmd\EpointPayment\EpointFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

function loadFixture(string $name): array
{
    return json_decode(
        file_get_contents(__DIR__.'/Fixtures/'.$name.'.json'),
        true
    );
}

/**
 * Create a mock EpointService with Guzzle MockHandler.
 *
 * Returns an object with ->service and ->history properties.
 * History is populated after requests are made.
 *
 * @param  array<Response>  $responses
 */
function createMockService(array $responses): stdClass
{
    $container = new stdClass;
    $container->history = [];
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($container->history));

    $client = new Client(['handler' => $handlerStack]);

    $container->service = EpointFactory::create([
        'public_key' => 'i000000001',
        'private_key' => 'd3hjsl38sd8kdfhbcea0be04eafde9e8e2bad2fb092d',
    ], $client);

    return $container;
}

function mockResponse(array|string $body = [], int $status = 200): Response
{
    $jsonBody = is_string($body) ? $body : json_encode($body);

    return new Response($status, ['Content-Type' => 'application/json'], $jsonBody);
}
