<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

/**
 * Caminhos físicos para XML/PDF/eventos — fora de public/.
 */
final class FiscalDocumentStorageService
{
    /**
     * Diretório base storage/fiscal/{companyId}/...
     */
    public function companyRoot(int $companyId): string
    {
        $root = (string) config('fiscal.storage.root', 'storage/fiscal');

        return $this->absPath($root . '/' . $companyId);
    }

    /**
     * @param 'xml_unsigned'|'xml_signed'|'xml_authorized'|'pdf'|'events'|'nfse_payloads' $sub
     */
    public function subdir(int $companyId, string $sub, ?int $year = null, ?string $extra = null): string
    {
        $key = 'fiscal.storage.' . $sub;
        $rel = (string) config($key, $sub);
        $base = $this->companyRoot($companyId) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if ($year !== null) {
            $base .= DIRECTORY_SEPARATOR . $year;
        }
        if ($extra !== null && $extra !== '') {
            $base .= DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9._-]/', '_', $extra);
        }
        if (!is_dir($base)) {
            @mkdir($base, 0775, true);
        }

        return $base;
    }

    /**
     * Caminho relativo ao projeto (para gravar em BD).
     */
    public function relativeToBase(string $absolutePath): string
    {
        $base = rtrim(base_path(), DIRECTORY_SEPARATOR);
        $abs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        return ltrim(str_replace($base, '', $abs), DIRECTORY_SEPARATOR);
    }

    private function absPath(string $relativeOrAbsolute): string
    {
        if ($relativeOrAbsolute !== '' && ($relativeOrAbsolute[0] === '/' || (strlen($relativeOrAbsolute) > 2 && $relativeOrAbsolute[1] === ':'))) {
            return $relativeOrAbsolute;
        }

        return base_path(ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeOrAbsolute), DIRECTORY_SEPARATOR));
    }
}
