<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */

$rows = $rows ?? [];

$stLabel = static function (string $s): string {
    return match ($s) {
        'pending' => 'Pendente',
        'paid' => 'Pago',
        'cancelled' => 'Cancelado',
        default => $s,
    };
};
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Financeiro</div>
        <h2 class="h4 mb-1 text-white">Boletos bancários</h2>
        <div class="text-secondary small">Registro de cobranças e acompanhamento de status (sem remessa/retorno automático).</div>
    </div>
</div>

<div class="card app-card border-secondary-subtle mb-4">
    <div class="card-body">
        <h3 class="h6 text-white mb-3">Novo boleto</h3>
        <form method="post" action="/financeiro/boletos-bancarios" class="row g-3 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="_action" value="store">
            <div class="col-md-3">
                <label class="form-label lumis-label" for="payer">Pagador</label>
                <input type="text" class="form-control app-input" id="payer" name="payer_name" placeholder="Nome">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="b_amt">Valor (R$)</label>
                <input type="text" class="form-control app-input" id="b_amt" name="amount" required placeholder="0,00">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="b_due">Vencimento</label>
                <input type="date" class="form-control app-input" id="b_due" name="due_date" required>
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="our">Nosso número</label>
                <input type="text" class="form-control app-input" id="our" name="our_number" placeholder="Opcional">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="notes">Obs.</label>
                <input type="text" class="form-control app-input" id="notes" name="notes" placeholder="Opcional">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100">Salvar</button>
            </div>
        </form>
    </div>
</div>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Pagador</th>
                <th class="text-end">Valor</th>
                <th>Vencimento</th>
                <th>Nosso número</th>
                <th>Status</th>
                <th class="text-end">Alterar status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhum boleto cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? 'pending');
                ?>
                <tr>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['payer_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-white small"><?= htmlspecialchars(lumis_money_br((float) ($row['amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars(substr((string) ($row['due_date'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small font-monospace"><?= htmlspecialchars((string) ($row['our_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge badge-lumis <?= $st === 'paid' ? 'badge-lumis-success' : ($st === 'cancelled' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stLabel($st), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-end">
                        <?php if (can('financeiro.boletos_bancarios.view')): ?>
                            <form method="post" action="/financeiro/boletos-bancarios" class="d-inline-flex gap-1 align-items-center justify-content-end">
                                <?= \App\Helpers\Csrf::field() ?>
                                <input type="hidden" name="_action" value="status">
                                <input type="hidden" name="id" value="<?= $rid ?>">
                                <select name="status" class="form-select form-select-sm app-input" style="width: auto;">
                                    <?php foreach (['pending', 'paid', 'cancelled'] as $sv): ?>
                                        <option value="<?= $sv ?>" <?= $st === $sv ? 'selected' : '' ?>><?= htmlspecialchars($stLabel($sv), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-lumis-secondary">OK</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
