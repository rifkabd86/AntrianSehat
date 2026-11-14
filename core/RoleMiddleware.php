<?php

class RoleMiddleware
{
    public function handle(array $roles): void
    {
        AuthMiddleware::requireRole($roles);
    }
}