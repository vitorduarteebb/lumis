<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;

final class PasswordResetService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly PasswordResetRepository $resets = new PasswordResetRepository(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
        private readonly MailService $mail = new MailService(),
    ) {
    }

    /**
     * Solicita reset: sempre retorna void; não revela se o e-mail existe.
     * Se existir, grava token e dispara MailService (log hoje).
     */
    public function requestReset(string $email, ?string $ip, ?string $userAgent): void
    {
        $this->resets->deleteExpired();

        $email = trim($email);
        if ($email === '') {
            return;
        }

        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return;
        }

        $plain = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plain);

        $expires = (new \DateTimeImmutable('now'))->modify('+60 minutes');

        $dbEmail = (string) $user['email'];
        $this->resets->deleteByEmail($dbEmail);
        $this->resets->insert($dbEmail, $hash, $expires);

        $base = rtrim((string) (config('app.url') ?? ''), '/');
        $query = http_build_query([
            'email' => $dbEmail,
            'token' => $plain,
        ]);
        $url = $base . '/password/reset?' . $query;

        $subject = 'Redefinição de senha — Lumis ERP';
        $html = '<p>Recebemos uma solicitação para redefinir sua senha.</p>'
            . '<p><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">Redefinir senha</a></p>'
            . '<p>Se você não solicitou, ignore este e-mail.</p>';
        $text = "Redefinir senha: {$url}\n";

        $this->mail->sendPasswordReset($dbEmail, $subject, $html, $text);

        $this->audit->log(
            (int) $user['id'],
            'auth.password_reset_requested',
            'auth',
            'Token de redefinição gerado.',
            $ip,
            $userAgent
        );
    }

    /**
     * @return null|string null = sucesso
     */
    public function resetPassword(string $email, string $plainToken, string $newPassword): ?string
    {
        $this->resets->deleteExpired();

        $email = trim($email);
        if ($email === '' || $plainToken === '') {
            return 'Dados inválidos.';
        }

        $hash = hash('sha256', $plainToken);
        $row = $this->resets->findValidByEmailAndTokenHash($email, $hash);
        if ($row === null) {
            return 'Link inválido ou expirado. Solicite um novo.';
        }

        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return 'Usuário não encontrado.';
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->users->updatePasswordHash((int) $user['id'], $newHash);
        $this->resets->deleteByEmail($email);

        $this->audit->log(
            (int) $user['id'],
            'auth.password_reset_completed',
            'auth',
            'Senha redefinida com sucesso.',
            null,
            null
        );

        return null;
    }
}
