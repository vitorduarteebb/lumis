<?php
/** @var array<string, mixed> $row */
/** @var list<array<string, mixed>> $items */
$code = htmlspecialchars((string) ($row['code'] ?? ''), ENT_QUOTES, 'UTF-8');
$client = htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$tot = lumis_money_br((float) ($total ?? 0));
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>O.S. <?= $code ?></title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#222;}
h1{font-size:16px;margin:0 0 8px;}
table{width:100%;border-collapse:collapse;margin-top:12px;}
th,td{border:1px solid #ccc;padding:6px;}
th{background:#f0f0f0;}
.right{text-align:right;}
</style>
</head>
<body>
<h1>Ordem de serviço</h1>
<p><strong>Código:</strong> <?= $code ?> &nbsp; <strong>Cliente:</strong> <?= $client ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
<?php if (!empty($row['description'])): ?><p><?= nl2br(htmlspecialchars((string) $row['description'], ENT_QUOTES, 'UTF-8')) ?></p><?php endif; ?>
<table>
<thead><tr><th>Item</th><th class="right">Qtd</th><th class="right">Subtotal</th></tr></thead>
<tbody>
<?php foreach ($items as $it): ?>
<?php
$isP = $it['product_id'] !== null && (int) $it['product_id'] > 0;
$nm = $isP ? ($it['product_name'] ?? '') : ($it['service_name'] ?? '');
?>
<tr>
<td><?= htmlspecialchars((string) $nm, ENT_QUOTES, 'UTF-8') ?></td>
<td class="right"><?= htmlspecialchars((string) ($it['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
<td class="right"><?= htmlspecialchars(lumis_money_br((float) ($it['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p style="margin-top:12px;"><strong>Total:</strong> <?= htmlspecialchars($tot, ENT_QUOTES, 'UTF-8') ?></p>
</body>
</html>
