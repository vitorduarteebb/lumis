<?php

declare(strict_types=1);

use App\Helpers\Session;

$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
$flashWarning = Session::getFlash('warning');
$flashInfo = Session::getFlash('info');
?>

<?php if ($flashSuccess !== null && $flashSuccess !== ''): ?>
    <div class="lumis-alert lumis-alert--success alert d-flex align-items-start gap-2 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle-fill mt-1 flex-shrink-0" aria-hidden="true"></i>
        <div class="small"><?= htmlspecialchars((string) $flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
<?php endif; ?>

<?php if ($flashError !== null && $flashError !== ''): ?>
    <div class="lumis-alert lumis-alert--danger alert d-flex align-items-start gap-2 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-exclamation-octagon-fill mt-1 flex-shrink-0" aria-hidden="true"></i>
        <div class="small"><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
<?php endif; ?>

<?php if ($flashWarning !== null && $flashWarning !== ''): ?>
    <div class="lumis-alert lumis-alert--warning alert d-flex align-items-start gap-2 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0" aria-hidden="true"></i>
        <div class="small"><?= htmlspecialchars((string) $flashWarning, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
<?php endif; ?>

<?php if ($flashInfo !== null && $flashInfo !== ''): ?>
    <div class="lumis-alert lumis-alert--info alert d-flex align-items-start gap-2 border-0 shadow-sm mb-3" role="alert">
        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0" aria-hidden="true"></i>
        <div class="small"><?= htmlspecialchars((string) $flashInfo, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
<?php endif; ?>
