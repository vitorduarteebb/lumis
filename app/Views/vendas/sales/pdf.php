<?php
/** @var array<string, mixed> $row */
/** @var list<array<string, mixed>> $items */
/** @var string $kind */
$kind = $kind ?? 'product';
$num = htmlspecialchars((string) ($row['document_number'] ?? ''), ENT_QUOTES, 'UTF-8');
$client = htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$notes = htmlspecialchars((string) ($row['notes'] ?? ''), ENT_QUOTES, 'UTF-8');
$sub = lumis_money_br((float) ($row['subtotal_amount'] ?? 0));
$disc = lumis_money_br((float) ($row['discount_total'] ?? 0));
$tot = lumis_money_br((float) ($row['total_amount'] ?? 0));
$tit = $kind === 'service' ? 'Venda de serviços' : 'Venda de produtos';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title><?= $num ?></title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#222;}
h1{font-size:16px;margin:0 0 8px;}
table{width:100%;border-collapse:collapse;margin-top:12px;}
th,td{border:1px solid #ccc;padding:6px;text-align:left;}
th{background:#f0f0f0;}
.right{text-align:right;}
</style>
</head>
<body>
<h1><?= $tit ?></h1>
<p><strong>Documento:</strong> <?= $num ?> &nbsp; <strong>Cliente:</strong> <?= $client ?></p>
<p><strong>Subtotal:</strong> <?= $sub ?> &nbsp; <strong>Desconto:</strong> <?= $disc ?> &nbsp; <strong>Total:</strong> <?= $tot ?></p>
<?php if ($notes !== ''): ?><p><?= nl2br($notes) ?></p><?php endif; ?>
<table>
<thead><tr><th>Item</th><th class="right">Qtd</th><th class="right">V.unit</th><th class="right">Desc.</th><th class="right">Subtotal</th></tr></thead>
<tbody>
<?php foreach ($items as $it): ?>
<?php
$nm = $kind === 'service' ? ($it['service_name'] ?? '') : ($it['product_name'] ?? '');
?>
<tr>
<td><?= htmlspecialchars((string) $nm, ENT_QUOTES, 'UTF-8') ?></td>
<td class="right"><?= htmlspecialchars((string) ($it['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
<td class="right"><?= htmlspecialchars(lumis_money_br((float) ($it['unit_price'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
<td class="right"><?= htmlspecialchars(lumis_money_br((float) ($it['line_discount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
<td class="right"><?= htmlspecialchars(lumis_money_br((float) ($it['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</body>
</html>
