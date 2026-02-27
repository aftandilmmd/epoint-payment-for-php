<?php

namespace Aftandilmmd\EpointPayment\Enums;

enum OperationCode: string
{
    case CardRegistration = '001';
    case UserPayment = '100';
    case CardRegistrationWithPayment = '200';

    public function label(string $locale = 'az'): string
    {
        return match ($this) {
            self::CardRegistration => match ($locale) {
                'en' => 'Card registration',
                'ru' => 'Регистрация карты',
                default => 'Kart qeydiyyatı',
            },
            self::UserPayment => match ($locale) {
                'en' => 'User payment',
                'ru' => 'Платёж пользователя',
                default => 'İstifadəçi ödənişi',
            },
            self::CardRegistrationWithPayment => match ($locale) {
                'en' => 'Card registration with first payment',
                'ru' => 'Регистрация карты с первым платежом',
                default => 'İlk ödəniş ilə kart qeydiyyatı',
            },
        };
    }

    public static function options(string $locale = 'az'): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label($locale);
        }

        return $options;
    }
}
