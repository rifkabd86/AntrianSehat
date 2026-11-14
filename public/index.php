<?php
// Tampilkan semua error untuk debugging sementara di InfinityFree
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/config/bootstrap.php';

try {
    $router = new Router();
    require BASE_PATH . '/routes/web.php';
    $router->dispatch();
} catch (PDOException $pdoException) {
    error_log($pdoException->__toString());
    http_response_code(500);
    echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; font-family: monospace; margin: 20px; border-radius: 5px;">';
    echo '<h3>⚠️ Database Error (PDOException):</h3>';
    echo '<p>' . htmlspecialchars($pdoException->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<pre>' . htmlspecialchars($pdoException->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '</div>';
} catch (Throwable $exception) {
    error_log($exception->__toString());
    http_response_code(500);
    echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; font-family: monospace; margin: 20px; border-radius: 5px;">';
    echo '<h3>⚠️ Application Error (Throwable):</h3>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8') . ' on line ' . $exception->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '</div>';
}