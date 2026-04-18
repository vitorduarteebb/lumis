<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class EmailNotificationRepository extends BaseRepository
{
    /** @var array<string, string> */
    public const EVENT_LABELS = [
        'sale_created' => 'Nova venda registrada',
        'payment_due' => 'Conta a pagar/receber próxima do vencimento',
        'stock_low' => 'Produto abaixo do estoque mínimo',
        'ticket_open' => 'Novo chamado de atendimento',
        'user_invited' => 'Novo usuário / convite',
        'password_reset' => 'Recuperação de senha',
        'quote_approved' => 'Orçamento aprovado',
        'contract_expiring' => 'Contrato próximo do vencimento',
    ];

    /**
     * @return array<string, int> event_key => 0|1
     */
    public function mapForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT event_key, enabled FROM email_notification_settings WHERE company_id = :cid'
        );
        $stmt->execute(['cid' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['event_key']] = (int) $r['enabled'];
        }
        foreach (array_keys(self::EVENT_LABELS) as $key) {
            if (!array_key_exists($key, $map)) {
                $map[$key] = 1;
            }
        }

        return $map;
    }

    /**
     * @return array<string, int|null> event_key => template_id ou null
     */
    public function templateMapForCompany(int $companyId): array
    {
        if (!$this->columnExists('email_notification_settings', 'template_id')) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT event_key, template_id FROM email_notification_settings WHERE company_id = :cid'
        );
        $stmt->execute(['cid' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) {
            $tid = $r['template_id'] ?? null;
            $map[(string) $r['event_key']] = $tid !== null && $tid !== '' ? (int) $tid : null;
        }

        return $map;
    }

    public function upsert(int $companyId, string $eventKey, int $enabled, ?int $templateId = null): void
    {
        if ($this->columnExists('email_notification_settings', 'template_id')) {
            $tid = $templateId !== null && $templateId > 0 ? $templateId : null;
            $stmt = $this->pdo()->prepare(
                'INSERT INTO email_notification_settings (company_id, event_key, template_id, enabled, created_at, updated_at)
                 VALUES (:cid, :ek, :tid, :en, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), template_id = VALUES(template_id), updated_at = NOW()'
            );
            $stmt->execute(['cid' => $companyId, 'ek' => $eventKey, 'tid' => $tid, 'en' => $enabled]);

            return;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO email_notification_settings (company_id, event_key, enabled, created_at, updated_at)
             VALUES (:cid, :ek, :en, NOW(), NOW())
             ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), updated_at = NOW()'
        );
        $stmt->execute(['cid' => $companyId, 'ek' => $eventKey, 'en' => $enabled]);
    }
}
