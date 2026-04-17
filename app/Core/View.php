<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $viewPath = base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
        if (!is_file($viewPath)) {
            throw new \InvalidArgumentException('View não encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        $content = (string) ob_get_clean();

        if ($layout === null || $layout === '') {
            return $content;
        }

        $layoutPath = base_path('app/Views/' . str_replace('.', '/', $layout) . '.php');
        if (!is_file($layoutPath)) {
            return $content;
        }

        ob_start();
        include $layoutPath;
        return (string) ob_get_clean();
    }
}
