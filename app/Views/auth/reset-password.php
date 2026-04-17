<div class="lumis-auth-card">
    <div class="lumis-auth-card__body">
        <div class="lumis-auth-card__head">
            <div class="lumis-auth-card__logo-row d-none d-lg-flex">
                <div class="brand-mark rounded-3"></div>
                <div>
                    <h2 class="lumis-auth-card__title mb-0">Nova senha</h2>
                    <p class="lumis-auth-card__subtitle">Defina uma senha forte para sua conta</p>
                </div>
            </div>
            <div class="d-lg-none">
                <h2 class="lumis-auth-card__title">Nova senha</h2>
                <p class="lumis-auth-card__subtitle">Conta protegida</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="lumis-alert-auth lumis-alert-auth--error" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="lumis-alert-auth lumis-alert-auth--success" role="alert"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/password/reset" autocomplete="on">
            <?= $csrfField ?>
            <input type="hidden" name="email" value="<?= htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars((string) $token, ENT_QUOTES, 'UTF-8') ?>">

            <div class="lumis-field">
                <label for="reset-password">Nova senha</label>
                <input class="lumis-input" id="reset-password" name="password" type="password" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="lumis-field">
                <label for="reset-password-2">Confirmar senha</label>
                <input class="lumis-input" id="reset-password-2" name="password_confirmation" type="password" required minlength="6" autocomplete="new-password" placeholder="Repita a senha">
            </div>

            <button class="lumis-btn-submit" type="submit">Salvar e entrar depois</button>

            <div class="lumis-auth-meta">
                <span style="color: var(--lumis-muted);">Token válido por tempo limitado</span>
                <a href="/login">Voltar ao login</a>
            </div>
        </form>
    </div>
</div>
