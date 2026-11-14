<?php
/**
 * ANTRIAN SEHAT - Main Application Class
 */

class App
{
    protected string $controller = 'AuthController';
    protected string $method = 'login';
    protected array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        if ($url !== null && !empty($url[0])) {
            // Jika URL adalah 'login', 'register', 'logout', arahkan ke AuthController
            $authMethods = ['login', 'register', 'logout', 'forgot-password', 'profile', 'change-password'];
            if (in_array(strtolower($url[0]), $authMethods, true)) {
                $this->controller = 'AuthController';
                $this->method = $this->toCamelCase($url[0]);
                unset($url[0]);
            } else {
                $controllerName = $this->toPascalCase($url[0]) . 'Controller';
                $controllerFile = BASE_PATH . '/controllers/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $this->controller = $controllerName;
                    $this->method = 'index';
                    unset($url[0]);
                } else {
                    $this->show404();
                    return;
                }
            }
        }

        $controllerFile = BASE_PATH . '/controllers/' . $this->controller . '.php';
        if (!file_exists($controllerFile)) {
            $this->show404();
            return;
        }
        require_once $controllerFile;

        if (!class_exists($this->controller)) {
            $this->show404();
            return;
        }
        $controllerInstance = new $this->controller();

        if (isset($url[1]) && !empty($url[1])) {
            $methodName = $this->toCamelCase($url[1]);
            if (method_exists($controllerInstance, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            } else {
                $this->show404();
                return;
            }
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }

    protected function parseUrl(): ?array
    {
        if (!empty($_GET['url'])) {
            $url = trim((string) $_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return $url !== '' ? explode('/', $url) : null;
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = rawurldecode($path);
        $path = preg_replace('#^/public(/index\.php)?#i', '', $path);
        $path = preg_replace('#^/index\.php#i', '', $path);
        $path = trim((string) $path, '/');

        return $path !== '' ? explode('/', $path) : null;
    }

    protected function toPascalCase(string $string): string
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        return str_replace(' ', '', $string);
    }

    protected function toCamelCase(string $string): string
    {
        $pascal = $this->toPascalCase($string);
        return lcfirst($pascal);
    }

    protected function show404(): void
    {
        http_response_code(404);
        $errorFile = BASE_PATH . '/views/errors/404.php';
        if (file_exists($errorFile)) {
            require_once $errorFile;
        } else {
            echo '404 - Halaman Tidak Ditemukan';
        }
        exit;
    }
}