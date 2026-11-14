<?php
/**
 * ANTRIAN SEHAT - Global Core Helpers
 * 
 * Kumpulan fungsi helper prosedural yang menjembatani kelas Helper,
 * SecurityHelper, UrlHelper, dan CsrfMiddleware di folder core/.
 */

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return UrlHelper::to($path);
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path = ''): string
    {
        return UrlHelper::to(ltrim($path, '/'));
    }
}

if (!function_exists('e')) {
    function e(?string $string): string
    {
        return Helper::e($string);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return SecurityHelper::csrfField();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return CsrfMiddleware::getToken();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Helper::old($key, $default);
    }
}
