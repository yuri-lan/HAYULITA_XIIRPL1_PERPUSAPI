<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$total    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM books"))[0];
$kategori = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT kategori) FROM books"))[0];
$penulis  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT penulis) FROM books"))[0];
$penerbit = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT penerbit) FROM books"))[0];
$terbaru  = mysqli_fetch_row(mysqli_query($conn,"SELECT MAX(tahun_terbit) FROM books"))[0];
$terlama  = mysqli_fetch_row(mysqli_query($conn,"SELECT MIN(tahun_terbit) FROM books"))[0];
$rataTahun= round(mysqli_fetch_row(mysqli_query($conn,"SELECT AVG(tahun_terbit) FROM books"))[0]);

$targetBaca = 50;
$persenTarget = $targetBaca > 0 ? min(100, round(($total / $targetBaca) * 100)) : 0;

$qKat = mysqli_query($conn, "SELECT kategori, COUNT(*) as jml FROM books GROUP BY kategori ORDER BY jml DESC");
$kategoriData = [];
while ($r = mysqli_fetch_assoc($qKat)) $kategoriData[] = $r;

$qTahun = mysqli_query($conn, "SELECT tahun_terbit, COUNT(*) as jml FROM books GROUP BY tahun_terbit ORDER BY tahun_terbit ASC");
$tahunData = [];
while ($r = mysqli_fetch_assoc($qTahun)) $tahunData[] = $r;

// Cari nilai max buat set batas atas sumbu Y
$maxTahun = 0;
foreach ($tahunData as $t) {
    if ((int)$t['jml'] > $maxTahun) $maxTahun = (int)$t['jml'];
}

$qRecent = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC LIMIT 5");
$recentBooks = [];
while ($r = mysqli_fetch_assoc($qRecent)) $recentBooks[] = $r;

$qTopPenulis = mysqli_query($conn, "SELECT penulis, COUNT(*) as jml FROM books GROUP BY penulis ORDER BY jml DESC LIMIT 5");

$ach = [
    ['icon'=>'📚','title'=>'Kolektor Pemula','desc'=>'Koleksi minimal 10 buku','unlocked'=>$total >= 10],
    ['icon'=>'🏛️','title'=>'Pustakawan Muda','desc'=>'Koleksi minimal 25 buku','unlocked'=>$total >= 25],
    ['icon'=>'👑','title'=>'Master Perpus','desc'=>'Koleksi minimal 50 buku','unlocked'=>$total >= 50],
    ['icon'=>'🎨','title'=>'Kolektor Kategori','desc'=>'Punya 5 kategori berbeda','unlocked'=>$kategori >= 5],
    ['icon'=>'✍️','title'=>'Penggemar Penulis','desc'=>'Punya 10 penulis berbeda','unlocked'=>$penulis >= 10],
    ['icon'=>'🆕','title'=>'Update Terus','desc'=>'Ada buku terbit 2024+','unlocked'=>$terbaru >= 2024],
];

