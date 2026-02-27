<?php

namespace Aftandilmmd\EpointPayment\Services;

use Aftandilmmd\EpointPayment\Contracts\EpointServiceInterface;
use Aftandilmmd\EpointPayment\DTOs\Response\EpointResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\PaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\ReverseResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SavedCardPaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SplitPaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\WidgetResponse;
use Aftandilmmd\EpointPayment\Exceptions\EpointException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class EpointService implements EpointServiceInterface
{
    private Client $client;

    private ?LoggerInterface $logger;

    public function __construct(
        protected array $config,
        protected SignatureGenerator $signatureGenerator,
        ?Client $client = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->client = $client ?? new Client(['timeout' => $this->config['timeout'] ?? 30]);
        $this->logger = $logger;
    }

    // ── Payment ──

    public function createPayment(array $data): RedirectResponse
    {
        return RedirectResponse::fromArray(
            $this->sendRequest('/api/1/request', $data)
        );
    }

    // ── Pre-Auth Payment ──

    public function createPreAuthPayment(array $data): RedirectResponse
    {
        return RedirectResponse::fromArray(
            $this->sendRequest('/api/1/pre-auth-request', $data)
        );
    }

    public function completePreAuth(array $data): EpointResponse
    {
        return EpointResponse::fromArray(
            $this->sendRequest('/api/1/pre-auth-complete', $data)
        );
    }

    // ── Payment Status ──

    public function getPaymentStatus(array $data): PaymentResponse
    {
        return PaymentResponse::fromArray(
            $this->sendRequest('/api/1/get-status', $data)
        );
    }

    // ── Card Registration ──

    public function registerCard(array $data): RedirectResponse
    {
        return RedirectResponse::fromArray(
            $this->sendRequest('/api/1/card-registration', $data)
        );
    }

    // ── Saved Card Payment ──

    public function payWithSavedCard(array $data): SavedCardPaymentResponse
    {
        return SavedCardPaymentResponse::fromArray(
            $this->sendRequest('/api/1/execute-pay', $data)
        );
    }

    // ── Card Registration with Payment ──

    public function registerCardWithPay(array $data): RedirectResponse
    {
        return RedirectResponse::fromArray(
            $this->sendRequest('/api/1/card-registration-with-pay', $data)
        );
    }

    // ── Refund (Disbursement) ──

    public function refund(array $data): SavedCardPaymentResponse
    {
        return SavedCardPaymentResponse::fromArray(
            $this->sendRequest('/api/1/refund-request', $data)
        );
    }

    // ── Reverse (Cancel) ──

    public function reverse(array $data): ReverseResponse
    {
        return ReverseResponse::fromArray(
            $this->sendRequest('/api/1/reverse', $data)
        );
    }

    // ── Split Payment ──

    public function createSplitPayment(array $data): RedirectResponse
    {
        return RedirectResponse::fromArray(
            $this->sendRequest('/api/1/split-request', $data)
        );
    }

    public function splitPayWithSavedCard(array $data): SplitPaymentResponse
    {
        return SplitPaymentResponse::fromArray(
            $this->sendRequest('/api/1/split-execute-pay', $data)
        );
    }

    public function splitRegisterCardWithPay(array $data): RedirectResponse
    {
        return RedirectResponse::fromArray(
            $this->sendRequest('/api/1/split-card-registration-with-pay', $data)
        );
    }

    // ── Apple Pay & Google Pay Widget ──

    public function createWidget(array $data): WidgetResponse
    {
        return WidgetResponse::fromArray(
            $this->sendRequest('/api/1/token/widget', $data)
        );
    }

    // ── Callback Verification ──

    public function verifyCallback(string $data, string $signature): bool
    {
        return $this->signatureGenerator->verify($data, $signature);
    }

    public function decodeCallbackData(string $data): array
    {
        return json_decode(base64_decode($data), true) ?? [];
    }

    // ── Internal ──

    protected function baseUrl(): string
    {
        return $this->config['base_url'] ?? 'https://epoint.az';
    }

    /**
     * Encode data as base64 JSON and generate signature, then send POST request.
     *
     * @throws EpointException
     */
    protected function sendRequest(string $path, array $params): array
    {
        $params = array_merge(['public_key' => $this->config['public_key']], $params);

        $data = base64_encode(json_encode($params));
        $signature = $this->signatureGenerator->generate($data);

        $fullUrl = $this->baseUrl().$path;

        if ($this->logger && ($this->config['logging']['enabled'] ?? false)) {
            $this->logger->debug('Epoint API Request', ['url' => $fullUrl, 'params' => $params]);
        }

        $retryTimes = $this->config['retry']['times'] ?? 3;
        $retrySleep = $this->config['retry']['sleep'] ?? 100;
        $lastException = null;
        $response = null;

        for ($attempt = 0; $attempt <= $retryTimes; $attempt++) {
            if ($attempt > 0) {
                usleep($retrySleep * 1000);
            }

            try {
                $response = $this->client->request('POST', $fullUrl, [
                    'form_params' => [
                        'data' => $data,
                        'signature' => $signature,
                    ],
                ]);

                break;
            } catch (ConnectException $e) {
                $lastException = $e;

                continue;
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    $statusCode = $response->getStatusCode();

                    if ($statusCode >= 500) {
                        $lastException = $e;

                        continue;
                    }

                    break;
                }

                $lastException = $e;

                continue;
            }
        }

        if ($response === null) {
            throw new EpointException(
                'Epoint API connection failed after '.($retryTimes + 1).' attempts: '.($lastException?->getMessage() ?? 'Unknown error'),
                0,
            );
        }

        $body = json_decode($response->getBody()->getContents(), true) ?? [];

        if ($this->logger && ($this->config['logging']['enabled'] ?? false)) {
            $this->logger->debug('Epoint API Response', ['status' => $response->getStatusCode(), 'body' => $body]);
        }

        if ($response->getStatusCode() >= 400) {
            throw new EpointException(
                $body['message'] ?? 'Epoint API error',
                $response->getStatusCode(),
                $body['errors'] ?? null,
            );
        }

        return $body;
    }
}
