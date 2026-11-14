<?php
/**
 * ANTRIAN SEHAT - Router Class
 */

class Router
{
    protected array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    public function get(string $pattern, string $action, array $middleware = []): self
    {
        $this->routes['GET'][$this->normalizePath($pattern)] = ['action' => $action, 'middleware' => $middleware];
        return $this;
    }

    public function post(string $pattern, string $action, array $middleware = []): self
    {
        $this->routes['POST'][$this->normalizePath($pattern)] = ['action' => $action, 'middleware' => $middleware];
        return $this;
    }

    public function any(string $pattern, string $action, array $middleware = []): self
    {
        $this->get($pattern, $action, $middleware);
        $this->post($pattern, $action, $middleware);
        return $this;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : $path;
    }

    public function loadRoutes(array $routes): self
    {
        if (isset($routes['GET'])) {
            foreach ($routes['GET'] as $pattern => $action) {
                is_array($action)
                    ? $this->get($pattern, $action['action'], $action['middleware'] ?? [])
                    : $this->get($pattern, $action);
            }
        }

        if (isset($routes['POST'])) {
            foreach ($routes['POST'] as $pattern => $action) {
                is_array($action)
                    ? $this->post($pattern, $action['action'], $action['middleware'] ?? [])
                    : $this->post($pattern, $action);
            }
        }

        return $this;
    }

    public function dispatch(): void
    {
        $url = $this->getUrl();
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!isset($this->routes[$method])) {
            $this->show405();
            return;
        }

        $routes = $this->routes[$method];

        foreach ($routes as $pattern => $action) {
            $params = $this->matchRoute($pattern, $url);

            if ($params !== false) {
                foreach ($action['middleware'] as $middleware) {
                    if (is_array($middleware)) {
                        [$class, $arguments] = $middleware;
                        (new $class())->handle(...$arguments);
                    } else {
                        (new $middleware())->handle();
                    }
                }
                $this->executeAction($action['action'], $params);
                return;
            }
        }

        $this->show404();
    }

    protected function getUrl(): string
    {
        if (!empty($_GET['url']) && is_string($_GET['url'])) {
            $path = '/' . ltrim($_GET['url'], '/');
        } else {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        }

        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        $basePath = rtrim(rawurldecode($basePath), '/');
        $path = rawurldecode($path);

        if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            $path = substr($path, strlen($basePath));
        }

        // Bersihkan awalan /public atau /index.php jika terbawa oleh rewrite InfinityFree
        $path = preg_replace('#^/public(/index\.php)?#i', '', $path);
        $path = preg_replace('#^/index\.php#i', '', $path);

        return $this->normalizePath($path ?: '/');
    }

    protected function matchRoute(string $pattern, string $url): array|false
    {
        $pattern = $this->normalizePath($pattern);
        $url = $this->normalizePath($url);

        if ($pattern === $url) {
            return [];
        }

        $patternParts = explode('/', $pattern);
        $urlParts = explode('/', $url);

        if (count($patternParts) !== count($urlParts)) {
            return false;
        }

        $params = [];

        for ($i = 0; $i < count($patternParts); $i++) {
            $patternPart = $patternParts[$i];
            $urlPart = $urlParts[$i];

            if (preg_match('/^\{([a-zA-Z_]+)\}$/', $patternPart, $matches)) {
                $params[$matches[1]] = $urlPart;
            } elseif ($patternPart !== $urlPart) {
                return false;
            }
        }

        return $params;
    }

    protected function executeAction(string $action, array $params): void
    {
        if (strpos($action, '@') === false) {
            throw new \Exception("Format action tidak valid: '$action'. Gunakan format 'Controller@method'.");
        }

        list($controllerName, $methodName) = explode('@', $action, 2);

        if (!class_exists($controllerName)) {
            throw new \Exception("Class '$controllerName' tidak ditemukan.");
        }
        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            throw new \Exception("Method '$methodName' tidak ditemukan di $controllerName.");
        }

        call_user_func_array([$controller, $methodName], array_values($params));
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

    protected function show405(): void
    {
        http_response_code(405);
        echo '405 - Method Tidak Diizinkan';
        exit;
    }
}