<?php
$pageTitle = 'Analytics';
$currentPage = 'analytics';
require __DIR__ . '/../layout/header.php';

$summary  = $data['summary'];
$daily    = $data['daily'];
$byType   = $data['by_type'];
$byArea   = $data['by_area'];
$topFailed = $data['top_failed'];

$total       = (int)($summary['total'] ?? 0);
$totalPass   = (int)($summary['total_pass'] ?? 0);
$totalFail   = (int)($summary['total_fail'] ?? 0);
$activeCP    = (int)($summary['active_checkpoints'] ?? 0);
$passRate    = $total > 0 ? round($totalPass / $total * 100, 1) : 0;
?>

<style>
.analytics-summary {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.analytics-stat {
    background: #fff;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 1.25rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.analytics-stat .as-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
    color: var(--gray-900);
}
.analytics-stat .as-label {
    font-size: 0.8125rem;
    color: var(--gray-500);
}
.analytics-stat.as-pass  { border-top: 3px solid var(--success); }
.analytics-stat.as-fail  { border-top: 3px solid var(--danger); }
.analytics-stat.as-rate  { border-top: 3px solid var(--primary); }
.analytics-stat.as-total { border-top: 3px solid var(--gray-400); }

.analytics-section {
    margin-bottom: 1.5rem;
}
.analytics-section h2 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--gray-700);
}

/* Bar chart rows */
.bar-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}
.bar-label {
    min-width: 160px;
    max-width: 160px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--gray-700);
}
.bar-track {
    flex: 1;
    height: 12px;
    background: var(--gray-100);
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}
.bar-fill-pass {
    height: 100%;
    border-radius: 6px;
    background: var(--success);
    transition: width 0.3s ease;
}
.bar-fill-fail {
    height: 100%;
    border-radius: 6px;
    background: var(--danger);
    transition: width 0.3s ease;
}
.bar-meta {
    min-width: 90px;
    text-align: right;
    color: var(--gray-500);
    font-size: 0.75rem;
}

/* Activity chart canvas */
#activityChart {
    width: 100%;
    height: 160px;
    display: block;
}

/* Filters bar */
.analytics-filters {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: flex-end;
    margin-bottom: 1.5rem;
}
.analytics-filters .form-group {
    margin: 0;
}
.analytics-filters label {
    display: block;
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-bottom: 2px;
}

