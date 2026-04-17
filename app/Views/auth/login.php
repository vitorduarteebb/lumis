<div class="lumis-auth-card">
    <div class="lumis-auth-card__body">
        <div class="lumis-auth-card__head">
            <div class="lumis-auth-card__logo-row d-none d-lg-flex">
                <div class="brand-mark rounded-3"></div>
                <div>
                    <h2 class="lumis-auth-card__title mb-0">Bem-vindo de volta</h2>
                    <p class="lumis-auth-card__subtitle">Entre com sua conta corporativa</p>
                </div>
            </div>
            <div class="d-lg-none">
                <h2 class="lumis-auth-card__title">Bem-vindo de volta</h2>
                <p class="lumis-auth-card__subtitle">Entre com sua conta corporativa</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="lumis-alert-auth lumis-alert-auth--error" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="lumis-alert-auth lumis-alert-auth--success" role="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/login" autocomplete="on">
            <?= $csrfField ?>

            <div class="lumis-field">
                <label for="login-email">E-mail corporativo</label>
                <input class="lumis-input" id="login-email" name="email" type="email" required autocomplete="email" placeholder="nome@empresa.com.br" autofocus>
            </div>
            <div class="lumis-field">
                <label for="login-password">Senha</label>
                <input class="lumis-input" id="login-password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••">
            </div>

            <button class="lumis-btn-submit" type="submit">Entrar na plataforma</button>

            <div class="lumis-auth-meta">
                <span style="color: var(--lumis-muted);">Autenticação segura via MySQL</span>
                <a href="/password/forgot">Esqueci minha senha</a>
            </div>
        </form>
    </div>
</div>
