<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Administrator',
            self::Manager => 'Manager',
            self::Staff => 'Staff',
        };
    }

    /** Rank used for "at least this role" checks; higher wins. */
    public function level(): int
    {
        return match ($this) {
            self::Owner => 40,
            self::Admin => 30,
            self::Manager => 20,
            self::Staff => 10,
        };
    }

    public function atLeast(self $other): bool
    {
        return $this->level() >= $other->level();
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
