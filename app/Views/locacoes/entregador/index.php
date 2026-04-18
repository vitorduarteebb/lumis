<?php
declare(strict_types=1);
$rows = is_array($rows ?? null) ? $rows : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$typeLabels = is_array($typeLabels ?? null) ? $typeLabels : [];
$pollTs = (string) ($pollTs ?? '');
?>
<h1 class="h5 mb-3">Suas entregas</h1>

<form method="get" action="/locacoes/painel-entregador" class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <select name="status" class="form-select form-select-sm" style="max-width:200px">
        <option value="all">Todos os status</option>
        <?php foreach ($statusLabels as $sk => $sl): ?>
            <option value="<?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?>" <?= ($status ?? '') === $sk ? 'selected' : '' ?>><?= htmlspecialchars($sl, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm btn-outline-light">Filtrar</button>
</form>

<div class="d-flex flex-column gap-2">
    <?php if ($rows === []): ?>
        <p class="text-secondary small">Nenhuma entrega atribuída no momento.</p>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
        <?php
        $rid = (int) ($row['id'] ?? 0);
        $st = (string) ($row['status'] ?? '');
        $ot = (string) ($row['operation_type'] ?? '');
        ?>
        <a href="/locacoes/painel-entregador/<?= $rid ?>" class="text-decoration-none entregador-card p-3 d-block text-white">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="font-monospace small text-info"><?= htmlspecialchars((string) ($row['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="fw-semibold"><?= htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="small text-secondary"><?= htmlspecialchars((string) ($row['district'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($row['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <span class="badge text-bg-secondary"><?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="small text-secondary mt-1"><?= htmlspecialchars($typeLabels[$ot] ?? $ot, ENT_QUOTES, 'UTF-8') ?></div>
        </a>
    <?php endforeach; ?>
</div>

<?php
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$basePath = '/locacoes/painel-entregador';
$queryParams = array_filter(['status' => (string) ($status ?? '') !== 'all' ? (string) $status : null]);
include base_path('app/Views/partials/pagination.php');
?>

<script>
(function () {
    let since = <?= json_encode($pollTs, JSON_THROW_ON_ERROR) ?>;
    setInterval(function () {
        fetch('/locacoes/painel-entregador/poll?since=' + encodeURIComponent(since), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.changed && data.updated_at_max) {
                    window.location.reload();
                }
                if (data && data.updated_at_max) {
                    since = data.updated_at_max;
                }
            })
            .catch(function () {});
    }, 15000);
})();
</script>
