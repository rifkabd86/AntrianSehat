<?php

class Auth
{
    public static function check(): bool
    {
        return !empty(Session::get('user_id'));
    }

    public static function user(): ?array
    {
        $user = AuthMiddleware::getCurrentUser();
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['nama_lengkap'],
            'role' => $user['role'],
        ];
    }

    public static function hasRole(string|array $roles): bool
    {
        return AuthMiddleware::isRole($roles);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}