<?php

declare(strict_types=1);

/** @var array<string, mixed> $supplier */

$c = is_array($supplier ?? null) ? $supplier : [];
$id = (int) ($c['id'] ?? 0);
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Cadastros</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= ($c['person_type'] ?? '') === 'J' ? 'Pessoa jurídica' : 'Pessoa física' ?></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/cadastros/fornecedores">Voltar</a>
        <?php if (can('cadastros.fornecedores.edit') || can('suppliers.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/cadastros/fornecedores/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm module-shell__panel">
    <div class="card-body p-4">
        <div class="row g-3 small">
            <div class="col-md-6">
                <div class="text-secondary">Documento</div>
                <div class="text-white"><?= htmlspecialchars((string) ($c['document'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary">E-mail</div>
                <div class="text-white"><?= htmlspecialchars((string) ($c['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary">Telefone / Celular</div>
                <div class="text-white"><?= htmlspecialchars(trim(($c['phone'] ?? '') . ' / ' . ($c['mobile'] ?? ''), ' /'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary">Status</div>
                <div class="text-white"><?= (int) ($c['status'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></div>
            </div>
            <div class="col-12">
                <div class="text-secondary">Endereço</div>
                <div class="text-white">
                    <?php
                    $parts = array_filter([
                        $c['street'] ?? '',
                        $c['address_number'] ?? '',
                        $c['complement'] ?? '',
                        $c['district'] ?? '',
                        $c['city'] ?? '',
                        $c['state'] ?? '',
                        $c['cep'] ?? '',
                    ], static fn ($x) => $x !== null && $x !== '');
                    echo htmlspecialchars(implode(' · ', $parts) ?: '—', ENT_QUOTES, 'UTF-8');
                    ?>
                </div>
            </div>
            <?php if (!empty($c['notes'])): ?>
                <div class="col-12">
                    <div class="text-secondary">Observações</div>
                    <div class="text-white"><?= nl2br(htmlspecialchars((string) $c['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
