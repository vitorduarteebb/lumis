<?php

declare(strict_types=1);

/**
 * Esqueleto reutilizável para formulários (copiar/adaptar nas views dos módulos).
 */
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Módulo</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($pageTitle ?? 'Formulário'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">Campos agrupados por seções.</div>
    </div>
</div>

<form class="lumis-form" method="post" action="#">
    <?= \App\Helpers\Csrf::field() ?>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Identificação</div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label lumis-label">Nome</label>
                <input type="text" class="form-control app-input" name="name" autocomplete="off" disabled>
                <div class="lumis-help mt-1">Texto de ajuda discreto.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Status</label>
                <select class="form-select app-input" name="status" disabled>
                    <option>Ativo</option>
                    <option>Inativo</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea class="form-control app-input" rows="3" name="notes" disabled></textarea>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Anexos</div>
        <div class="text-secondary small">Área preparada para upload (Fase futura).</div>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="#">Cancelar</a>
        <button type="button" class="btn btn-lumis-danger" disabled>Excluir</button>
        <button type="submit" class="btn btn-primary" disabled>Salvar</button>
    </div>
</form>
