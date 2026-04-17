<div class="card app-card border-0 shadow-lg">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-2 mb-4">
            <div class="brand-mark rounded-2"></div>
            <div>
                <div class="fw-semibold text-white">Lumis ERP</div>
                <div class="text-secondary small">Nova senha</div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 small" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success border-0 small" role="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/password/reset" class="vstack gap-3" autocomplete="on">
            <?= $csrfField ?>
            <input type="hidden" name="email" value="<?= htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars((string) $token, ENT_QUOTES, 'UTF-8') ?>">

            <div>
                <label class="form-label small text-secondary">Nova senha</label>
                <input class="form-control form-control-lg app-input" name="password" type="password" required minlength="6" autocomplete="new-password">
                <div class="form-text text-secondary small">Mínimo 6 caracteres.</div>
            </div>
            <div>
                <label class="form-label small text-secondary">Confirmar senha</label>
                <input class="form-control form-control-lg app-input" name="password_confirmation" type="password" required minlength="6" autocomplete="new-password">
            </div>

            <button class="btn btn-primary btn-lg w-100" type="submit">Salvar nova senha</button>
            <div class="text-secondary small">
                <a class="text-decoration-none" href="/login">Voltar ao login</a>
            </div>
        </form>
    </div>
</div>
