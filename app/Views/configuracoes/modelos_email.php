<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $templates */
$templates = is_array($templates ?? null) ? $templates : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Modelos de e-mails</h2>
        <div class="text-secondary small">Templates HTML para envios transacionais.</div>
    </div>
    <?php if (can('configuracoes.modelos_email.edit')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/configuracoes/modelos-email/novo">Novo modelo</a>
    <?php endif; ?>
</div>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Slug</th>
                <th>Assunto</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($templates === []): ?>
                <tr><td colspan="5" class="text-secondary small py-4">Nenhum modelo.</td></tr>
            <?php endif; ?>
            <?php foreach ($templates as $t): ?>
                <?php
                $tid = (int) ($t['id'] ?? 0);
                $ok = (int) ($t['status'] ?? 0) === 1;
                ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($t['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($t['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($t['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $ok ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                    <td class="text-end">
                        <?php if (can('configuracoes.modelos_email.edit')): ?>
                            <a class="btn btn-sm btn-lumis-secondary rounded-3" href="/configuracoes/modelos-email/<?= $tid ?>/editar">Editar</a>
                            <?php if ($ok): ?>
                                <form method="post" action="/configuracoes/modelos-email/<?= $tid ?>/excluir" class="d-inline" onsubmit="return confirm('Inativar este modelo?');">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Inativar</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
