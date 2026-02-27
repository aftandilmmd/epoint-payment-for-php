<?php

namespace Aftandilmmd\EpointPayment\Enums;

enum Language: string
{
    case Az = 'az';
    case En = 'en';
    case Ru = 'ru';

    public function label(string $locale = 'az'): string
    {
        return match ($this) {
            self::Az => match ($locale) {
                'en' => 'Azerbaijani',
                'ru' => 'Азербайджанский',
                default => 'Azərbaycan dili',
            },
            self::En => match ($locale) {
                'en' => 'English',
                'ru' => 'Английский',
                default => 'İngilis dili',
            },
            self::Ru => match ($locale) {
                'en' => 'Russian',
                'ru' => 'Русский',
                default => 'Rus dili',
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
