<?php

declare(strict_types=1);

$counts = is_array($counts ?? null) ? $counts : [];
$recent = is_array($recent ?? null) ? $recent : [];
$overdue = is_array($overdue ?? null) ? $overdue : [];
$filteredRows = is_array($filteredRows ?? null) ? $filteredRows : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$openStatuses = is_array($openStatuses ?? null) ? $openStatuses : [];
$technicians = is_array($technicians ?? null) ? $technicians : [];
$clients = is_array($clients ?? null) ? $clients : [];
$dateFrom = (string) ($dateFrom ?? '');
$dateTo = (string) ($dateTo ?? '');
$techFilter = (int) ($techFilter ?? 0);
$clientFilter = (int) ($clientFilter ?? 0);

$prLabels = ['low' => 'Baixa', 'normal' => 'Média', 'high' => 'Alta', 'urgent' => 'Urgente'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Ordens de serviço</div>
        <h2 class="h4 mb-1 text-white">Painel operacional</h2>
        <div class="text-secondary small">Contagens por status, atrasos e lista filtrada.</div>
    </div>
    <a class="btn btn-primary btn-sm rounded-3" href="/ordens-servico/novo">Nova O.S.</a>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($statusLabels as $slug => $label): ?>
        <?php $n = (int) ($counts[$slug] ?? 0); ?>
        <div class="col-6 col-md-4 col-lg">
            <div class="p-3 rounded-3 border border-secondary-subtle bg-dark bg-opacity-25 h-100">
                <div class="text-secondary small"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="h4 text-white mb-0"><?= $n ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<form method="get" action="/ordens-servico/painel" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <input type="date" name="from" class="form-control app-input" style="max-width: 155px;" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="to" class="form-control app-input" style="max-width: 155px;" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
    <select name="tech" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Técnico (todos)</option>
        <?php foreach ($technicians as $t): ?>
            <?php $tid = (int) ($t['id'] ?? 0); ?>
            <option value="<?= $tid ?>" <?= $techFilter === $tid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($t['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="client" class="form-select app-input" style="max-width: 220px;">
        <option value="0">Cliente (todos)</option>
        <?php foreach ($clients as $c): ?>
            <?php $cid = (int) ($c['id'] ?? 0); ?>
            <option value="<?= $cid ?>" <?= $clientFilter === $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Aplicar filtros</button>
</form>

<div class="row g-3">
    <div class="col-lg-6">
        <h3 class="h6 text-white">Atrasadas (previsão vencida)</h3>
        <div class="lumis-table-wrap">
            <table class="table lumis-table mb-0">
                <thead><tr><th>Código</th><th>Cliente</th><th>Previsão</th></tr></thead>
                <tbody>
                    <?php if ($overdue === []): ?>
                        <tr><td colspan="3" class="text-secondary small py-3">Nenhuma O.S. atrasada.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($overdue as $r): ?>
                        <tr>
                            <td><a class="text-white text-decoration-none" href="/ordens-servico/<?= (int) ($r['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($r['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                            <td class="text-secondary small"><?= htmlspecialchars((string) ($r['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-danger small"><?= htmlspecialchars((string) ($r['expected_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <h3 class="h6 text-white">Recentes</h3>
        <div class="lumis-table-wrap">
            <table class="table lumis-table mb-0">
                <thead><tr><th>Código</th><th>Cliente</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($recent as $r): ?>
                        <?php $st = (string) ($r['status'] ?? ''); ?>
                        <tr>
                            <td><a class="text-white text-decoration-none" href="/ordens-servico/<?= (int) ($r['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($r['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                            <td class="text-secondary small"><?= htmlspecialchars((string) ($r['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-secondary small"><?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <h3 class="h6 text-white">Lista filtrada (últimas 30)</h3>
    <div class="lumis-table-wrap">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Prioridade</th>
                    <th>Abertura</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filteredRows as $r): ?>
                    <?php $st = (string) ($r['status'] ?? ''); $pr = (string) ($r['priority'] ?? ''); ?>
                    <tr>
                        <td><a class="text-white text-decoration-none font-monospace" href="/ordens-servico/<?= (int) ($r['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($r['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($r['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= in_array($st, $openStatuses, true) ? '<span class="badge text-bg-warning text-dark">' . htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') . '</span>' : '<span class="badge badge-lumis badge-lumis-secondary">' . htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') . '</span>' ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars($prLabels[$pr] ?? $pr, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($r['opened_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
