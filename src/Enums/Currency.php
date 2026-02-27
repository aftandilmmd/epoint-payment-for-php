<?php

namespace Aftandilmmd\EpointPayment\Enums;

enum Currency: string
{
    case Azn = 'AZN';

    public function label(string $locale = 'az'): string
    {
        return match ($this) {
            self::Azn => match ($locale) {
                'en' => 'Azerbaijani Manat',
                'ru' => 'Азербайджанский манат',
                default => 'Azərbaycan manatı',
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
