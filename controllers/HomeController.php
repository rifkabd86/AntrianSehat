<?php
/**
 * ANTRIAN SEHAT - Home Controller
 */

class HomeController extends Controller
{
    public function index(): void
    {
        if (AuthMiddleware::isAuthenticated()) {
            $this->redirect('dashboard');
        } else {
            $this->redirect('login');
        }
    }
}
