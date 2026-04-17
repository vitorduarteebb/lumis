<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Log;

/**
 * Envio de e-mail — hoje registra em log; PHPMailer pode ser integrado sem mudar a API pública.
 */
final class MailService
{
    public function sendPasswordReset(string $toEmail, string $subject, string $htmlBody, string $textBody = ''): void
    {
        $driver = (string) (config('mail.driver') ?? 'log');

        if ($driver === 'log' || $driver === '') {
            Log::instance()->notice('E-mail (fila/log): redefinição de senha', [
                'to' => $toEmail,
                'subject' => $subject,
                'body_text' => $textBody !== '' ? $textBody : strip_tags($htmlBody),
            ]);
            return;
        }

        // Futuro: PHPMailer com credenciais de config/mail.php
        Log::instance()->warning('Driver de e-mail não implementado; usando log.', ['driver' => $driver]);
        Log::instance()->notice('E-mail (fallback): redefinição de senha', [
            'to' => $toEmail,
            'subject' => $subject,
        ]);
    }
}
