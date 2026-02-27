<?php

namespace Aftandilmmd\EpointPayment\Contracts;

use Aftandilmmd\EpointPayment\DTOs\Response\CardRegistrationResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\CardRegistrationWithPayResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\EpointResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\PaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\RedirectResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\ReverseResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SavedCardPaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\SplitPaymentResponse;
use Aftandilmmd\EpointPayment\DTOs\Response\WidgetResponse;

interface EpointServiceInterface
{
    // Payment
    public function createPayment(array $data): RedirectResponse;

    // Pre-Auth Payment
    public function createPreAuthPayment(array $data): RedirectResponse;

    public function completePreAuth(array $data): EpointResponse;

    // Payment Status
    public function getPaymentStatus(array $data): PaymentResponse;

    // Card Registration
    public function registerCard(array $data): RedirectResponse;

    // Saved Card Payment
    public function payWithSavedCard(array $data): SavedCardPaymentResponse;

    // Card Registration with Payment
    public function registerCardWithPay(array $data): RedirectResponse;

    // Refund (disbursement)
    public function refund(array $data): SavedCardPaymentResponse;

    // Reverse (cancel)
    public function reverse(array $data): ReverseResponse;

    // Split Payment
    public function createSplitPayment(array $data): RedirectResponse;

    // Split Payment with Saved Card
    public function splitPayWithSavedCard(array $data): SplitPaymentResponse;

    // Split Card Registration with Payment
    public function splitRegisterCardWithPay(array $data): RedirectResponse;

    // Apple Pay & Google Pay Widget
    public function createWidget(array $data): WidgetResponse;

    // Callback Signature Verification
    public function verifyCallback(string $data, string $signature): bool;

    // Decode callback data
    public function decodeCallbackData(string $data): array;
}
