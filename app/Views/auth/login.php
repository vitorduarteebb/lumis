<div class="card app-card border-0 shadow-lg">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-2 mb-4">
            <div class="brand-mark rounded-2"></div>
            <div>
                <div class="fw-semibold text-white">Lumis ERP</div>
                <div class="text-secondary small">Entrar na conta</div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 small" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success border-0 small" role="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/login" class="vstack gap-3" autocomplete="on">
            <?= $csrfField ?>
            <div>
                <label class="form-label small text-secondary">E-mail</label>
                <input class="form-control form-control-lg app-input" name="email" type="email" required autocomplete="email" autofocus>
            </div>
            <div>
                <label class="form-label small text-secondary">Senha</label>
                <input class="form-control form-control-lg app-input" name="password" type="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-primary btn-lg w-100" type="submit">Entrar</button>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 text-secondary small">
                <span>Autenticação via MySQL.</span>
                <a class="text-decoration-none text-nowrap" href="/password/forgot">Esqueci minha senha</a>
            </div>
        </form>
    </div>
</div>
