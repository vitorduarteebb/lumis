<?php

declare(strict_types=1);

/**
 * Padrão de modal (Bootstrap 5). Inclua o markup onde precisar e dispare via data-bs-toggle / JS.
 */
?>

<button type="button" class="btn btn-sm btn-lumis-secondary" data-bs-toggle="modal" data-bs-target="#lumisModalExample">
    Abrir modal de exemplo
</button>

<div class="modal fade" id="lumisModalExample" tabindex="-1" aria-labelledby="lumisModalExampleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content lumis-modal">
            <div class="modal-header">
                <h2 class="modal-title fs-6 mb-0" id="lumisModalExampleLabel">Confirmação</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-secondary small">
                Texto do modal. Use para confirmações, detalhes rápidos e fluxos de 1 etapa.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lumis-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>
