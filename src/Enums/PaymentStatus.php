<?php

namespace Aftandilmmd\EpointPayment\Enums;

enum PaymentStatus: string
{
    case New = 'new';
    case Success = 'success';
    case Returned = 'returned';
    case Error = 'error';
    case ServerError = 'server_error';

    public function label(string $locale = 'az'): string
    {
        return match ($this) {
            self::New => match ($locale) {
                'en' => 'New payment',
                'ru' => 'Новый платёж',
                default => 'Yeni ödəniş',
            },
            self::Success => match ($locale) {
                'en' => 'Successful payment',
                'ru' => 'Успешный платёж',
                default => 'Uğurlu ödəniş',
            },
            self::Returned => match ($locale) {
                'en' => 'Returned payment',
                'ru' => 'Возвращённый платёж',
                default => 'Geri qaytarılmış ödəniş',
            },
            self::Error => match ($locale) {
                'en' => 'Error occurred',
                'ru' => 'Произошла ошибка',
                default => 'Xəta baş verdi',
            },
            self::ServerError => match ($locale) {
                'en' => 'Server error',
                'ru' => 'Ошибка сервера',
                default => 'Server xətası',
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