function coverColor($id) {
    $colors = [
        ['#8fa998', '#6f8a7a'], ['#a8c0b0', '#8fa998'], ['#d4b483', '#c9a570'],
        ['#c98a8a', '#b06f6f'], ['#9eb5c2', '#7a9aab'], ['#b8a8c9', '#9a8aab'],
        ['#a8c9b5', '#8aab97'],
    ];
    return $colors[$id % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav>
  <span class="brand">📚 Perpustakaan</span>
  <a href="dashboard.php" class="nav-link">Dashboard</a>
  <a href="buku.php" class="nav-link">Data Buku</a>
  <a href="tambah.php" class="nav-link">Tambah Buku</a>
  <a href="export_excel.php" class="nav-link">Export Excel</a>
  <a href="export_pdf.php" class="nav-link">Export PDF</a>
  <a href="logout.php" class="nav-link" onclick="return konfirmasiLogout(event)">Logout</a>
  <button class="dark-toggle" onclick="toggleDark()" id="darkBtnDesktop">🌙</button>
  <button class="hamburger" onclick="toggleMenu(event)" id="hamburgerBtn">☰</button>
  <div class="nav-menu" id="navMenu">
    <a href="dashboard.php">Dashboard</a>
    <a href="buku.php">Data Buku</a>
    <a href="tambah.php">Tambah Buku</a>
    <a href="export_excel.php">Export Excel</a>
    <a href="export_pdf.php">Export PDF</a>
    <a href="logout.php" onclick="return konfirmasiLogout(event)">Logout</a>
    <button class="dark-toggle-menu" onclick="toggleDark()" id="darkBtnMenu">🌙 Dark Mode</button>
  </div>
</nav>

<div class="container">

  <div class="hero">
    <h1>👋 Halo, <?= htmlspecialchars($_SESSION['admin']) ?>!</h1>
    <p>Selamat datang di Dashboard Perpustakaan. Berikut ringkasan data buku hari ini.</p>
  </div>

  <h2 style="margin-bottom:15px;">📊 Statistik Utama</h2>
  <div class="stats-v2">
    <div class="stat-card">
      <span class="icon">📚</span>
      <div class="label">Total Buku</div>
      <div class="value count-up" data-target="<?= $total ?>">0</div>
    </div>
    <div class="stat-card">
      <span class="icon">🏷️</span>
      <div class="label">Kategori</div>
      <div class="value count-up" data-target="<?= $kategori ?>">0</div>
    </div>
    <div class="stat-card">
      <span class="icon">✍️</span>
      <div class="label">Penulis</div>
      <div class="value count-up" data-target="<?= $penulis ?>">0</div>
    </div>
    <div class="stat-card">
      <span class="icon">🏢</span>
      <div class="label">Penerbit</div>
      <div class="value count-up" data-target="<?= $penerbit ?>">0</div>
    </div>
    <div class="stat-card">
      <span class="icon">🆕</span>
      <div class="label">Terbit Terbaru</div>
      <div class="value count-up" data-target="<?= $terbaru ?>">0</div>
    </div>
    <div class="stat-card">
      <span class="icon">📅</span>
      <div class="label">Rata-rata Tahun</div>
      <div class="value count-up" data-target="<?= $rataTahun ?>">0</div>
    </div>
  </div>

  <div class="panel">
    <h2>🎯 Target Koleksi Buku</h2>
    <div class="progress-wrap">
      <div class="progress-info">
        <span><?= $total ?> dari <?= $targetBaca ?> buku</span>
        <span><?= $persenTarget ?>%</span>
      </div>
      <div class="progress-bar-bg">
        <div class="progress-bar-fill" data-progress="<?= $persenTarget ?>"></div>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <div class="panel">
      <h2>🍩 Buku per Kategori</h2>
      <div class="chart-full">
        <canvas id="chartKategori"></canvas>
      </div>
    </div>
    <div class="panel">
      <h2>📈 Buku per Tahun Terbit</h2>
      <div class="chart-full">
        <canvas id="chartTahun"></canvas>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <div class="panel">
      <h2>📅 Kalender Bulan Ini</h2>
      <div class="calendar-month" id="calMonth"></div>
      <table class="calendar">
        <thead>
          <tr>
            <th>Sen</th><th>Sel</th><th>Rab</th><th>Kam</th><th>Jum</th><th>Sab</th><th>Min</th>
          </tr>
        </thead>
        <tbody id="calBody"></tbody>
      </table>
    </div>

    <div class="panel">
      <h2>🆕 5 Buku Terbaru</h2>
      <ul class="recent-list">
        <?php if (empty($recentBooks)): ?>
          <li style="color:#8a9a90;">Belum ada data buku.</li>
        <?php else: foreach ($recentBooks as $b):
            $c = coverColor($b['id']);
        ?>
          <li>
            <div class="recent-item">
              <?php if ($b['cover'] && file_exists(__DIR__.'/uploads/'.$b['cover'])): ?>
                <img src="uploads/<?= htmlspecialchars($b['cover']) ?>" class="cover-img" alt="cover">
              <?php else: ?>
                <div class="recent-cover" style="background: linear-gradient(135deg, <?= $c[0] ?>, <?= $c[1] ?>);">
                  <?= strtoupper(substr($b['judul'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div class="recent-info">
                <div class="judul"><?= htmlspecialchars($b['judul']) ?></div>
                <div class="meta"><?= htmlspecialchars($b['penulis']) ?> • <?= $b['tahun_terbit'] ?></div>
              </div>
              <span class="badge"><?= htmlspecialchars($b['kategori']) ?></span>
            </div>
          </li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>

  <div class="panel">
    <h2>🏅 Pencapaian</h2>
    <div class="achievements">
      <?php foreach ($ach as $a): ?>
        <div class="achievement <?= $a['unlocked'] ? '' : 'locked' ?>">
          <div class="ach-icon"><?= $a['icon'] ?></div>
          <div class="ach-info">
            <div class="ach-title"><?= $a['title'] ?></div>
            <div class="ach-desc"><?= $a['desc'] ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="grid-2">
    <div class="panel">
      <h2>🏆 Top 5 Penulis</h2>
      <ul class="recent-list">
        <?php
        $adaPenulis = false;
        while ($p = mysqli_fetch_assoc($qTopPenulis)):
          $adaPenulis = true;
        ?>
          <li>
            <div>
              <div class="judul"><?= htmlspecialchars($p['penulis']) ?></div>
              <div class="meta">Penulis</div>
            </div>
            <span class="badge"><?= $p['jml'] ?> buku</span>
          </li>
        <?php endwhile; ?>
        <?php if (!$adaPenulis): ?>
          <li style="color:#8a9a90;">Belum ada data penulis.</li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="panel">
      <h2>⚡ Aksi Cepat</h2>
      <div class="quick-actions">
        <a href="tambah.php" class="quick-btn"><span class="icon">➕</span> Tambah Buku</a>
        <a href="buku.php" class="quick-btn"><span class="icon">📖</span> Data Buku</a>
        <a href="export_excel.php" class="quick-btn"><span class="icon">📊</span> Export Excel</a>
        <a href="export_pdf.php" class="quick-btn"><span class="icon">📄</span> Export PDF</a>
      </div>
    </div>
  </div>

</div>

<script>
// ================== HAMBURGER MENU ==================
function toggleMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('navMenu');
  const btn = document.getElementById('hamburgerBtn');
  menu.classList.toggle('open');
  btn.textContent = menu.classList.contains('open') ? '✕' : '☰';
}
document.addEventListener('click', function(e) {
  const menu = document.getElementById('navMenu');
  const btn = document.getElementById('hamburgerBtn');
  if (menu && menu.classList.contains('open') && !menu.contains(e.target) && e.target !== btn) {
    menu.classList.remove('open');
    btn.textContent = '☰';
  }
});

// ================== DARK MODE ==================
function toggleDark() {
  document.body.classList.toggle('dark');
  const isDark = document.body.classList.contains('dark');
  localStorage.setItem('darkMode', isDark ? '1' : '0');
  updateDarkButtons(isDark);
  updateChartColors(isDark);
}
function updateDarkButtons(isDark) {
  const btnD = document.getElementById('darkBtnDesktop');
  const btnM = document.getElementById('darkBtnMenu');
  if (btnD) btnD.textContent = isDark ? '☀️' : '🌙';
  if (btnM) btnM.textContent = isDark ? '☀️ Dark Mode' : '🌙 Dark Mode';
}
if (localStorage.getItem('darkMode') === '1') {
  document.body.classList.add('dark');
  updateDarkButtons(true);
} else {
  updateDarkButtons(false);
}

// ================== LOGOUT ==================
function konfirmasiLogout(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Logout?', text: 'Anda akan keluar dari dashboard admin.', icon: 'question',
    showCancelButton: true, confirmButtonText: 'Ya, Logout', cancelButtonText: 'Batal',
    confirmButtonColor: '#8fa998', cancelButtonColor: '#a8c0b0'
  }).then((r) => { if (r.isConfirmed) window.location.href = 'logout.php'; });
  return false;
}

// ================== CHART ==================
const warnaPastel = ['#8fa998','#a8c0b0','#c9d6cd','#d4b483','#c98a8a','#9eb5c2','#b8a8c9','#a8c9b5','#d4c9a8','#c9b8a8'];
const maxTahunVal = <?= $maxTahun ?>; // nilai max dari PHP

let chartKategori, chartTahun;
function textColor() { return document.body.classList.contains('dark') ? '#d8e0da' : '#3d4a42'; }
function gridColor() { return document.body.classList.contains('dark') ? '#4e5c52' : '#e0e6e0'; }

function initCharts() {
  // ==== DOUGHNUT: Buku per Kategori ====
  chartKategori = new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_column($kategoriData, 'kategori')) ?>,
      datasets: [{
        data: <?= json_encode(array_column($kategoriData, 'jml')) ?>,
        backgroundColor: warnaPastel,
        borderWidth: 3,
        borderColor: document.body.classList.contains('dark') ? '#3d4a42' : '#ffffff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: { top: 10, bottom: 5 }
      },
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: textColor(),
            font: { family: 'Segoe UI', size: 12 },
            padding: 12,
            boxWidth: 12,
            boxHeight: 12
          }
        }
      }
    }
  });

  // ==== BAR: Buku per Tahun ====
  chartTahun = new Chart(document.getElementById('chartTahun'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_column($tahunData, 'tahun_terbit')) ?>,
      datasets: [{
        label: 'Jumlah Buku',
        data: <?= json_encode(array_column($tahunData, 'jml')) ?>,
        backgroundColor: '#a8c0b0',
        hoverBackgroundColor: '#8fa998',
        borderRadius: 8,
        borderSkipped: false,
        maxBarThickness: 40
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: { top: 20, right: 10, left: 5, bottom: 5 }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(61, 74, 66, 0.95)',
          padding: 10,
          cornerRadius: 8,
          titleFont: { size: 13, weight: 'bold' },
          bodyFont: { size: 12 }
        }
      },
      scales: {
        x: {
          ticks: {
            color: textColor(),
            font: { size: 11 },
            maxRotation: 0,
            autoSkip: false
          },
          grid: { display: false },
          border: { display: false }
        },
        y: {
          beginAtZero: true,
          // Kasih ruang di atas bar tertinggi
          suggestedMax: maxTahunVal + 1,
          ticks: {
            color: textColor(),
            stepSize: 1,
            precision: 0,
            font: { size: 11 }
          },
          grid: { color: gridColor(), drawBorder: false },
          border: { display: false }
        }
      }
    }
  });
}

