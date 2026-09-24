<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JSON API Viewer</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .json-container {
      background: #2a332d;
      color: #d8e0da;
      padding: 25px;
      border-radius: 14px;
      font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
      font-size: 13px;
      line-height: 1.7;
      overflow-x: auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      max-height: 600px;
      overflow-y: auto;
      white-space: pre;
      tab-size: 2;
    }

    .json-key    { color: #a8c0b0; }
    .json-string { color: #d4b483; }
    .json-number { color: #c98a8a; }
    .json-null   { color: #9eb5c2; font-style: italic; }
    .json-bool   { color: #b8a8c9; }

    .json-actions {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
      flex-wrap: wrap;
      align-items: center;
    }

    .json-actions .info {
      background: #d4e4d8;
      color: #4a6b54;
      padding: 8px 14px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
    }

    .btn-json {
      padding: 9px 16px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      font-family: inherit;
      text-decoration: none;
      display: inline-block;
      transition: all 0.2s;
    }

    .btn-json-copy { background: #8fa998; color: #ffffff; }
    .btn-json-copy:hover { background: #6f8a7a; }

    .btn-json-open { background: #d4b483; color: #5a4a2a; }
    .btn-json-open:hover { background: #c9a570; }

    .btn-json-refresh { background: #9eb5c2; color: #ffffff; }
    .btn-json-refresh:hover { background: #7a9aab; }

    body.dark .json-container {
      background: #1a221d;
      color: #d8e0da;
    }
  </style>
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
  <h1>🔌 JSON API Viewer</h1>
  <p style="color:#7a8a80;font-size:13px;margin-bottom:20px;">
    Data buku dalam format JSON dari
    <code style="background:#e8efe9;padding:2px 8px;border-radius:4px;">data_json.php</code>
  </p>

  <div class="json-actions">
    <span class="info" id="infoJumlah">⏳ Memuat data...</span>
    <button class="btn-json btn-json-copy" onclick="copyJSON()">📋 Copy JSON</button>
    <a href="data_json.php" target="_blank" class="btn-json btn-json-open">🔗 Buka Raw JSON</a>
    <button class="btn-json btn-json-refresh" onclick="loadJSON()">🔄 Refresh</button>
  </div>

  <div class="json-container" id="jsonOutput">Memuat data...</div>
</div>

<script>
let jsonData = null;

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

// ================== SYNTAX HIGHLIGHT JSON ==================
function syntaxHighlight(json) {
  json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

  return json.replace(
    /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
    function (match) {
      let cls = 'json-number';
      if (/^"/.test(match)) {
        if (/:$/.test(match)) {
          cls = 'json-key';
        } else {
          cls = 'json-string';
        }
      } else if (/true|false/.test(match)) {
        cls = 'json-bool';
      } else if (/null/.test(match)) {
        cls = 'json-null';
      }
      return '<span class="' + cls + '">' + match + '</span>';
    }
  );
}

// ================== LOAD JSON ==================
function loadJSON() {
  const output = document.getElementById('jsonOutput');
  const info = document.getElementById('infoJumlah');
  output.textContent = 'Memuat data...';
  info.textContent = '⏳ Memuat data...';

  fetch('data_json.php?t=' + Date.now())
    .then(r => r.json())
    .then(data => {
      jsonData = data;
      const jsonStr = JSON.stringify(data, null, 2);
      output.innerHTML = syntaxHighlight(jsonStr);
      info.textContent = '✅ Total: ' + data.length + ' buku | Ukuran: ' + (jsonStr.length / 1024).toFixed(2) + ' KB';
    })
    .catch(err => {
      output.innerHTML = '<span style="color:#c98a8a;">❌ Gagal memuat JSON: ' + err.message + '</span>';
      info.textContent = '❌ Error';
      Swal.fire({
        icon: 'error',
        title: 'Gagal Memuat',
        text: 'Tidak bisa fetch data_json.php',
        confirmButtonColor: '#8fa998'
      });
    });
}

// ================== COPY JSON ==================
function copyJSON() {
  if (!jsonData) {
    Swal.fire({
      icon: 'info',
      title: 'Belum ada data',
      text: 'Tunggu data selesai dimuat.',
      confirmButtonColor: '#8fa998'
    });
    return;
  }

  const jsonStr = JSON.stringify(jsonData, null, 2);

  navigator.clipboard.writeText(jsonStr).then(() => {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil di-copy!',
      text: 'JSON udah masuk clipboard. Tinggal paste di mana aja.',
      confirmButtonColor: '#8fa998',
      timer: 2000,
      showConfirmButton: false
    });
  }).catch(err => {
    Swal.fire({
      icon: 'error',
      title: 'Gagal Copy',
      text: err.message,
      confirmButtonColor: '#8fa998'
    });
  });
}

// ================== INIT ==================
loadJSON();
</script>
</body>
</html>