<div class="lumis-auth-card">
    <div class="lumis-auth-card__body">
        <div class="lumis-auth-card__head">
            <div class="lumis-auth-card__logo-row d-none d-lg-flex">
                <div class="brand-mark rounded-3"></div>
                <div>
                    <h2 class="lumis-auth-card__title mb-0">Recuperar acesso</h2>
                    <p class="lumis-auth-card__subtitle">Enviaremos instruções para o e-mail cadastrado</p>
                </div>
            </div>
            <div class="d-lg-none">
                <h2 class="lumis-auth-card__title">Recuperar acesso</h2>
                <p class="lumis-auth-card__subtitle">Instruções por e-mail</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="lumis-alert-auth lumis-alert-auth--error" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="lumis-alert-auth lumis-alert-auth--success" role="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/password/forgot" autocomplete="on">
            <?= $csrfField ?>

            <div class="lumis-field">
                <label for="forgot-email">E-mail</label>
                <input class="lumis-input" id="forgot-email" name="email" type="email" required autocomplete="email" placeholder="nome@empresa.com.br">
            </div>

            <button class="lumis-btn-submit" type="submit">Enviar instruções</button>

            <div class="lumis-auth-meta">
                <span style="color: var(--lumis-muted);">Lembrou a senha?</span>
                <a href="/login">Voltar ao login</a>
            </div>
        </form>
    </div>
</div>
