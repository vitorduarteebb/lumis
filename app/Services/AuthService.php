<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Helpers\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly PermissionRepository $permissions = new PermissionRepository(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    /**
     * @return null|string null = sucesso; string = mensagem de erro amigável
     */
    public function login(string $email, string $password, Request $request): ?string
    {
        $email = trim($email);
        $email = preg_replace('/[\x00-\x1F\x7F]/u', '', $email) ?? $email;
        $email = function_exists('mb_strtolower') ? mb_strtolower($email, 'UTF-8') : strtolower($email);
        $ip = (string) ($request->server('REMOTE_ADDR') ?? '');
        $ua = (string) ($request->server('HTTP_USER_AGENT') ?? '');

        $user = $this->users->findByEmail($email);
        if ($user === null) {
            $this->auditSafe(null, 'auth.login_failed', 'auth', 'E-mail não encontrado.', $ip, $ua);
            return 'Credenciais inválidas.';
        }

        $userId = (int) $user['id'];
        $status = (int) ($user['status'] ?? 0);
        if ($status !== 1) {
            $this->auditSafe($userId, 'auth.login_blocked', 'auth', 'Tentativa de login em conta inativa.', $ip, $ua);
            return 'Conta inativa. Contate o administrador.';
        }

        if (!password_verify($password, (string) $user['password'])) {
            $this->auditSafe($userId, 'auth.login_failed', 'auth', 'Senha incorreta.', $ip, $ua);
            return 'Credenciais inválidas.';
        }

        Session::regenerate();

        $permSlugs = $this->permissions->getSlugsByUserId($userId);
        $roleSlugs = $this->permissions->getRoleSlugsByUserId($userId);

        Session::put('user_id', $userId);
        Session::put('user_name', (string) $user['name']);
        Session::put('user_email', (string) $user['email']);
        Session::put('company_id', $user['company_id'] !== null ? (int) $user['company_id'] : null);
        Session::put('store_id', $user['store_id'] !== null ? (int) $user['store_id'] : null);
        Session::put('permissions', $permSlugs);
        Session::put('role_slugs', $roleSlugs);

        $this->users->updateLastLogin($userId);
        $this->auditSafe($userId, 'auth.login_success', 'auth', 'Login realizado com sucesso.', $ip, $ua);

        return null;
    }

    /**
     * Auditoria não deve impedir login se a tabela estiver indisponível.
     */
    private function auditSafe(
        ?int $userId,
        string $action,
        string $module,
        ?string $description,
        string $ip,
        string $ua
    ): void {
        try {
            $this->audit->log($userId, $action, $module, $description, $ip, $ua);
        } catch (\Throwable $e) {
            error_log('[Lumis] audit log falhou: ' . $e->getMessage());
        }
    }

    public function logout(Request $request): void
    {
        $uid = Session::get('user_id');
        $userId = is_numeric($uid) ? (int) $uid : null;
        $ip = (string) ($request->server('REMOTE_ADDR') ?? '');
        $ua = (string) ($request->server('HTTP_USER_AGENT') ?? '');

        if ($userId !== null) {
            $this->audit->log($userId, 'auth.logout', 'auth', 'Logout realizado.', $ip, $ua);
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
    }
}
