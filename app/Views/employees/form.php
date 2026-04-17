<?php
$mode = $mode ?? 'create';
$employee = is_array($employee ?? null) ? $employee : null;
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$action = $isEdit ? '/cadastros/funcionarios/' . (int) ($employee['id'] ?? 0) : '/cadastros/funcionarios';
$val = static function (string $key, string $default = '') use ($old, $employee, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $employee !== null && isset($employee[$key]) && $employee[$key] !== null) {
        return (string) $employee[$key];
    }
    return $default;
};
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Cadastros</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar funcionário' : 'Novo funcionário' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/cadastros/funcionarios">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Dados</div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label lumis-label" for="name">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="document">CPF / Documento</label>
                <input type="text" class="form-control app-input" id="document" name="document" value="<?= htmlspecialchars($val('document'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="job_title">Cargo</label>
                <input type="text" class="form-control app-input" id="job_title" name="job_title" value="<?= htmlspecialchars($val('job_title'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="hire_date">Admissão</label>
                <input type="date" class="form-control app-input" id="hire_date" name="hire_date" value="<?= htmlspecialchars($val('hire_date'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="status">Status</label>
                <?php $st = (int) $val('status', '1'); ?>
                <select class="form-select app-input" id="status" name="status">
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="email">E-mail</label>
                <input type="email" class="form-control app-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($val('email'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="phone">Telefone</label>
                <input type="text" class="form-control app-input" id="phone" name="phone" value="<?= htmlspecialchars($val('phone'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label" for="notes">Observações</label>
                <textarea class="form-control app-input" id="notes" name="notes" rows="3"><?= htmlspecialchars($val('notes'), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="/cadastros/funcionarios">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Cadastrar' ?></button>
    </div>
</form>
