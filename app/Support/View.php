<?php

namespace App\Support;

class View
{
    private static string $baseViewPath = __DIR__ . '/../Views/';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewFile = self::$baseViewPath . trim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: " . $viewFile);
        }

        // Extract variables to scope
        extract($data);

        // Render inner view
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = self::$baseViewPath . trim($layout, '/') . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout file not found: " . $layoutFile);
            }
            // Layout receives $content plus original data
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function component(string $name, array $props = []): void
    {
        $compFile = self::$baseViewPath . 'components/' . trim($name, '/') . '.php';
        if (file_exists($compFile)) {
            extract($props);
            require $compFile;
        }
    }
}
