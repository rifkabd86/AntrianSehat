<?php

class View
{
    public function render(string $view, array $viewData = [], ?string $layout = null): void
    {
        $viewFile = $this->resolve($view . '.php');
        extract($viewData, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = $this->resolve('layouts/' . $layout . '.php');
        require $layoutFile;
    }

    private function resolve(string $relativePath): string
    {
        $root = realpath(BASE_PATH . '/views');
        $file = realpath(BASE_PATH . '/views/' . $relativePath);
        if ($root === false || $file === false || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) {
            throw new RuntimeException('Template tidak ditemukan.');
        }
        return $file;
    }
}