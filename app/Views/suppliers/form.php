<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $supplier */
/** @var array<string, string> $errors */
/** @var array<string, mixed> $old */

$mode = $mode ?? 'create';
$supplier = is_array($supplier ?? null) ? $supplier : null;
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$action = $isEdit ? '/cadastros/fornecedores/' . (int) ($supplier['id'] ?? 0) : '/cadastros/fornecedores';

$val = static function (string $key, string $default = '') use ($old, $supplier, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $supplier !== null && array_key_exists($key, $supplier) && $supplier[$key] !== null) {
        return (string) $supplier[$key];
    }

    return $default;
};
$pt = $val('person_type', $isEdit ? 'J' : 'J');
if ($old !== [] && isset($old['person_type'])) {
    $pt = (string) $old['person_type'];
}
$pt = $pt === 'F' ? 'F' : 'J';
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Cadastros</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar fornecedor' : 'Novo fornecedor' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/cadastros/fornecedores">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Identificação</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label" for="person_type">Tipo</label>
                <select class="form-select app-input" id="person_type" name="person_type">
                    <option value="F" <?= $pt === 'F' ? 'selected' : '' ?>>Pessoa física</option>
                    <option value="J" <?= $pt === 'J' ? 'selected' : '' ?>>Pessoa jurídica</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label lumis-label" for="name">Nome / Razão social <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="trade_name">Nome fantasia</label>
                <input type="text" class="form-control app-input" id="trade_name" name="trade_name" value="<?= htmlspecialchars($val('trade_name'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="document">CPF/CNPJ</label>
                <input type="text" class="form-control app-input" id="document" name="document" value="<?= htmlspecialchars($val('document'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="state_registration">Inscrição estadual</label>
                <input type="text" class="form-control app-input" id="state_registration" name="state_registration" value="<?= htmlspecialchars($val('state_registration'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="email">E-mail</label>
                <input type="email" class="form-control app-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($val('email'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="phone">Telefone</label>
                <input type="text" class="form-control app-input" id="phone" name="phone" value="<?= htmlspecialchars($val('phone'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="mobile">Celular</label>
                <input type="text" class="form-control app-input" id="mobile" name="mobile" value="<?= htmlspecialchars($val('mobile'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="contact_name">Contato principal</label>
                <input type="text" class="form-control app-input" id="contact_name" name="contact_name" value="<?= htmlspecialchars($val('contact_name'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="status">Status</label>
                <?php $st = (int) $val('status', '1'); ?>
                <select class="form-select app-input" id="status" name="status">
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Endereço</div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label lumis-label" for="cep">CEP</label>
                <input type="text" class="form-control app-input" id="cep" name="cep" value="<?= htmlspecialchars($val('cep'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="street">Logradouro</label>
                <input type="text" class="form-control app-input" id="street" name="street" value="<?= htmlspecialchars($val('street'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="address_number">Número</label>
                <input type="text" class="form-control app-input" id="address_number" name="address_number" value="<?= htmlspecialchars($val('address_number'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="complement">Complemento</label>
                <input type="text" class="form-control app-input" id="complement" name="complement" value="<?= htmlspecialchars($val('complement'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="district">Bairro</label>
                <input type="text" class="form-control app-input" id="district" name="district" value="<?= htmlspecialchars($val('district'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="city">Cidade</label>
                <input type="text" class="form-control app-input" id="city" name="city" value="<?= htmlspecialchars($val('city'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label lumis-label" for="state">UF</label>
                <input type="text" class="form-control app-input <?= isset($errors['state']) ? 'is-invalid' : '' ?>" id="state" name="state" maxlength="2" value="<?= htmlspecialchars($val('state'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['state'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['state'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Observações</div>
        <textarea class="form-control app-input" name="notes" rows="3"><?= htmlspecialchars($val('notes'), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="/cadastros/fornecedores">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Cadastrar' ?></button>
    </div>
</form>
