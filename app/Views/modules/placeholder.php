<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $description */
/** @var string $icon */
/** @var list<string> $nextSteps */
/** @var array{label: string, href?: string, disabled?: bool, hint?: string}|null $primaryAction */

$icon = isset($icon) && is_string($icon) ? $icon : 'bi-layers';
$nextSteps = isset($nextSteps) && is_array($nextSteps) ? $nextSteps : [];
$primaryAction = isset($primaryAction) && is_array($primaryAction) ? $primaryAction : null;
?>

<div class="module-shell">
    <div class="module-shell__hero card border-0 shadow-sm overflow-hidden mb-4">
        <div class="module-shell__hero-inner p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row align-items-start gap-4">
                <div class="module-shell__icon rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> fs-2" aria-hidden="true"></i>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <p class="text-secondary small text-uppercase fw-semibold letter-spacing mb-2">Módulo</p>
                    <h2 class="h4 text-white mb-3"><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="text-secondary mb-0 lh-lg"><?= htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100 module-shell__panel">
                <div class="card-body p-4">
                    <h3 class="h6 text-white mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle text-primary" aria-hidden="true"></i>
                        Próximos passos sugeridos
                    </h3>
                    <ul class="list-unstyled mb-0 module-shell__steps">
                        <?php foreach ($nextSteps as $step): ?>
                            <li class="d-flex gap-2 mb-3">
                                <span class="module-shell__bullet mt-1" aria-hidden="true"></span>
                                <span class="text-secondary small"><?= htmlspecialchars((string) $step, ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100 module-shell__panel">
                <div class="card-body p-4 d-flex flex-column">
                    <h3 class="h6 text-white mb-2">Área em construção</h3>
                    <p class="text-secondary small mb-4">
                        Esta tela confirma navegação, permissões e layout. As ações de negócio serão adicionadas aqui sem mudar URLs ou slugs de permissão.
                    </p>
                    <?php if ($primaryAction !== null && isset($primaryAction['label'])): ?>
                        <?php
                        $paLabel = (string) $primaryAction['label'];
                        $paHref = isset($primaryAction['href']) ? (string) $primaryAction['href'] : '#';
                        $paDisabled = !empty($primaryAction['disabled']);
                        $paHint = isset($primaryAction['hint']) ? (string) $primaryAction['hint'] : '';
                        ?>
                        <?php if ($paDisabled || $paHref === '#'): ?>
                            <button type="button" class="btn btn-primary w-100 rounded-3 disabled" disabled>
                                <?= htmlspecialchars($paLabel, ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        <?php else: ?>
                            <a class="btn btn-primary w-100 rounded-3" href="<?= htmlspecialchars($paHref, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($paLabel, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($paHint !== ''): ?>
                            <p class="text-secondary small mt-2 mb-0"><?= htmlspecialchars($paHint, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="rounded-3 border border-secondary-subtle p-3 bg-dark bg-opacity-25 small text-secondary">
                            Ação principal será habilitada quando o fluxo de negócio for implementado (ex.: novo cadastro, importação, assistente).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
