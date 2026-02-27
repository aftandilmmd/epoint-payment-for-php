**English** | [Azərbaycanca](README.az.md)

# Epoint for PHP

Framework-agnostic [Epoint.az](https://epoint.az) payment gateway integration for PHP. Supports standard payments, pre-auth, split payments, card registration, saved card payments, refunds, reversals, Apple Pay / Google Pay widgets, and callback verification.

Works with any PHP 8.2+ application -- no Laravel or other framework required.

## Requirements

- PHP 8.2+
- Guzzle HTTP 7.0+

## Installation

```bash
composer require aftandilmmd/epoint-payment-for-php
```

## Quick Start

```php
use Aftandilmmd\EpointPayment\EpointFactory;

$epoint = EpointFactory::create([
    'public_key' => 'your-public-key',   // Merchant ID
    'private_key' => 'your-private-key', // Secret key
]);

$response = $epoint->createPayment([
    'amount' => 10.50,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'order-001',
    'description' => 'Test payment',
]);

// Redirect user to payment page
header('Location: ' . $response->redirectUrl);
```

## Configuration

```php
$epoint = EpointFactory::create([
    'public_key' => 'your-public-key',   // Required - Merchant ID
    'private_key' => 'your-private-key', // Required - Secret key
    'base_url' => 'https://epoint.az',   // Default: https://epoint.az
    'timeout' => 30,                      // Default: 30 seconds
    'retry' => [
        'times' => 3,                    // Default: 3
        'sleep' => 100,                  // Default: 100ms
    ],
    'logging' => [
        'enabled' => false,              // Default: false
    ],
]);
```

### Custom Guzzle Client

You can pass your own Guzzle client instance:

```php
use GuzzleHttp\Client;

$client = new Client(['timeout' => 60, 'proxy' => 'http://proxy:8080']);
$epoint = EpointFactory::create($config, $client);
```

### PSR-3 Logger

Pass any PSR-3 compatible logger (Monolog, etc.) and enable logging:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('epoint');
$logger->pushHandler(new StreamHandler('path/to/epoint.log'));

$epoint = EpointFactory::create([
    'public_key' => 'your-public-key',
    'private_key' => 'your-private-key',
    'logging' => ['enabled' => true],
], logger: $logger);
```

---

## How It Works

Epoint uses a signature-based authentication model. Every request sends two POST form parameters:

| Parameter   | Description |
|-------------|-------------|
| `data`      | Base64-encoded JSON of your request parameters |
| `signature` | `base64_encode(sha1(private_key + data + private_key, true))` |

This package handles all encoding, signing, and decoding automatically. You only work with plain PHP arrays and typed response DTOs.

---

## API Reference

### Create Payment

Initialize a payment and redirect the user to Epoint's hosted payment page.

```php
$response = $epoint->createPayment([
    'amount' => 10.50,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'order-001',
    'description' => 'Monthly subscription',
]);

$response->status;      // "success"
$response->transaction; // "te0000000001"
$response->redirectUrl; // "https://epoint.az/pay/te0000000001"

// Redirect user to $response->redirectUrl
```

**Endpoint:** `POST /api/1/request`

---

### Get Payment Status

Retrieve the current status of a payment by transaction ID.

```php
$response = $epoint->getPaymentStatus([
    'transaction' => 'te0000000001',
]);

$response->status;          // "success"
$response->orderId;         // "order-001"
$response->transaction;     // "te0000000001"
$response->bankTransaction; // "EPGO00000001"
$response->bankResponse;    // "APPROVED"
$response->rrn;             // "412345678901"
$response->cardName;        // "VISA"
$response->cardMask;        // "422865****8101"
$response->amount;          // 10.50
$response->code;            // "100"
$response->operationCode;   // "100"
```

**Endpoint:** `POST /api/1/get-status`

---

### Pre-Auth Payment

Create a pre-authorized payment (hold funds) and complete it later.

#### Step 1: Create Pre-Auth

```php
$response = $epoint->createPreAuthPayment([
    'amount' => 50.00,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'order-preauth-1',
    'description' => 'Hotel reservation',
]);

$response->status;      // "success"
$response->transaction; // "te0000000101"
$response->redirectUrl; // "https://epoint.az/pay/te0000000101"

// Redirect user to $response->redirectUrl
```

**Endpoint:** `POST /api/1/pre-auth-request`

#### Step 2: Complete Pre-Auth

```php
$response = $epoint->completePreAuth([
    'amount' => 50.00,
    'transaction' => 'te0000000101',
]);

$response->status;        // "success"
$response->isSuccessful(); // true
```

**Endpoint:** `POST /api/1/pre-auth-complete`

---

### Card Registration

Register a card for future payments (tokenization).

```php
$response = $epoint->registerCard([
    'language' => 'az',
]);

$response->status;      // "success"
$response->redirectUrl; // "https://epoint.az/pay/..."
$response->cardId;      // "cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"

// Redirect user to registration page
```

**Endpoint:** `POST /api/1/card-registration`

---

### Pay with Saved Card

Charge a previously registered card without user redirection.

```php
$response = $epoint->payWithSavedCard([
    'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'amount' => 25.00,
    'currency' => 'AZN',
    'order_id' => 'order-saved-1',
    'description' => 'Recurring payment',
]);

$response->status;          // "success"
$response->transaction;     // "te0000000201"
$response->bankTransaction; // "EPGO00000002"
$response->amount;          // 25.0
$response->cardName;        // "VISA"
$response->cardMask;        // "422865****8101"
```

**Endpoint:** `POST /api/1/execute-pay`

---

### Card Registration with Payment

Register a card and charge it in a single step. Useful for first-time payments where you want to save the card for future use.

```php
$response = $epoint->registerCardWithPay([
    'amount' => 15.00,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'order-regpay-1',
]);

$response->status;      // "success"
$response->transaction; // "te0000000301"
$response->redirectUrl; // "https://epoint.az/pay/te0000000301"
$response->cardId;      // "cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"

// Redirect user -- after payment, you'll have the card_id for future charges
```

**Endpoint:** `POST /api/1/card-registration-with-pay`

---

### Refund (Disbursement)

Refund a payment to the original card.

```php
$response = $epoint->refund([
    'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'amount' => 10.50,
    'currency' => 'AZN',
    'order_id' => 'refund-001',
    'description' => 'Order refund',
]);

$response->status;          // "success"
$response->transaction;     // "te0000000401"
$response->bankTransaction; // "EPGO00000004"
$response->amount;          // 10.5
```

**Endpoint:** `POST /api/1/refund-request`

---

### Reverse (Cancel)

Cancel a pending transaction.

```php
$response = $epoint->reverse([
    'transaction' => 'te0000000001',
    'amount' => 10.50,
]);

$response->status;        // "success"
$response->isSuccessful(); // true
```

**Endpoint:** `POST /api/1/reverse`

---

### Split Payment

Split a payment between the main merchant and a sub-merchant.

#### Create Split Payment

```php
$response = $epoint->createSplitPayment([
    'amount' => 100.00,
    'split_user' => 'i000000002',    // Sub-merchant public_key
    'split_amount' => 30.00,          // Amount for sub-merchant
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'order-split-1',
]);

$response->status;      // "success"
$response->transaction; // "tw0000000501"
$response->redirectUrl; // "https://epoint.az/pay/tw0000000501"
```

**Endpoint:** `POST /api/1/split-request`

#### Split Payment with Saved Card

```php
$response = $epoint->splitPayWithSavedCard([
    'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'order_id' => 'order-split-saved-1',
    'amount' => 100.00,
    'split_user' => 'i000000002',
    'split_amount' => 30.00,
    'currency' => 'AZN',
]);

$response->status;      // "success"
$response->amount;      // 100.0
$response->splitAmount; // 30.0
```

**Endpoint:** `POST /api/1/split-execute-pay`

#### Split Card Registration with Payment

```php
$response = $epoint->splitRegisterCardWithPay([
    'order_id' => 'order-split-reg-1',
    'amount' => 100.00,
    'split_user' => 'i000000002',
    'split_amount' => 30.00,
    'currency' => 'AZN',
    'language' => 'az',
]);

$response->status;      // "success"
$response->redirectUrl; // "https://epoint.az/pay/..."
$response->cardId;      // "cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
```

**Endpoint:** `POST /api/1/split-card-registration-with-pay`

---

### Apple Pay & Google Pay Widget

Create a widget URL for Apple Pay / Google Pay payments.

```php
$response = $epoint->createWidget([
    'amount' => 2.50,
    'order_id' => 'order-widget-1',
    'description' => 'Widget payment',
]);

$response->status;    // "success"
$response->widgetUrl; // "https://epoint.az/api/1/token/widget/000001"

// Embed or redirect to $response->widgetUrl
```

**Endpoint:** `POST /api/1/token/widget`

---

### Callback Verification

When Epoint sends a callback to your application after a payment, you need to verify the signature to ensure the callback is authentic.

#### Verify Callback Signature

```php
// In your callback handler (e.g., POST /payment/callback)
$data = $_POST['data'];           // Base64-encoded data from Epoint
$signature = $_POST['signature']; // Signature from Epoint

if ($epoint->verifyCallback($data, $signature)) {
    // Signature is valid -- safe to process
    $decoded = $epoint->decodeCallbackData($data);

    $decoded['status'];     // "success"
    $decoded['order_id'];   // "order-001"
    $decoded['amount'];     // 10.50
    $decoded['transaction']; // "te0000000001"
} else {
    // Invalid signature -- reject the callback
    http_response_code(403);
}
```

#### Decode Callback Data

```php
$decoded = $epoint->decodeCallbackData($data);

// Returns the decoded array from the base64 JSON data
// e.g., ['status' => 'success', 'order_id' => '123', 'amount' => 30.75]
```

---

## Response DTOs

All API methods return typed readonly DTOs. Every DTO extends `EpointResponse` and includes:

```php
$response->status;        // "success", "error", "failed", etc.
$response->message;       // Human-readable message
$response->rawData;       // Full raw API response array
$response->isSuccessful(); // true if status === "success"
$response->isFailed();     // true if status === "failed" or "error"
```

### Available DTOs

| DTO | Used By | Key Properties |
|-----|---------|----------------|
| `RedirectResponse` | `createPayment`, `createPreAuthPayment`, `registerCard`, `registerCardWithPay`, `createSplitPayment`, `splitRegisterCardWithPay` | `transaction`, `redirectUrl`, `cardId` |
| `PaymentResponse` | `getPaymentStatus` | `orderId`, `code`, `transaction`, `bankTransaction`, `bankResponse`, `operationCode`, `rrn`, `cardName`, `cardMask`, `amount` |
| `SavedCardPaymentResponse` | `payWithSavedCard`, `refund` | `transaction`, `bankTransaction`, `bankResponse`, `rrn`, `cardName`, `cardMask`, `amount` |
| `SplitPaymentResponse` | `splitPayWithSavedCard` | All of `PaymentResponse` + `splitAmount` |
| `ReverseResponse` | `reverse` | `status`, `message` |
| `WidgetResponse` | `createWidget` | `widgetUrl` |
| `EpointResponse` | `completePreAuth` | `status`, `message` |
| `CardRegistrationResponse` | Callback processing | `code`, `cardId`, `bankTransaction`, `bankResponse`, `operationCode`, `rrn`, `cardName`, `cardMask` |
| `CardRegistrationWithPayResponse` | Callback processing | All of `CardRegistrationResponse` + `orderId`, `transaction`, `amount` |

---

## Error Handling

All API errors throw typed exceptions:

```php
use Aftandilmmd\EpointPayment\Exceptions\EpointException;
use Aftandilmmd\EpointPayment\Exceptions\EpointAuthenticationException;

try {
    $response = $epoint->createPayment([...]);
} catch (EpointException $e) {
    // API error or connection failure
    // $e->getMessage() => "Epoint API connection failed after 4 attempts: Connection refused"
    // $e->getMessage() => "Bad Request"
    // $e->getCode()    => 400
    // $e->errors       => ['field' => 'amount', ...] (if available)
}
```

### Retry Behavior

The package automatically retries on:
- **Connection failures** (`ConnectException`) -- up to `retry.times` attempts
- **5xx server errors** -- up to `retry.times` attempts

4xx client errors are **not** retried and throw immediately.

---

## Enums

The package provides typed enums for all API constants with multilanguage support (`az`, `en`, `ru`):

```php
use Aftandilmmd\EpointPayment\Enums\Currency;
use Aftandilmmd\EpointPayment\Enums\Language;
use Aftandilmmd\EpointPayment\Enums\PaymentStatus;
use Aftandilmmd\EpointPayment\Enums\OperationCode;

// Values
Currency::Azn->value;                        // "AZN"
Language::Az->value;                         // "az"
PaymentStatus::Success->value;               // "success"
OperationCode::CardRegistration->value;      // "001"

// Labels (default: Azerbaijani)
Currency::Azn->label();                      // "Azərbaycan manatı"
Language::Az->label();                       // "Azərbaycan dili"
PaymentStatus::Success->label();             // "Uğurlu ödəniş"
OperationCode::UserPayment->label();         // "İstifadəçi ödənişi"

// Labels in English
Currency::Azn->label('en');                  // "Azerbaijani Manat"
PaymentStatus::Success->label('en');         // "Successful payment"
OperationCode::UserPayment->label('en');     // "User payment"

// Labels in Russian
Currency::Azn->label('ru');                  // "Азербайджанский манат"
PaymentStatus::Success->label('ru');         // "Успешный платёж"
OperationCode::UserPayment->label('ru');     // "Платёж пользователя"

// Select options (value => label) -- pass locale to get translated options
Currency::options();       // ["AZN" => "Azərbaycan manatı"]
Currency::options('en');   // ["AZN" => "Azerbaijani Manat"]
Currency::options('ru');   // ["AZN" => "Азербайджанский манат"]
Language::options();       // ["az" => "Azərbaycan dili", "en" => "İngilis dili", "ru" => "Rus dili"]
PaymentStatus::options();  // ["new" => "Yeni ödəniş", "success" => "Uğurlu ödəniş", ...]
```

Available enums: `Currency`, `Language`, `PaymentStatus`, `OperationCode`.

---

## Testing

```bash
vendor/bin/pest
```

56 tests, 165 assertions. Uses Guzzle `MockHandler` with JSON fixtures.

## License

MIT