/* Dark mode */
.dark-mode .analytics-stat { background: #1e293b; border-color: #334155; }
.dark-mode .analytics-stat .as-value { color: #f1f5f9; }
.dark-mode .analytics-stat .as-label { color: #64748b; }
.dark-mode .analytics-section h2 { color: #94a3b8; }
.dark-mode .bar-label { color: #cbd5e1; }
.dark-mode .bar-track { background: #334155; }
.dark-mode .bar-meta  { color: #64748b; }
</style>

<div class="page-header">
    <h1>Analytics</h1>
    <a href="/checks" class="btn btn-secondary btn-sm">Check Logs</a>
</div>

<!-- Filters -->
<form method="GET" action="/analytics">
    <div class="analytics-filters">
        <div class="form-group">
            <label>Time Range</label>
            <select name="range" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="7"  <?= $range === '7'   ? 'selected' : '' ?>>Last 7 days</option>
                <option value="30" <?= ($range === '30' || $range === null) ? 'selected' : '' ?>>Last 30 days</option>
                <option value="90" <?= $range === '90'  ? 'selected' : '' ?>>Last 90 days</option>
                <option value="all" <?= $range === 'all' ? 'selected' : '' ?>>All time</option>
            </select>
        </div>
        <div class="form-group">
            <label>Venue</label>
            <select name="venue_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">All Venues</option>
                <?php foreach ($venues as $v): ?>
                <option value="<?= $v['id'] ?>" <?= ($venueId ?? '') == $v['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<!-- Summary stats -->
<div class="analytics-summary">
    <div class="analytics-stat as-total">
        <span class="as-value"><?= number_format($total) ?></span>
        <span class="as-label">Total Checks</span>
    </div>
    <div class="analytics-stat as-pass">
        <span class="as-value"><?= number_format($totalPass) ?></span>
        <span class="as-label">Passed</span>
    </div>
    <div class="analytics-stat as-fail">
        <span class="as-value"><?= number_format($totalFail) ?></span>
        <span class="as-label">Failed</span>
    </div>
    <div class="analytics-stat as-rate">
        <span class="as-value"><?= $passRate ?>%</span>
        <span class="as-label">Pass Rate</span>
    </div>
    <div class="analytics-stat as-total">
        <span class="as-value"><?= number_format($activeCP) ?></span>
        <span class="as-label">Active Check Points</span>
    </div>
</div>

<!-- Daily Activity Chart -->
<div class="card analytics-section">
    <div class="card-header">Daily Activity</div>
    <div class="card-body" style="padding-bottom: 0.5rem;">
        <?php if (empty($daily)): ?>
            <p class="text-muted" style="text-align:center;padding:1rem 0;">No data for selected period.</p>
        <?php else: ?>
        <canvas id="activityChart"></canvas>
        <script>
        (function() {
            const raw = <?= json_encode($daily) ?>;
            const canvas = document.getElementById('activityChart');
            if (!canvas) return;

            // Fill in missing days in the range
            const days = [];
            if (raw.length > 1) {
                const start = new Date(raw[0].day);
                const end   = new Date(raw[raw.length - 1].day);
                const map   = {};
                raw.forEach(r => { map[r.day] = r; });
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    const key = d.toISOString().slice(0, 10);
                    days.push(map[key] || { day: key, pass_count: 0, fail_count: 0 });
                }
            } else {
                raw.forEach(r => days.push(r));
            }

            const maxVal = Math.max(1, ...days.map(d => +d.pass_count + +d.fail_count));
            const dpr    = window.devicePixelRatio || 1;
            const W      = canvas.offsetWidth;
            const H      = 160;
            canvas.width  = W * dpr;
            canvas.height = H * dpr;
            canvas.style.height = H + 'px';
            const ctx = canvas.getContext('2d');
            ctx.scale(dpr, dpr);

            const isDark = document.documentElement.classList.contains('dark-mode');
            const gridColor  = isDark ? '#334155' : '#e2e8f0';
            const labelColor = isDark ? '#64748b' : '#94a3b8';
            const passColor  = '#16a34a';
            const failColor  = '#dc2626';

            const padL = 36, padR = 8, padT = 8, padB = 28;
            const chartW = W - padL - padR;
            const chartH = H - padT - padB;
            const barW   = Math.max(2, Math.floor(chartW / days.length) - 2);

            // Gridlines
            ctx.strokeStyle = gridColor;
            ctx.lineWidth = 1;
            [0, 0.25, 0.5, 0.75, 1].forEach(frac => {
                const y = padT + chartH * (1 - frac);
                ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(W - padR, y); ctx.stroke();
                if (frac > 0) {
                    ctx.fillStyle = labelColor;
                    ctx.font = '10px sans-serif';
                    ctx.textAlign = 'right';
                    ctx.fillText(Math.round(maxVal * frac), padL - 4, y + 3);
                }
            });

            // Bars
            days.forEach((d, i) => {
                const x = padL + i * (chartW / days.length);
                const passH = (d.pass_count / maxVal) * chartH;
                const failH = (d.fail_count / maxVal) * chartH;
                // Pass bar (bottom)
                ctx.fillStyle = passColor;
                ctx.fillRect(x + 1, padT + chartH - passH, barW, passH);
                // Fail bar (stacked on top)
                ctx.fillStyle = failColor;
                ctx.fillRect(x + 1, padT + chartH - passH - failH, barW, failH);
            });

            // X-axis labels (show ~6 evenly spaced dates)
            ctx.fillStyle = labelColor;
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            const step = Math.max(1, Math.floor(days.length / 6));
            days.forEach((d, i) => {
                if (i % step === 0 || i === days.length - 1) {
                    const x = padL + (i + 0.5) * (chartW / days.length);
                    ctx.fillText(d.day.slice(5), x, H - padB + 14);
                }
            });

            // Legend
            const ly = H - 6;
            ctx.fillStyle = passColor; ctx.fillRect(padL, ly - 8, 12, 8);
            ctx.fillStyle = labelColor; ctx.font = '10px sans-serif'; ctx.textAlign = 'left';
            ctx.fillText('Pass', padL + 14, ly);
            ctx.fillStyle = failColor; ctx.fillRect(padL + 60, ly - 8, 12, 8);
            ctx.fillStyle = labelColor;
            ctx.fillText('Fail', padL + 74, ly);
        })();
        </script>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;margin-bottom:1.5rem;">

<!-- Failure by Check Type -->
<div class="card analytics-section" style="margin-bottom:0;">
    <div class="card-header">Failures by Check Type</div>
    <div class="card-body">
        <?php if (empty($byType)): ?>
            <p class="text-muted" style="text-align:center;">No data.</p>
        <?php else: ?>
            <?php
            $maxFail = max(1, ...array_column($byType, 'fail_count'));
            foreach ($byType as $row):
                $pct = $row['total'] > 0 ? round($row['fail_count'] / $row['total'] * 100) : 0;
                $barW = round($row['fail_count'] / $maxFail * 100);
            ?>
            <div class="bar-row">
                <span class="bar-label" title="<?= htmlspecialchars($row['type_name'] ?? 'Untyped') ?>">
                    <?= htmlspecialchars($row['type_name'] ?? 'Untyped') ?>
                </span>
                <div class="bar-track">
                    <div class="bar-fill-fail" style="width:<?= $barW ?>%;"></div>
                </div>
                <span class="bar-meta"><?= $row['fail_count'] ?> fails (<?= $pct ?>%)</span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Failure by Area -->
<div class="card analytics-section" style="margin-bottom:0;">
    <div class="card-header">Failures by Area</div>
    <div class="card-body">
        <?php if (empty($byArea)): ?>
            <p class="text-muted" style="text-align:center;">No data.</p>
        <?php else: ?>
            <?php
            $maxFail = max(1, ...array_column($byArea, 'fail_count'));
            foreach ($byArea as $row):
                $pct = $row['total'] > 0 ? round($row['fail_count'] / $row['total'] * 100) : 0;
                $barW = round($row['fail_count'] / $maxFail * 100);
                $areaLabel = $row['area_name'] . ' (' . $row['venue_name'] . ')';
            ?>
            <div class="bar-row">
                <span class="bar-label" title="<?= htmlspecialchars($areaLabel) ?>">
                    <?= htmlspecialchars($areaLabel) ?>
                </span>
                <div class="bar-track">
                    <div class="bar-fill-fail" style="width:<?= $barW ?>%;"></div>
                </div>
                <span class="bar-meta"><?= $row['fail_count'] ?> fails (<?= $pct ?>%)</span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</div>

<!-- Top Failed Check Points -->
<div class="card analytics-section">
    <div class="card-header">Top Failed Check Points</div>
    <?php if (empty($topFailed)): ?>
    <div class="card-body text-center">
        <p class="text-muted">No failures recorded in the selected period.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Label</th>
                    <th>Type</th>
                    <th>Area</th>
                    <th>Venue</th>
                    <th style="text-align:right;">Failures</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topFailed as $row): ?>
                <tr>
                    <td><code><?= htmlspecialchars($row['reference'] ?? '') ?></code></td>
                    <td><?= htmlspecialchars($row['label'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['type_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['area_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['venue_name'] ?? '') ?></td>
                    <td style="text-align:right;"><span class="badge badge-fail"><?= $row['fail_count'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
