<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Repositories\DigitalCertificateRepository;

/**
 * Resolve certificado A1 da empresa para assinatura (caminho + senha em claro na memória).
 */
final class CertificateService
{
    public function __construct(
        private readonly DigitalCertificateRepository $certs = new DigitalCertificateRepository()
    ) {
    }

    /**
     * @return array{pfx_path: string, password: string, expires_at: ?string}|null
     */
    public function loadCertificateForEmission(int $companyId, int $certificateId): ?array
    {
        $row = $this->certs->findByIdForCompany($certificateId, $companyId);
        if ($row === null || empty($row['file_path'])) {
            return null;
        }
        $path = $this->resolvePath((string) $row['file_path']);
        if (!is_readable($path)) {
            return null;
        }
        $pw = '';
        if (!empty($row['password_encrypted'])) {
            $pw = lumis_decrypt_secret((string) $row['password_encrypted']);
        }

        return [
            'pfx_path' => $path,
            'password' => $pw,
            'expires_at' => $row['expires_at'] !== null ? (string) $row['expires_at'] : null,
        ];
    }

    /**
     * Valida existência do ficheiro (caminho guardado pode ser relativo).
     */
    private function resolvePath(string $stored): string
    {
        if ($stored !== '' && ($stored[0] === '/' || (strlen($stored) > 2 && $stored[1] === ':'))) {
            return $stored;
        }

        return base_path(ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $stored), DIRECTORY_SEPARATOR));
    }
}
