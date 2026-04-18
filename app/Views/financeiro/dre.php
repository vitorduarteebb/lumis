<?php

declare(strict_types=1);

/** @var string $mes */
/** @var string $periodoInicio */
/** @var string $periodoFim */
/** @var array{receita: float, despesas: float, resultado: float} $dre */

$mes = (string) ($mes ?? date('Y-m'));
$dre = is_array($dre ?? null) ? $dre : ['receita' => 0.0, 'despesas' => 0.0, 'resultado' => 0.0];
$periodoInicio = (string) ($periodoInicio ?? '');
$periodoFim = (string) ($periodoFim ?? '');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Financeiro</div>
        <h2 class="h4 mb-1 text-white">DRE gerencial</h2>
        <div class="text-secondary small">Receita de vendas (documentos) × despesas com vencimento no período. Indicador sintético para acompanhamento.</div>
    </div>
</div>

<form method="get" action="/financeiro/dre-gerencial" class="lumis-toolbar mb-4 d-flex flex-wrap gap-2 align-items-end">
    <div>
        <label class="form-label lumis-label small mb-1" for="mes">Mês de referência</label>
        <input type="month" class="form-control app-input" id="mes" name="mes" value="<?= htmlspecialchars($mes, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-primary rounded-3">Atualizar</button>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card app-card border-secondary-subtle h-100">
            <div class="card-body">
                <div class="text-secondary small">Receita (vendas no período)</div>
                <div class="h4 text-white mb-0"><?= htmlspecialchars(lumis_money_br((float) $dre['receita']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card app-card border-secondary-subtle h-100">
            <div class="card-body">
                <div class="text-secondary small">Despesas (títulos a pagar — vencimento)</div>
                <div class="h4 text-white mb-0"><?= htmlspecialchars(lumis_money_br((float) $dre['despesas']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card app-card border-secondary-subtle h-100">
            <div class="card-body">
                <div class="text-secondary small">Resultado aproximado</div>
                <div class="h4 mb-0 <?= ($dre['resultado'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>"><?= htmlspecialchars(lumis_money_br((float) $dre['resultado']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
</div>

<p class="text-secondary small">Período: <?= htmlspecialchars($periodoInicio, ENT_QUOTES, 'UTF-8') ?> a <?= htmlspecialchars($periodoFim, ENT_QUOTES, 'UTF-8') ?>.</p>
