<?php

declare(strict_types=1);

/**
 * Esqueleto reutilizável para páginas de listagem (copiar/adaptar nas views dos módulos).
 *
 * Variáveis esperadas (exemplo):
 * - $pageTitle, $breadcrumbs
 * - $toolbarPrimaryLabel, $toolbarPrimaryHref (opcional)
 */
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Módulo</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($pageTitle ?? 'Listagem'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">Descrição curta da tela.</div>
    </div>
</div>

<div class="lumis-toolbar">
    <div class="lumis-toolbar__left">
        <div class="input-group lumis-search">
            <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" class="form-control app-input" placeholder="Buscar…" disabled>
        </div>
        <button type="button" class="btn btn-sm btn-lumis-secondary" disabled>Filtros</button>
    </div>
    <div class="lumis-toolbar__right">
        <button type="button" class="btn btn-sm btn-lumis-secondary" disabled>Exportar</button>
        <a class="btn btn-sm btn-primary" href="#">Novo registro</a>
    </div>
</div>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>0001</td>
                <td>Exemplo A</td>
                <td><span class="badge badge-lumis badge-lumis-success">Ativo</span></td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-lumis-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Ações</button>
                        <ul class="dropdown-menu dropdown-menu-end app-dropdown border-0">
                            <li><a class="dropdown-item" href="#">Editar</a></li>
                            <li><a class="dropdown-item text-danger" href="#">Excluir</a></li>
                        </ul>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 text-secondary small">
    <div>Mostrando <span class="text-white">1</span>–<span class="text-white">1</span> de <span class="text-white">1</span></div>
    <nav aria-label="Paginação">
        <ul class="pagination lumis-pagination pagination-sm mb-0">
            <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            <li class="page-item active"><span class="page-link">1</span></li>
            <li class="page-item disabled"><span class="page-link">Próximo</span></li>
        </ul>
    </nav>
</div>
