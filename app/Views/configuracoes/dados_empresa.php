<?php
declare(strict_types=1);

/** @var array<string, mixed> $profile */
$p = is_array($profile ?? null) ? $profile : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Dados da empresa</h2>
        <div class="text-secondary small">Informações legais e endereço para documentos.</div>
    </div>
</div>

<form class="lumis-form" method="post" action="/configuracoes/dados-empresa">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Identificação</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label" for="legal_name">Razão social</label>
                <input type="text" class="form-control app-input" id="legal_name" name="legal_name" value="<?= htmlspecialchars((string) ($p['legal_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="trade_name">Nome fantasia</label>
                <input type="text" class="form-control app-input" id="trade_name" name="trade_name" value="<?= htmlspecialchars((string) ($p['trade_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="document_cnpj">CNPJ</label>
                <input type="text" class="form-control app-input" id="document_cnpj" name="document_cnpj" value="<?= htmlspecialchars((string) ($p['document_cnpj'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="state_registration">Inscrição estadual</label>
                <input type="text" class="form-control app-input" id="state_registration" name="state_registration" value="<?= htmlspecialchars((string) ($p['state_registration'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="municipal_registration">Inscrição municipal</label>
                <input type="text" class="form-control app-input" id="municipal_registration" name="municipal_registration" value="<?= htmlspecialchars((string) ($p['municipal_registration'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
    </div>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Contato</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label" for="email">E-mail</label>
                <input type="email" class="form-control app-input" id="email" name="email" value="<?= htmlspecialchars((string) ($p['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="phone">Telefone</label>
                <input type="text" class="form-control app-input" id="phone" name="phone" value="<?= htmlspecialchars((string) ($p['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="mobile">Celular</label>
                <input type="text" class="form-control app-input" id="mobile" name="mobile" value="<?= htmlspecialchars((string) ($p['mobile'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="website">Site</label>
                <input type="url" class="form-control app-input" id="website" name="website" value="<?= htmlspecialchars((string) ($p['website'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://">
            </div>
        </div>
    </div>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Endereço</div>
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label lumis-label" for="cep">CEP</label>
                <input type="text" class="form-control app-input" id="cep" name="cep" value="<?= htmlspecialchars((string) ($p['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="street">Logradouro</label>
                <input type="text" class="form-control app-input" id="street" name="street" value="<?= htmlspecialchars((string) ($p['street'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="address_number">Número</label>
                <input type="text" class="form-control app-input" id="address_number" name="address_number" value="<?= htmlspecialchars((string) ($p['address_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="complement">Complemento</label>
                <input type="text" class="form-control app-input" id="complement" name="complement" value="<?= htmlspecialchars((string) ($p['complement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="district">Bairro</label>
                <input type="text" class="form-control app-input" id="district" name="district" value="<?= htmlspecialchars((string) ($p['district'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="city">Cidade</label>
                <input type="text" class="form-control app-input" id="city" name="city" value="<?= htmlspecialchars((string) ($p['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label" for="state">UF</label>
                <input type="text" maxlength="2" class="form-control app-input" id="state" name="state" value="<?= htmlspecialchars((string) ($p['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
    </div>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Observações</div>
        <textarea class="form-control app-input" name="notes" rows="3"><?= htmlspecialchars((string) ($p['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
    <?php if (can('configuracoes.dados_empresa.edit')): ?>
        <button type="submit" class="btn btn-primary rounded-3">Salvar</button>
    <?php endif; ?>
</form>
