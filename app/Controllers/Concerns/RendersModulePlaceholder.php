<?php

declare(strict_types=1);

namespace App\Controllers\Concerns;

/**
 * Telas base de módulo (placeholder evolutivo) com layout premium.
 */
trait RendersModulePlaceholder
{
    /**
     * @param array<string, mixed> $payload
     */
    protected function modulePlaceholder(array $payload): string
    {
        $defaults = [
            'pageTitle' => $payload['title'],
            'nextSteps' => [
                'Permissões e rotas já integradas — evolua com CRUDs e serviços de domínio.',
                'Mantenha breadcrumbs e títulos alinhados ao `config/navigation.php`.',
            ],
            'icon' => 'bi-layers',
            'primaryAction' => null,
        ];

        return $this->view('modules/placeholder', array_merge($defaults, $payload));
    }
}
