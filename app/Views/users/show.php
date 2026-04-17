<?php

declare(strict_types=1);

/** @var array<string, mixed> $user */
/** @var list<string> $roleLabels */

$user = is_array($user ?? null) ? $user : [];
$roleLabels = is_array($roleLabels ?? null) ? $roleLabels : [];
$uid = (int) ($user['id'] ?? 0);
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/configuracoes/usuarios">Voltar</a>
        <?php if (can('configuracoes.usuarios.edit') || can('users.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/configuracoes/usuarios/<?= $uid ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm module-shell__panel">
            <div class="card-body p-4">
                <h3 class="h6 text-white mb-3">Dados</h3>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-secondary">Status</dt>
                    <dd class="col-sm-8 text-white"><?= (int) ($user['status'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></dd>
                    <dt class="col-sm-4 text-secondary">Papéis</dt>
                    <dd class="col-sm-8 text-white"><?= $roleLabels === [] ? '—' : htmlspecialchars(implode(', ', $roleLabels), ENT_QUOTES, 'UTF-8') ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
