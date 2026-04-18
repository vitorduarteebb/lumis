<?php
declare(strict_types=1);
$row = is_array($row ?? null) ? $row : [];
$items = is_array($items ?? null) ? $items : [];
$history = is_array($history ?? null) ? $history : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$typeLabels = is_array($typeLabels ?? null) ? $typeLabels : [];
$mapsUrl = (string) ($mapsUrl ?? '#');
$rid = (int) ($row['id'] ?? 0);
$basePath = '/locacoes/gerenciar';
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Locações</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($row['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars($typeLabels[(string) ($row['operation_type'] ?? '')] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-success btn-sm rounded-3" href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="bi bi-geo-alt"></i> Google Maps</a>
        <?php if (can('locacoes.gerenciar.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $rid . '/editar', ENT_QUOTES, 'UTF-8') ?>">Editar</a>
        <?php endif; ?>
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Lista</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="lumis-form-section">
            <div class="lumis-form-section__title">Resumo</div>
            <p class="small text-secondary mb-1">Cliente: <span class="text-white"><?= htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></p>
            <p class="small text-secondary mb-1">Status: <span class="badge text-bg-secondary"><?= htmlspecialchars($statusLabels[(string) ($row['status'] ?? '')] ?? '', ENT_QUOTES, 'UTF-8') ?></span></p>
            <p class="small text-secondary mb-1">Entregador: <?= htmlspecialchars((string) ($row['delivery_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="small text-secondary mb-0">Datas: locação <?= htmlspecialchars((string) ($row['rental_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                · prev. entrega <?= htmlspecialchars((string) ($row['expected_delivery_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                · prev. coleta <?= htmlspecialchars((string) ($row['expected_pickup_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="lumis-form-section mt-3">
            <div class="lumis-form-section__title">Endereço</div>
            <p class="small text-white mb-0">
                <?= htmlspecialchars(trim(implode(', ', array_filter([
                    (string) ($row['street'] ?? ''),
                    (string) ($row['address_number'] ?? ''),
                    (string) ($row['complement'] ?? ''),
                    (string) ($row['district'] ?? ''),
                    (string) ($row['city'] ?? ''),
                    (string) ($row['state'] ?? ''),
                    (string) ($row['cep'] ?? ''),
                ]))), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php if (trim((string) ($row['reference'] ?? '')) !== ''): ?>
                <p class="small text-secondary mt-2 mb-0">Ref.: <?= htmlspecialchars((string) $row['reference'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
        <div class="lumis-form-section mt-3">
            <div class="lumis-form-section__title">Contato</div>
            <p class="small text-secondary mb-1"><?= htmlspecialchars((string) ($row['contact_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="small text-secondary mb-0">Tel.: <?= htmlspecialchars((string) ($row['phone_primary'] ?? '—'), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($row['phone_secondary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="lumis-form-section">
            <div class="lumis-form-section__title">Itens</div>
            <ul class="list-unstyled small mb-0">
                <?php foreach ($items as $it): ?>
                    <li class="mb-2 text-secondary">
                        <span class="text-white"><?= htmlspecialchars((string) ($it['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        · qtd <?= htmlspecialchars((string) ($it['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php if (trim((string) ($it['notes'] ?? '')) !== ''): ?>
                            <br><span class="small"><?= htmlspecialchars((string) $it['notes'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php if ($items === []): ?><li class="text-secondary">Sem itens.</li><?php endif; ?>
            </ul>
        </div>
        <div class="lumis-form-section mt-3">
            <div class="lumis-form-section__title">Observações</div>
            <p class="small text-secondary mb-1"><strong class="text-white">Internas:</strong> <?= nl2br(htmlspecialchars((string) ($row['notes_internal'] ?? ''), ENT_QUOTES, 'UTF-8')) ?: '—' ?></p>
            <p class="small text-secondary mb-0"><strong class="text-white">Entregador:</strong> <?= nl2br(htmlspecialchars((string) ($row['notes_driver'] ?? ''), ENT_QUOTES, 'UTF-8')) ?: '—' ?></p>
        </div>
    </div>
</div>

<div class="lumis-form-section mt-3">
    <div class="lumis-form-section__title">Histórico de status</div>
    <div class="table-responsive">
        <table class="table lumis-table mb-0">
            <thead><tr><th>Quando</th><th>De</th><th>Para</th><th>Obs.</th><th>Usuário</th></tr></thead>
            <tbody>
                <?php if ($history === []): ?><tr><td colspan="5" class="text-secondary small">Sem registros.</td></tr><?php endif; ?>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td class="small"><?= htmlspecialchars((string) ($h['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars($statusLabels[(string) ($h['from_status'] ?? '')] ?? (string) ($h['from_status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars($statusLabels[(string) ($h['to_status'] ?? '')] ?? (string) ($h['to_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($h['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($h['user_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <?php if (can('locacoes.gerenciar.edit') && ($row['status'] ?? '') !== 'cancelled'): ?>
        <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/cancelar', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Cancelar esta locação?');"><?= \App\Helpers\Csrf::field() ?>
            <button type="submit" class="btn btn-outline-warning btn-sm">Cancelar locação</button>
        </form>
    <?php endif; ?>
    <?php if (can('locacoes.gerenciar.delete')): ?>
        <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/excluir', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Excluir (lógico) esta locação?');"><?= \App\Helpers\Csrf::field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
        </form>
    <?php endif; ?>
</div>
