<?php

declare(strict_types=1);

$cSum = is_array($cSum ?? null) ? $cSum : [];
$rows = is_array($rows ?? null) ? $rows : [];
$ckind = (string) ($ckind ?? 'all');
$ckLab = ['all' => 'Todos os tipos', 'service' => 'Prestação de serviço', 'rental' => 'Locação', 'subscription' => 'Assinatura'];
$stL = ['active' => 'Ativo', 'suspended' => 'Suspenso', 'closed' => 'Encerrado', 'cancelled' => 'Cancelado'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Contratos</h2>
        <div class="text-secondary small">Visão consolidada de serviços, locações e assinaturas.</div>
    </div>
</div>

<div class="row g-2 mb-3" data-report-scope="contratos">
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Serviços (total)</div>
            <div class="h6 text-white mb-0"><?= (int) ($cSum['svc'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Locações</div>
            <div class="h6 text-white mb-0"><?= (int) ($cSum['rent'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Assinaturas</div>
            <div class="h6 text-white mb-0"><?= (int) ($cSum['sub'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Serv. ativos</div>
            <div class="h6 text-success mb-0"><?= (int) ($cSum['svc_active'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Vencendo (30 dias)</div>
            <div class="h6 text-warning mb-0"><?= (int) ($cSum['ending_soon'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="rounded-3 border border-secondary-subtle p-3 h-100 bg-dark bg-opacity-25">
            <div class="text-secondary small">Suspensos / encerr. ou canc.</div>
            <div class="small text-white"><?= (int) ($cSum['suspended'] ?? 0) ?> / <?= (int) ($cSum['cancelled'] ?? 0) ?></div>
        </div>
    </div>
</div>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/contratos'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="ckind" class="form-select app-input" style="max-width: 220px;">
        <?php foreach ($ckLab as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $ckind === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="form-select app-input" style="max-width: 180px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['active', 'suspended', 'closed', 'cancelled'] as $s): ?>
            <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($stL[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="client_id" class="form-select app-input" style="max-width: 240px;">
        <option value="0">Cliente (todos)</option>
        <?php foreach (is_array($clients ?? null) ? $clients : [] as $c): ?>
            <option value="<?= (int) ($c['id'] ?? 0) ?>" <?= (int) ($clientId ?? 0) === (int) ($c['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="contratos">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Ref.</th>
                <th>Cliente</th>
                <th>Status</th>
                <th class="text-end">Valor</th>
                <th>Início</th>
                <th>Fim / próximo</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="8" class="text-secondary small py-4">Nenhum contrato encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $ck = (string) ($row['ckind'] ?? '');
                $rid = (int) ($row['id'] ?? 0);
                $href = match ($ck) {
                    'rental' => '/contratos/locacoes/' . $rid,
                    'subscription' => '/contratos/assinaturas/' . $rid,
                    default => '/contratos/servicos/' . $rid,
                };
                $tipoBr = match ($ck) {
                    'rental' => 'Locação',
                    'subscription' => 'Assinatura',
                    default => 'Serviço',
                };
                $st = (string) ($row['status'] ?? '');
                ?>
                <tr>
                    <td class="text-secondary small"><?= htmlspecialchars($tipoBr, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-white font-monospace small"><?= htmlspecialchars((string) ($row['ref_num'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $st === 'active' ? 'badge-lumis-success' : ($st === 'suspended' ? 'badge-lumis-warning' : 'text-bg-secondary') ?>"><?= htmlspecialchars($stL[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['start_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['end_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>
