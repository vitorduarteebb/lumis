<?php
$e = is_array($employee ?? null) ? $employee : [];
$id = (int) ($e['id'] ?? 0);
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Cadastros</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($e['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/cadastros/funcionarios">Voltar</a>
        <?php if (can('cadastros.funcionarios.edit') || can('employees.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/cadastros/funcionarios/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>
<div class="card border-0 shadow-sm module-shell__panel">
    <div class="card-body p-4 small">
        <div class="row g-3">
            <div class="col-md-6"><div class="text-secondary">Cargo</div><div class="text-white"><?= htmlspecialchars((string) ($e['job_title'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">Documento</div><div class="text-white"><?= htmlspecialchars((string) ($e['document'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">E-mail</div><div class="text-white"><?= htmlspecialchars((string) ($e['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">Telefone</div><div class="text-white"><?= htmlspecialchars((string) ($e['phone'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">Admissão</div><div class="text-white"><?= htmlspecialchars((string) ($e['hire_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">Status</div><div class="text-white"><?= (int) ($e['status'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></div></div>
            <?php if (!empty($e['notes'])): ?>
                <div class="col-12"><div class="text-secondary">Observações</div><div class="text-white"><?= nl2br(htmlspecialchars((string) $e['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
