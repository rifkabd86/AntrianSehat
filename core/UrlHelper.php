<?php

class UrlHelper
{
    public static function to(string $path = ''): string
    {
        return rtrim(BASE_URL, '/') . ($path === '' ? '' : '/' . ltrim($path, '/'));
    }
}