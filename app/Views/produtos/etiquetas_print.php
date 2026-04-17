<?php
declare(strict_types=1);

/** @var list<array{product: array<string, mixed>, qty: int}> $lines */
$lines = is_array($lines ?? null) ? $lines : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Etiquetas — Lumis</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; padding: 12px; background: #fff; color: #111; }
        .sheet { display: flex; flex-wrap: wrap; gap: 8px; align-content: flex-start; }
        .tag {
            width: 48mm; min-height: 30mm;
            border: 1px dashed #333;
            padding: 6px 8px;
            display: flex; flex-direction: column; justify-content: space-between;
            page-break-inside: avoid;
            font-size: 10px;
        }
        .tag__name { font-weight: 600; font-size: 11px; line-height: 1.2; }
        .tag__sku { color: #444; margin-top: 4px; }
        .tag__price { font-size: 12px; font-weight: 700; margin-top: 6px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<p class="no-print" style="margin-bottom:12px;">
    <button type="button" onclick="window.print()">Imprimir</button>
    <a href="/produtos/etiquetas">Voltar</a>
</p>
<div class="sheet">
    <?php foreach ($lines as $line): ?>
        <?php
        $p = $line['product'];
        $q = max(1, (int) ($line['qty'] ?? 1));
        $name = (string) ($p['name'] ?? '');
        $sku = (string) ($p['sku'] ?? '');
        $barcode = (string) ($p['barcode'] ?? '');
        $price = lumis_money_br((float) ($p['sale_price'] ?? 0));
        ?>
        <?php for ($i = 0; $i < $q; $i++): ?>
            <div class="tag">
                <div>
                    <div class="tag__name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="tag__sku">SKU <?= htmlspecialchars($sku, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if ($barcode !== ''): ?>
                        <div class="tag__sku">EAN <?= htmlspecialchars($barcode, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <div class="tag__price"><?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endfor; ?>
    <?php endforeach; ?>
</div>
</body>
</html>
