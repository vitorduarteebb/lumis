<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Visão geral</div>
        <h2 class="h4 mb-1 text-white">Olá, <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">Resumo operacional · dados ilustrativos até a Fase 4</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-lumis-secondary" disabled>Exportar</button>
        <button type="button" class="btn btn-sm btn-primary" disabled>Nova ação</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($kpis as $kpi): ?>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="lumis-kpi p-3">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="lumis-kpi__label text-uppercase"><?= htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="lumis-kpi__value text-white mt-1"><?= htmlspecialchars($kpi['value'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="lumis-kpi__hint"><?= htmlspecialchars($kpi['hint'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="lumis-kpi__icon flex-shrink-0">
                        <i class="bi <?= htmlspecialchars($kpi['icon'] ?? 'bi-activity', ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="lumis-card">
            <div class="lumis-card__head">
                <h3 class="lumis-card__title">Vendas (últimos 12 meses)</h3>
                <span class="badge badge-lumis badge-lumis-neutral">placeholder</span>
            </div>
            <div class="lumis-card__body">
                <div style="height: 280px;">
                    <canvas id="chartSales" aria-label="Gráfico de vendas"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="lumis-card mb-3">
            <div class="lumis-card__head">
                <h3 class="lumis-card__title">Fluxo de caixa</h3>
                <span class="badge badge-lumis badge-lumis-neutral">placeholder</span>
            </div>
            <div class="lumis-card__body">
                <div style="height: 220px;">
                    <canvas id="chartCashflow" aria-label="Gráfico de fluxo de caixa"></canvas>
                </div>
            </div>
        </div>
        <div class="lumis-card">
            <div class="lumis-card__head">
                <h3 class="lumis-card__title">Agenda</h3>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-lumis-secondary" disabled aria-label="Mês anterior"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="btn btn-sm btn-lumis-secondary" disabled aria-label="Próximo mês"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="lumis-card__body p-0">
                <?php
                $cal = $calendar ?? ['month' => '', 'year' => '', 'leadingBlanks' => 0, 'daysInMonth' => 30, 'today' => 1];
                $leading = (int) ($cal['leadingBlanks'] ?? 0);
                $dim = (int) ($cal['daysInMonth'] ?? 30);
                $today = (int) ($cal['today'] ?? 1);
                ?>
                <div class="lumis-cal">
                    <div class="lumis-cal__head">
                        <div class="fw-semibold text-white"><?= htmlspecialchars((string) ($cal['month'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <span class="text-secondary fw-normal"><?= htmlspecialchars((string) ($cal['year'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <span class="badge badge-lumis badge-lumis-neutral small">preview</span>
                    </div>
                    <div class="lumis-cal__grid">
                        <?php foreach (['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $dow): ?>
                            <div class="lumis-cal__dow"><?= htmlspecialchars($dow, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < $leading; $i++): ?>
                            <div class="lumis-cal__day lumis-cal__day--muted"></div>
                        <?php endfor; ?>
                        <?php for ($d = 1; $d <= $dim; $d++): ?>
                            <div class="lumis-cal__day <?= $d === $today ? 'lumis-cal__day--today' : '' ?>"><?= $d ?></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="lumis-card h-100">
            <div class="lumis-card__head">
                <h3 class="lumis-card__title">Atividades recentes</h3>
                <a class="small text-decoration-none" href="#">Ver tudo</a>
            </div>
            <div class="lumis-card__body">
                <div class="lumis-timeline">
                    <?php foreach ($activities as $row): ?>
                        <div class="lumis-timeline__item">
                            <div class="lumis-timeline__dot" aria-hidden="true"></div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi <?= htmlspecialchars((string) ($row['icon'] ?? 'bi-dot'), ENT_QUOTES, 'UTF-8') ?> text-secondary mt-1" aria-hidden="true"></i>
                                    <div class="min-w-0">
                                        <div class="text-white small fw-semibold"><?= htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-secondary" style="font-size: 0.8rem;"><?= htmlspecialchars((string) ($row['meta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="lumis-card mb-3">
            <div class="lumis-card__head">
                <h3 class="lumis-card__title">Alertas</h3>
            </div>
            <div class="lumis-card__body">
                <div class="vstack gap-2">
                    <?php foreach ($alerts as $a): ?>
                        <?php
                        $tone = (string) ($a['tone'] ?? 'info');
                        $cls = match ($tone) {
                            'warning' => 'lumis-alert--warning',
                            'danger' => 'lumis-alert--danger',
                            default => 'lumis-alert--info',
                        };
                        ?>
                        <div class="lumis-alert <?= $cls ?> p-3 rounded-3 border">
                            <div class="fw-semibold small"><?= htmlspecialchars((string) ($a['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="text-secondary small mt-1"><?= htmlspecialchars((string) ($a['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="lumis-card">
            <div class="lumis-card__head">
                <h3 class="lumis-card__title">Atalhos</h3>
            </div>
            <div class="lumis-card__body">
                <div class="row g-2">
                    <?php foreach ($shortcuts as $sc): ?>
                        <div class="col-12">
                            <a class="btn btn-lumis-secondary w-100 d-flex align-items-center justify-content-start gap-2 <?= !empty($sc['disabled']) ? 'disabled' : '' ?>" href="<?= htmlspecialchars((string) ($sc['href'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi <?= htmlspecialchars((string) ($sc['icon'] ?? 'bi-link-45deg'), ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                <span><?= htmlspecialchars((string) ($sc['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="lumis-empty mt-3">
            <div class="lumis-empty__icon"><i class="bi bi-inboxes" aria-hidden="true"></i></div>
            <div class="fw-semibold text-white">Nenhum widget extra</div>
            <div class="small">Este bloco demonstra <span class="text-white">empty state</span> para listagens futuras.</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
(() => {
  const sales = <?= json_encode($chartSales ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const cash = <?= json_encode($chartCashflow ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const months = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

  const commonOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#9aa7bd' }, grid: { color: 'rgba(255,255,255,0.06)' } },
      y: { ticks: { color: '#9aa7bd' }, grid: { color: 'rgba(255,255,255,0.06)' } },
    },
  };

  const el1 = document.getElementById('chartSales');
  const el2 = document.getElementById('chartCashflow');
  if (!window.Chart || !el1 || !el2) return;

  new Chart(el1, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        label: 'Vendas',
        data: sales,
        borderColor: 'rgba(61, 116, 255, 0.95)',
        backgroundColor: 'rgba(61, 116, 255, 0.12)',
        fill: true,
        tension: 0.35,
        borderWidth: 2,
        pointRadius: 0,
      }],
    },
    options: commonOpts,
  });

  new Chart(el2, {
    type: 'bar',
    data: {
      labels: months.slice(0, cash.length),
      datasets: [{
        label: 'Fluxo',
        data: cash,
        backgroundColor: 'rgba(201, 162, 77, 0.35)',
        borderColor: 'rgba(201, 162, 77, 0.65)',
        borderWidth: 1,
      }],
    },
    options: commonOpts,
  });
})();
</script>