function updateChartColors(isDark) {
  const color = isDark ? '#d8e0da' : '#3d4a42';
  const grid  = isDark ? '#4e5c52' : '#e0e6e0';
  const border= isDark ? '#3d4a42' : '#ffffff';

  // Doughnut
  chartKategori.options.plugins.legend.labels.color = color;
  chartKategori.data.datasets[0].borderColor = border;
  chartKategori.update();

  // Bar
  chartTahun.options.scales.x.ticks.color = color;
  chartTahun.options.scales.y.ticks.color = color;
  chartTahun.options.scales.y.grid.color = grid;
  chartTahun.update();
}

// ================== COUNT-UP ==================
function animateCountUp() {
  document.querySelectorAll('.count-up').forEach(el => {
    const target = parseInt(el.dataset.target) || 0;
    const duration = 1200;
    const start = performance.now();
    function update(now) {
      const progress = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(ease * target);
      if (progress < 1) requestAnimationFrame(update);
      else el.textContent = target;
    }
    requestAnimationFrame(update);
  });
}

// ================== PROGRESS BAR ==================
function animateProgress() {
  const fill = document.querySelector('.progress-bar-fill');
  if (!fill) return;
  const target = fill.dataset.progress || 0;
  setTimeout(() => { fill.style.width = target + '%'; }, 300);
}

