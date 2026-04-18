<?php
declare(strict_types=1);
$row = is_array($row ?? null) ? $row : [];
$items = is_array($items ?? null) ? $items : [];
$history = is_array($history ?? null) ? $history : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$typeLabels = is_array($typeLabels ?? null) ? $typeLabels : [];
$presetNotes = is_array($presetNotes ?? null) ? $presetNotes : [];
$mapsUrl = (string) ($mapsUrl ?? '#');
$rid = (int) ($row['id'] ?? 0);
$st = (string) ($row['status'] ?? '');
?>
<div class="mb-3">
    <a href="/locacoes/painel-entregador" class="small text-secondary text-decoration-none">← Voltar</a>
</div>
<h1 class="h5 mb-1"><?= htmlspecialchars((string) ($row['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
<p class="small text-secondary mb-3"><?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($typeLabels[(string) ($row['operation_type'] ?? '')] ?? '', ENT_QUOTES, 'UTF-8') ?></p>

<a class="btn btn-success w-100 mb-3 py-2" href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="bi bi-geo-alt-fill"></i> Abrir no Google Maps</a>

<div class="entregador-card p-3 mb-3">
    <div class="fw-semibold mb-1"><?= htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    <div class="small text-secondary mb-2">
        <?= htmlspecialchars(trim(implode(', ', array_filter([
            (string) ($row['street'] ?? ''),
            (string) ($row['address_number'] ?? ''),
            (string) ($row['complement'] ?? ''),
            (string) ($row['district'] ?? ''),
            (string) ($row['city'] ?? ''),
            (string) ($row['state'] ?? ''),
        ]))), ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php if (trim((string) ($row['reference'] ?? '')) !== ''): ?>
        <div class="small"><span class="text-secondary">Ref.:</span> <?= htmlspecialchars((string) $row['reference'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <div class="small mt-2">
        <a class="text-white" href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', (string) ($row['phone_primary'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($row['phone_primary'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php if (trim((string) ($row['phone_secondary'] ?? '')) !== ''): ?>
            · <a class="text-white" href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', (string) ($row['phone_secondary'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $row['phone_secondary'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="entregador-card p-3 mb-3">
    <div class="small text-secondary mb-2">Itens</div>
    <ul class="list-unstyled small mb-0">
        <?php foreach ($items as $it): ?>
            <li class="mb-1"><?= htmlspecialchars((string) ($it['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <span class="text-secondary">× <?= htmlspecialchars((string) ($it['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></li>
        <?php endforeach; ?>
    </ul>
</div>

<?php if (trim((string) ($row['notes_driver'] ?? '')) !== ''): ?>
    <div class="entregador-card p-3 mb-3">
        <div class="small text-secondary mb-1">Observações</div>
        <div class="small"><?= nl2br(htmlspecialchars((string) $row['notes_driver'], ENT_QUOTES, 'UTF-8')) ?></div>
    </div>
<?php endif; ?>

<?php if (can('locacoes.entregador.update_status') && !in_array($st, ['cancelled', 'delivered', 'delivered_issues', 'collected'], true)): ?>
    <div class="entregador-card p-3 mb-3">
        <div class="small text-secondary mb-2">Atualizar status</div>
        <form method="post" action="/locacoes/painel-entregador/<?= $rid ?>/status" class="d-flex flex-column gap-2">
            <?= \App\Helpers\Csrf::field() ?>
            <label class="small text-secondary">Observação / ocorrência</label>
            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Opcional"></textarea>
            <div class="d-flex flex-wrap gap-1">
                <?php foreach ($presetNotes as $pn): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary lumis-preset-note" data-text="<?= htmlspecialchars($pn, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pn, ENT_QUOTES, 'UTF-8') ?></button>
                <?php endforeach; ?>
            </div>
            <div class="d-grid gap-2">
                <?php if (!in_array($st, ['in_route'], true)): ?>
                    <button type="submit" name="action" value="iniciar_rota" class="btn btn-outline-info">Iniciar rota</button>
                <?php endif; ?>
                <button type="submit" name="action" value="entregue" class="btn btn-primary">Marcar como entregue</button>
                <button type="submit" name="action" value="entregue_ressalvas" class="btn btn-warning text-dark">Entregue com ressalvas</button>
                <?php if (in_array((string) ($row['operation_type'] ?? ''), ['pickup', 'both'], true)): ?>
                    <button type="submit" name="action" value="coleta_pendente" class="btn btn-outline-light">Coleta pendente</button>
                    <button type="submit" name="action" value="coletado" class="btn btn-success">Coleta realizada</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <script>
    document.querySelectorAll('.lumis-preset-note').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ta = document.querySelector('textarea[name="note"]');
            if (ta) ta.value = btn.getAttribute('data-text') || '';
        });
    });
    </script>
<?php endif; ?>

<div class="small text-secondary mt-4">Histórico recente</div>
<ul class="list-unstyled small">
    <?php foreach (array_slice($history, 0, 8) as $h): ?>
        <li class="mb-1"><?= htmlspecialchars((string) ($h['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($statusLabels[(string) ($h['to_status'] ?? '')] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
</ul>
