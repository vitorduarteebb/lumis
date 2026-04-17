<?php
$c = is_array($carrier ?? null) ? $carrier : [];
$id = (int) ($c['id'] ?? 0);
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Cadastros</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($c['legal_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars((string) ($c['trade_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/cadastros/transportadoras">Voltar</a>
        <?php if (can('cadastros.transportadoras.edit') || can('carriers.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/cadastros/transportadoras/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>
<div class="card border-0 shadow-sm module-shell__panel">
    <div class="card-body p-4 small">
        <div class="row g-3">
            <div class="col-md-6"><div class="text-secondary">CNPJ</div><div class="text-white"><?= htmlspecialchars((string) ($c['document'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">IE</div><div class="text-white"><?= htmlspecialchars((string) ($c['state_registration'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">E-mail</div><div class="text-white"><?= htmlspecialchars((string) ($c['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-md-6"><div class="text-secondary">Telefone</div><div class="text-white"><?= htmlspecialchars((string) ($c['phone'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="col-12"><div class="text-secondary">Endereço</div><div class="text-white"><?php
                $parts = array_filter([$c['street'] ?? '', $c['address_number'] ?? '', $c['city'] ?? '', $c['state'] ?? '', $c['cep'] ?? ''], static fn ($x) => $x !== null && $x !== '');
                echo htmlspecialchars(implode(' · ', $parts) ?: '—', ENT_QUOTES, 'UTF-8');
            ?></div></div>
            <?php if (!empty($c['notes'])): ?>
                <div class="col-12"><div class="text-secondary">Observações</div><div class="text-white"><?= nl2br(htmlspecialchars((string) $c['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
