<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $user */
/** @var list<array<string, mixed>> $roles */
/** @var list<array<string, mixed>> $stores */
/** @var list<array<string, mixed>> $companies */
/** @var array<string, string> $errors */
/** @var array<string, mixed> $old */
/** @var list<int>|null $selectedRoleIds */

$mode = $mode ?? 'create';
$user = is_array($user ?? null) ? $user : null;
$roles = is_array($roles ?? null) ? $roles : [];
$stores = is_array($stores ?? null) ? $stores : [];
$companies = is_array($companies ?? null) ? $companies : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$selectedRoleIds = isset($selectedRoleIds) && is_array($selectedRoleIds) ? $selectedRoleIds : [];

$isEdit = $mode === 'edit';
$action = $isEdit ? '/configuracoes/usuarios/' . (int) ($user['id'] ?? 0) : '/configuracoes/usuarios';
$val = static function (string $key, string $default = '') use ($old, $user, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $user !== null && isset($user[$key])) {
        return (string) $user[$key];
    }

    return $default;
};
$selRoles = $old['role_ids'] ?? null;
if (is_array($selRoles)) {
    $selectedRoleIds = array_map('intval', $selRoles);
}
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h2>
        <div class="text-secondary small">Preencha os dados e associe papéis de acesso.</div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/configuracoes/usuarios">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Identificação</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label" for="name">Nome completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="email">E-mail <span class="text-danger">*</span></label>
                <input type="email" class="form-control app-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" required value="<?= htmlspecialchars($val('email'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="password">Senha <?= $isEdit ? '(deixe em branco para manter)' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" class="form-control app-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="status">Status</label>
                <select class="form-select app-input" id="status" name="status">
                    <?php $st = (int) $val('status', '1'); ?>
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Empresa e loja</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label lumis-label" for="company_id">Empresa</label>
                <select class="form-select app-input" id="company_id" name="company_id">
                    <?php $cidSel = (int) $val('company_id', (string) ($user['company_id'] ?? current_company_id() ?? '')); ?>
                    <?php foreach ($companies as $c): ?>
                        <?php $cid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $cid ?>" <?= $cid === $cidSel ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label" for="store_id">Loja</label>
                <select class="form-select app-input" id="store_id" name="store_id">
                    <option value="">—</option>
                    <?php $sidSel = $val('store_id', $user !== null && isset($user['store_id']) ? (string) $user['store_id'] : ''); ?>
                    <?php foreach ($stores as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= (string) $sid === $sidSel ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Operação</div>
        <div class="form-check">
            <?php
            $idd = 0;
            if (array_key_exists('is_delivery_driver', $old)) {
                $idd = (int) $old['is_delivery_driver'];
            } elseif ($isEdit && $user !== null && isset($user['is_delivery_driver'])) {
                $idd = (int) $user['is_delivery_driver'];
            }
            ?>
            <input class="form-check-input" type="checkbox" name="is_delivery_driver" value="1" id="is_delivery_driver" <?= $idd === 1 ? 'checked' : '' ?>>
            <label class="form-check-label small" for="is_delivery_driver">Atuar como entregador (locações / logística)</label>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Papéis</div>
        <div class="row g-2">
            <?php foreach ($roles as $r): ?>
                <?php
                $rid = (int) ($r['id'] ?? 0);
                $checked = in_array($rid, $selectedRoleIds, true);
                ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= $rid ?>" id="role_<?= $rid ?>" <?= $checked ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="role_<?= $rid ?>"><?= htmlspecialchars((string) ($r['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="/configuracoes/usuarios">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar alterações' : 'Cadastrar' ?></button>
    </div>
</form>
