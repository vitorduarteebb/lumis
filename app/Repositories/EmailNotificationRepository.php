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

    public function upsert(int $companyId, string $eventKey, int $enabled): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO email_notification_settings (company_id, event_key, enabled, created_at, updated_at)
             VALUES (:cid, :ek, :en, NOW(), NOW())
             ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), updated_at = NOW()'
        );
        $stmt->execute(['cid' => $companyId, 'ek' => $eventKey, 'en' => $enabled]);
    }
}
