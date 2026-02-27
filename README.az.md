[English](README.md) | **Azərbaycanca**

# PHP üçün Epoint

PHP üçün framework-dən asılı olmayan [Epoint.az](https://epoint.az) ödəniş inteqrasiyası. Standart ödənişlər, ön icazələndirmə, bölünmüş ödənişlər, kart qeydiyyatı, saxlanmış kartla ödəniş, geri ödəmə, ləğvetmə, Apple Pay / Google Pay widget-ləri və callback doğrulamanı dəstəkləyir.

PHP 8.2+ ilə işləyən bütün tətbiqlərdə istifadə edilə bilər -- Laravel və ya başqa bir framework tələb etmir.

## Tələblər

- PHP 8.2+
- Guzzle HTTP 7.0+

## Quraşdırma

```bash
composer require aftandilmmd/epoint-payment-for-php
```

## Sürətli Başlanğıc

```php
use Aftandilmmd\EpointPayment\EpointFactory;

$epoint = EpointFactory::create([
    'public_key' => 'açıq-açarınız',    // Merchant ID
    'private_key' => 'gizli-açarınız',  // Gizli açar
]);

$response = $epoint->createPayment([
    'amount' => 10.50,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'sifariş-001',
    'description' => 'Test ödənişi',
]);

// İstifadəçini ödəniş səhifəsinə yönləndirin
header('Location: ' . $response->redirectUrl);
```

## Konfiqurasiya

```php
$epoint = EpointFactory::create([
    'public_key' => 'açıq-açarınız',    // Məcburi - Merchant ID
    'private_key' => 'gizli-açarınız',  // Məcburi - Gizli açar
    'base_url' => 'https://epoint.az',   // Defolt: https://epoint.az
    'timeout' => 30,                      // Defolt: 30 saniyə
    'retry' => [
        'times' => 3,                    // Defolt: 3
        'sleep' => 100,                  // Defolt: 100ms
    ],
    'logging' => [
        'enabled' => false,              // Defolt: false
    ],
]);
```

### Xüsusi Guzzle Klienti

Öz Guzzle klient nümunənizi ötürə bilərsiniz:

```php
use GuzzleHttp\Client;

$client = new Client(['timeout' => 60, 'proxy' => 'http://proxy:8080']);
$epoint = EpointFactory::create($config, $client);
```

### PSR-3 Logger

İstənilən PSR-3 uyğun logger (Monolog və s.) ötürün və log yazmağı aktivləşdirin:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('epoint');
$logger->pushHandler(new StreamHandler('path/to/epoint.log'));

$epoint = EpointFactory::create([
    'public_key' => 'açıq-açarınız',
    'private_key' => 'gizli-açarınız',
    'logging' => ['enabled' => true],
], logger: $logger);
```

---

## Necə İşləyir

Epoint imza əsaslı autentifikasiya modeli istifadə edir. Hər sorğu iki POST form parametri göndərir:

| Parametr    | Təsvir |
|-------------|--------|
| `data`      | Sorğu parametrlərinin Base64 kodlanmış JSON-u |
| `signature` | `base64_encode(sha1(private_key + data + private_key, true))` |

Bu paket bütün kodlama, imzalama və dekodlamanı avtomatik idarə edir. Siz yalnız sadə PHP massivləri və tipli cavab DTO-ları ilə işləyirsiniz.

---

## API Arayışı

### Ödəniş Yaratma

Ödənişi başladın və istifadəçini Epoint-in ödəniş səhifəsinə yönləndirin.

```php
$response = $epoint->createPayment([
    'amount' => 10.50,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'sifariş-001',
    'description' => 'Aylıq abunəlik',
]);

$response->status;      // "success"
$response->transaction; // "te0000000001"
$response->redirectUrl; // "https://epoint.az/pay/te0000000001"

// İstifadəçini $response->redirectUrl ünvanına yönləndirin
```

**Endpoint:** `POST /api/1/request`

---

### Ödəniş Statusunu Sorğulama

Əməliyyat ID-si ilə ödənişin cari statusunu alın.

```php
$response = $epoint->getPaymentStatus([
    'transaction' => 'te0000000001',
]);

$response->status;          // "success"
$response->orderId;         // "sifariş-001"
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

### Ön İcazələndirilmiş Ödəniş

Ön icazələndirilmiş ödəniş yaradın (vəsaitləri saxlayın) və sonra tamamlayın.

#### Addım 1: Ön İcazə Yaratma

```php
$response = $epoint->createPreAuthPayment([
    'amount' => 50.00,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'sifariş-preauth-1',
    'description' => 'Otel rezervasiyası',
]);

$response->status;      // "success"
$response->transaction; // "te0000000101"
$response->redirectUrl; // "https://epoint.az/pay/te0000000101"

// İstifadəçini $response->redirectUrl ünvanına yönləndirin
```

**Endpoint:** `POST /api/1/pre-auth-request`

#### Addım 2: Ön İcazəni Tamamlama

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

### Kart Qeydiyyatı

Gələcək ödənişlər üçün kartı qeydiyyatdan keçirin (tokenizasiya).

```php
$response = $epoint->registerCard([
    'language' => 'az',
]);

$response->status;      // "success"
$response->redirectUrl; // "https://epoint.az/pay/..."
$response->cardId;      // "cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"

// İstifadəçini qeydiyyat səhifəsinə yönləndirin
```

**Endpoint:** `POST /api/1/card-registration`

---

### Saxlanmış Kartla Ödəniş

Əvvəlcədən qeydiyyatdan keçmiş kartla istifadəçi yönləndirməsi olmadan ödəniş alın.

```php
$response = $epoint->payWithSavedCard([
    'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'amount' => 25.00,
    'currency' => 'AZN',
    'order_id' => 'sifariş-saved-1',
    'description' => 'Təkrarlanan ödəniş',
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

### Ödənişlə Kart Qeydiyyatı

Kartı qeydiyyatdan keçirin və eyni zamanda ödəniş alın. Kartı gələcək istifadə üçün saxlamaq istədiyiniz ilk ödənişlər üçün faydalıdır.

```php
$response = $epoint->registerCardWithPay([
    'amount' => 15.00,
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'sifariş-regpay-1',
]);

$response->status;      // "success"
$response->transaction; // "te0000000301"
$response->redirectUrl; // "https://epoint.az/pay/te0000000301"
$response->cardId;      // "cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"

// İstifadəçini yönləndirin -- ödənişdən sonra gələcək ödənişlər üçün card_id əldə edəcəksiniz
```

**Endpoint:** `POST /api/1/card-registration-with-pay`

---

### Geri Ödəmə

Ödənişi orijinal karta geri qaytarın.

```php
$response = $epoint->refund([
    'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'amount' => 10.50,
    'currency' => 'AZN',
    'order_id' => 'geri-odeme-001',
    'description' => 'Sifariş geri ödəməsi',
]);

$response->status;          // "success"
$response->transaction;     // "te0000000401"
$response->bankTransaction; // "EPGO00000004"
$response->amount;          // 10.5
```

**Endpoint:** `POST /api/1/refund-request`

---

### Ləğvetmə

Gözləyən əməliyyatı ləğv edin.

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

### Bölünmüş Ödəniş

Ödənişi əsas merchant və sub-merchant arasında bölün.

#### Bölünmüş Ödəniş Yaratma

```php
$response = $epoint->createSplitPayment([
    'amount' => 100.00,
    'split_user' => 'i000000002',    // Sub-merchant açıq açarı
    'split_amount' => 30.00,          // Sub-merchant üçün məbləğ
    'currency' => 'AZN',
    'language' => 'az',
    'order_id' => 'sifariş-split-1',
]);

$response->status;      // "success"
$response->transaction; // "tw0000000501"
$response->redirectUrl; // "https://epoint.az/pay/tw0000000501"
```

**Endpoint:** `POST /api/1/split-request`

#### Saxlanmış Kartla Bölünmüş Ödəniş

```php
$response = $epoint->splitPayWithSavedCard([
    'card_id' => 'cexxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'order_id' => 'sifariş-split-saved-1',
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

#### Ödənişlə Bölünmüş Kart Qeydiyyatı

```php
$response = $epoint->splitRegisterCardWithPay([
    'order_id' => 'sifariş-split-reg-1',
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

### Apple Pay və Google Pay Widget

Apple Pay / Google Pay ödənişləri üçün widget URL-i yaradın.

```php
$response = $epoint->createWidget([
    'amount' => 2.50,
    'order_id' => 'sifariş-widget-1',
    'description' => 'Widget ödənişi',
]);

$response->status;    // "success"
$response->widgetUrl; // "https://epoint.az/api/1/token/widget/000001"

// $response->widgetUrl ünvanına daxil edin və ya yönləndirin
```

**Endpoint:** `POST /api/1/token/widget`

---

### Callback Doğrulama

Epoint ödənişdən sonra tətbiqinizə callback göndərdikdə, callback-ın həqiqi olduğunu təmin etmək üçün imzanı doğrulamalısınız.

#### Callback İmzasını Doğrulama

```php
// Callback işləyicinizdə (məs., POST /payment/callback)
$data = $_POST['data'];           // Epoint-dən gələn Base64 kodlanmış məlumat
$signature = $_POST['signature']; // Epoint-dən gələn imza

if ($epoint->verifyCallback($data, $signature)) {
    // İmza doğrudur -- emal etmək təhlükəsizdir
    $decoded = $epoint->decodeCallbackData($data);

    $decoded['status'];      // "success"
    $decoded['order_id'];    // "sifariş-001"
    $decoded['amount'];      // 10.50
    $decoded['transaction']; // "te0000000001"
} else {
    // Yanlış imza -- callback-ı rədd edin
    http_response_code(403);
}
```

#### Callback Məlumatını Dekodlama

```php
$decoded = $epoint->decodeCallbackData($data);

// Base64 JSON məlumatından dekod edilmiş massivi qaytarır
// məs., ['status' => 'success', 'order_id' => '123', 'amount' => 30.75]
```

---

## Cavab DTO-ları

Bütün API metodları tipli readonly DTO-lar qaytarır. Hər DTO `EpointResponse`-u genişləndirir və daxildir:

```php
$response->status;        // "success", "error", "failed" və s.
$response->message;       // İnsan oxuya bilən mesaj
$response->rawData;       // Tam xam API cavab massivi
$response->isSuccessful(); // Status "success" olarsa true
$response->isFailed();     // Status "failed" və ya "error" olarsa true
```

### Mövcud DTO-lar

| DTO | İstifadə Edən Metodlar | Əsas Xüsusiyyətlər |
|-----|------------------------|---------------------|
| `RedirectResponse` | `createPayment`, `createPreAuthPayment`, `registerCard`, `registerCardWithPay`, `createSplitPayment`, `splitRegisterCardWithPay` | `transaction`, `redirectUrl`, `cardId` |
| `PaymentResponse` | `getPaymentStatus` | `orderId`, `code`, `transaction`, `bankTransaction`, `bankResponse`, `operationCode`, `rrn`, `cardName`, `cardMask`, `amount` |
| `SavedCardPaymentResponse` | `payWithSavedCard`, `refund` | `transaction`, `bankTransaction`, `bankResponse`, `rrn`, `cardName`, `cardMask`, `amount` |
| `SplitPaymentResponse` | `splitPayWithSavedCard` | `PaymentResponse` xüsusiyyətləri + `splitAmount` |
| `ReverseResponse` | `reverse` | `status`, `message` |
| `WidgetResponse` | `createWidget` | `widgetUrl` |
| `EpointResponse` | `completePreAuth` | `status`, `message` |
| `CardRegistrationResponse` | Callback emalı | `code`, `cardId`, `bankTransaction`, `bankResponse`, `operationCode`, `rrn`, `cardName`, `cardMask` |
| `CardRegistrationWithPayResponse` | Callback emalı | `CardRegistrationResponse` xüsusiyyətləri + `orderId`, `transaction`, `amount` |

---

## Xəta İdarəetməsi

Bütün API xətaları tipli istisna atır:

```php
use Aftandilmmd\EpointPayment\Exceptions\EpointException;
use Aftandilmmd\EpointPayment\Exceptions\EpointAuthenticationException;

try {
    $response = $epoint->createPayment([...]);
} catch (EpointException $e) {
    // API xətası və ya bağlantı uğursuzluğu
    // $e->getMessage() => "Epoint API connection failed after 4 attempts: Connection refused"
    // $e->getMessage() => "Bad Request"
    // $e->getCode()    => 400
    // $e->errors       => ['field' => 'amount', ...] (mövcuddursa)
}
```

### Yenidən Cəhd Davranışı

Paket avtomatik olaraq aşağıdakı hallarda yenidən cəhd edir:
- **Bağlantı uğursuzluqları** (`ConnectException`) -- `retry.times` qədər cəhd
- **5xx server xətaları** -- `retry.times` qədər cəhd

4xx müştəri xətaları **yenidən cəhd edilmir** və dərhal istisna atılır.

---

## Enum-lar

Paket bütün API sabitləri üçün çoxdilli (`az`, `en`, `ru`) tipli enum-lar təmin edir:

```php
use Aftandilmmd\EpointPayment\Enums\Currency;
use Aftandilmmd\EpointPayment\Enums\Language;
use Aftandilmmd\EpointPayment\Enums\PaymentStatus;
use Aftandilmmd\EpointPayment\Enums\OperationCode;

// Dəyərlər
Currency::Azn->value;                        // "AZN"
Language::Az->value;                         // "az"
PaymentStatus::Success->value;               // "success"
OperationCode::CardRegistration->value;      // "001"

// Etiketlər (defolt: Azərbaycan dili)
Currency::Azn->label();                      // "Azərbaycan manatı"
Language::Az->label();                       // "Azərbaycan dili"
PaymentStatus::Success->label();             // "Uğurlu ödəniş"
OperationCode::UserPayment->label();         // "İstifadəçi ödənişi"

// İngilis dilində etiketlər
Currency::Azn->label('en');                  // "Azerbaijani Manat"
PaymentStatus::Success->label('en');         // "Successful payment"
OperationCode::UserPayment->label('en');     // "User payment"

// Rus dilində etiketlər
Currency::Azn->label('ru');                  // "Азербайджанский манат"
PaymentStatus::Success->label('ru');         // "Успешный платёж"
OperationCode::UserPayment->label('ru');     // "Платёж пользователя"

// Seçim variantları (dəyər => etiket) -- tərcümə olunmuş seçimlər üçün dil ötürün
Currency::options();       // ["AZN" => "Azərbaycan manatı"]
Currency::options('en');   // ["AZN" => "Azerbaijani Manat"]
Currency::options('ru');   // ["AZN" => "Азербайджанский манат"]
Language::options();       // ["az" => "Azərbaycan dili", "en" => "İngilis dili", "ru" => "Rus dili"]
PaymentStatus::options();  // ["new" => "Yeni ödəniş", "success" => "Uğurlu ödəniş", ...]
```

Mövcud enum-lar: `Currency`, `Language`, `PaymentStatus`, `OperationCode`.

---

## Test

```bash
vendor/bin/pest
```

56 test, 165 doğrulama. JSON fixture-lar ilə Guzzle `MockHandler` istifadə edir.

## Lisenziya

MIT
