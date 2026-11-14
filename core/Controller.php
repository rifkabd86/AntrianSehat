<?php
/**
 * ANTRIAN SEHAT - Base Controller
 * 
 * Kelas dasar untuk semua controller.
 * Menyediakan method untuk memuat model dan view.
 */

class Controller
{
    /**
     * Load model class
     */
    protected function model(string $model): object
    {
        $modelFile = BASE_PATH . '/models/' . $model . '.php';

        if (!file_exists($modelFile)) {
            throw new \Exception("Model '$model' tidak ditemukan di: $modelFile");
        }

        require_once $modelFile;

        if (!class_exists($model)) {
            throw new \Exception("Class '$model' tidak ditemukan dalam file model.");
        }

        return new $model();
    }

    /**
     * Load view file with optional data and layout support
     */
    protected function view(string $view, array $data = []): void
    {
        (new View())->render($view, $data, $data['layout'] ?? null);
    }

    protected function renderPage(string $view, array $data = []): void
    {
        (new View())->render($view, $data, 'foundation');
    }

    /**
     * Send JSON response
     */
    protected function json($data, int $statusCode = 200): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect ke URL tertentu dengan memastikan session tersimpan ke disk lebih dulu
     */
    protected function redirect(string $url): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }

        $target = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        if (!headers_sent()) {
            header('Location: ' . $target);
        } else {
            echo '<script>window.location.href=' . json_encode($target) . ';</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        }
        exit;
    }

    /**
     * Cek apakah request menggunakan method POST
     */
    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Cek apakah request menggunakan AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get POST data with optional sanitization
     */
    protected function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET query parameter
     */
    protected function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
}