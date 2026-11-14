<?php

class SecurityHelper
{
    public static function escape(string|null $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrfField(): string
    {
        return CsrfMiddleware::getTokenField();
    }
}