<?php

use Aftandilmmd\EpointPayment\Enums\Currency;
use Aftandilmmd\EpointPayment\Enums\Language;
use Aftandilmmd\EpointPayment\Enums\OperationCode;
use Aftandilmmd\EpointPayment\Enums\PaymentStatus;

it('has correct currency values', function () {
    expect(Currency::Azn->value)->toBe('AZN');
});

it('returns currency labels', function () {
    expect(Currency::Azn->label())->toBe('Azərbaycan manatı')
        ->and(Currency::Azn->label('en'))->toBe('Azerbaijani Manat')
        ->and(Currency::Azn->label('ru'))->toBe('Азербайджанский манат');
});

it('returns currency options', function () {
    $options = Currency::options();
    expect($options)->toBeArray()
        ->toHaveKey('AZN');
});

it('has correct language values', function () {
    expect(Language::Az->value)->toBe('az')
        ->and(Language::En->value)->toBe('en')
        ->and(Language::Ru->value)->toBe('ru');
});

it('returns language labels', function () {
    expect(Language::Az->label())->toBe('Azərbaycan dili')
        ->and(Language::En->label())->toBe('İngilis dili')
        ->and(Language::Ru->label())->toBe('Rus dili')
        ->and(Language::Az->label('en'))->toBe('Azerbaijani')
        ->and(Language::En->label('en'))->toBe('English')
        ->and(Language::Ru->label('en'))->toBe('Russian')
        ->and(Language::Az->label('ru'))->toBe('Азербайджанский')
        ->and(Language::En->label('ru'))->toBe('Английский')
        ->and(Language::Ru->label('ru'))->toBe('Русский');
});

it('returns language options', function () {
    $options = Language::options();
    expect($options)->toHaveCount(3)
        ->toHaveKeys(['az', 'en', 'ru']);
});

it('has correct payment status values', function () {
    expect(PaymentStatus::New->value)->toBe('new')
        ->and(PaymentStatus::Success->value)->toBe('success')
        ->and(PaymentStatus::Returned->value)->toBe('returned')
        ->and(PaymentStatus::Error->value)->toBe('error')
        ->and(PaymentStatus::ServerError->value)->toBe('server_error');
});

it('returns payment status options', function () {
    $options = PaymentStatus::options();
    expect($options)->toHaveCount(5);
});

it('has correct operation code values', function () {
    expect(OperationCode::CardRegistration->value)->toBe('001')
        ->and(OperationCode::UserPayment->value)->toBe('100')
        ->and(OperationCode::CardRegistrationWithPayment->value)->toBe('200');
});

it('returns operation code options', function () {
    $options = OperationCode::options();
    expect($options)->toHaveCount(3);
});
