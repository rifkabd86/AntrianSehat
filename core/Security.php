<?php
/**
 * ANTRIAN SEHAT - Core Security Class
 * 
 * Menyediakan fungsi keamanan terpusat (proteksi XSS, CSRF token, dan sanitasi input)
 * yang terintegrasi dengan SecurityHelper dan CsrfMiddleware.
 */

class Security
{
    /**
     * Escape string untuk mencegah serangan Cross-Site Scripting (XSS)
     */
    public static function escape(?string $value): string
    {
        return SecurityHelper::escape($value);
    }

    /**
     * Menghasilkan HTML hidden input field untuk CSRF token
     */
    public static function csrfField(): string
    {
        return CsrfMiddleware::getTokenField();
    }

    /**
     * Mengambil token CSRF aktif dari session
     */
    public static function csrfToken(): string
    {
        return CsrfMiddleware::getToken();
    }

    /**
     * Memverifikasi kecocokan token CSRF
     */
    public static function verifyCsrf(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        return CsrfMiddleware::verifyToken($token);
    }

    /**
     * Sanitasi array atau string input
     */
    public static function clean(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([self::class, 'clean'], $input);
        }
        if (is_string($input)) {
            return trim(strip_tags($input));
        }
        return $input;
    }
}