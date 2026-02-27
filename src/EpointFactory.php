<?php

namespace Aftandilmmd\EpointPayment;

use Aftandilmmd\EpointPayment\Contracts\EpointServiceInterface;
use Aftandilmmd\EpointPayment\Services\EpointService;
use Aftandilmmd\EpointPayment\Services\SignatureGenerator;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class EpointFactory
{
    /**
     * Create a new EpointService instance.
     *
     * @param  array{
     *     public_key: string,
     *     private_key: string,
     *     base_url?: string,
     *     timeout?: int,
     *     retry?: array{times?: int, sleep?: int},
     *     logging?: array{enabled?: bool},
     * }  $config
     */
    public static function create(
        array $config,
        ?Client $client = null,
        ?LoggerInterface $logger = null,
    ): EpointServiceInterface {
        $config = array_merge([
            'base_url' => 'https://epoint.az',
            'timeout' => 30,
            'retry' => ['times' => 3, 'sleep' => 100],
            'logging' => ['enabled' => false],
        ], $config);

        $signatureGenerator = new SignatureGenerator(
            $config['private_key'],
        );

        return new EpointService($config, $signatureGenerator, $client, $logger);
    }
}
