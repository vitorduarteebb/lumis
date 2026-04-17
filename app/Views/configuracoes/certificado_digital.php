<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $certificates */
$rows = is_array($certificates ?? null) ? $certificates : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Certificado digital</h2>
        <div class="text-secondary small">Registro administrativo do certificado A1 (arquivo) para emissão fiscal futura.</div>
    </div>
</div>

<?php if (can('configuracoes.certificado_digital.edit')): ?>
    <div class="lumis-form-section mb-4">
        <div class="lumis-form-section__title">Novo registro</div>
        <form method="post" action="/configuracoes/certificado-digital" enctype="multipart/form-data" class="row g-2 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="col-md-4">
                <label class="form-label lumis-label small mb-1" for="label">Rótulo</label>
                <input type="text" class="form-control app-input" id="label" name="label" required placeholder="Ex.: Matriz 2026">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label small mb-1" for="expires_at">Validade</label>
                <input type="date" class="form-control app-input" id="expires_at" name="expires_at">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label small mb-1" for="cert_file">Arquivo (.pfx / .p12)</label>
                <input type="file" class="form-control app-input" id="cert_file" name="cert_file">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100">Salvar</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Rótulo</th>
                <th>Validade</th>
                <th>Status</th>
                <th>Arquivo</th>
                <?php if (can('configuracoes.certificado_digital.edit')): ?><th class="text-end">Ações</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="5" class="text-secondary small py-4">Nenhum certificado registrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($r['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($r['expires_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string) ($r['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="text-secondary small"><?= ($r['file_path'] ?? null) ? 'Sim' : 'Não' ?></td>
                    <?php if (can('configuracoes.certificado_digital.edit')): ?>
                        <td class="text-end">
                            <form method="post" action="/configuracoes/certificado-digital/excluir" class="d-inline" onsubmit="return confirm('Remover este registro?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int) ($r['id'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Excluir</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
