<?php

class AccessController extends Controller
{
    public function login(): void
    {
        $this->renderPage('pages/login', ['title' => 'Login']);
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . UrlHelper::to());
        exit;
    }
}