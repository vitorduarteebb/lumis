<div class="card app-card border-0 shadow-lg">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-2 mb-4">
            <div class="brand-mark rounded-2"></div>
            <div>
                <div class="fw-semibold text-white">Lumis ERP</div>
                <div class="text-secondary small">Recuperar acesso</div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 small" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success border-0 small" role="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/password/forgot" class="vstack gap-3" autocomplete="on">
            <?= $csrfField ?>
            <div>
                <label class="form-label small text-secondary">E-mail</label>
                <input class="form-control form-control-lg app-input" name="email" type="email" required autocomplete="email">
            </div>
            <button class="btn btn-primary btn-lg w-100" type="submit">Enviar instruções</button>
            <div class="text-secondary small">
                <a class="text-decoration-none" href="/login">Voltar ao login</a>
            </div>
        </form>
    </div>
</div>
