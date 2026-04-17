<div class="text-center py-5">
    <div class="display-6 text-white mb-2"><?= (int) ($code ?? 403) ?></div>
    <p class="text-secondary mb-4"><?= htmlspecialchars((string) ($message ?? 'Acesso negado.'), ENT_QUOTES, 'UTF-8') ?></p>
    <a class="btn btn-primary" href="/dashboard">Ir ao painel</a>
</div>