// ================== KALENDER ==================
function renderCalendar() {
  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth();
  const today = now.getDate();
  const namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  document.getElementById('calMonth').textContent = `${namaBulan[month]} ${year}`;
  const firstDay = new Date(year, month, 1).getDay();
  const offset = (firstDay === 0) ? 6 : firstDay - 1;
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const tbody = document.getElementById('calBody');
  tbody.innerHTML = '';
  let row = document.createElement('tr');
  for (let i = 0; i < offset; i++) {
    const td = document.createElement('td');
    td.innerHTML = `<div class="day empty">-</div>`;
    row.appendChild(td);
  }
  for (let d = 1; d <= daysInMonth; d++) {
    if (row.children.length === 7) { tbody.appendChild(row); row = document.createElement('tr'); }
    const td = document.createElement('td');
    const isToday = d === today;
    td.innerHTML = `<div class="day ${isToday ? 'today' : ''}">${d}</div>`;
    row.appendChild(td);
  }
  while (row.children.length > 0 && row.children.length < 7) {
    const td = document.createElement('td');
    td.innerHTML = `<div class="day empty">-</div>`;
    row.appendChild(td);
  }
  if (row.children.length > 0) tbody.appendChild(row);
}

// ================== INIT ==================
initCharts();
renderCalendar();
window.addEventListener('load', () => { animateCountUp(); animateProgress(); });
if (document.body.classList.contains('dark')) {
  setTimeout(() => updateChartColors(true), 100);
}
</script>
</body>
</html>