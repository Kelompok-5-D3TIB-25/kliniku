<?php

/**
 * KLINIKU — Web Unzipper Utility for InfinityFree
 * 
 * CARA PAKAI:
 * 1. Upload file ini (unzip.php) dan 'deploy.zip' ke dalam folder /htdocs di InfinityFree.
 * 2. Buka browser: http://namadomainanda.infinityfreeapp.com/unzip.php
 * 3. Script ini akan mengekstrak seluruh proyek KLINIKU secara otomatis dalam 2 detik.
 * 4. PENTING: Segera hapus file unzip.php dan deploy.zip setelah ekstraksi berhasil!
 */

header('Content-Type: text/html; charset=utf-8');

$zipFile = __DIR__ . '/deploy.zip';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>KLINIKU Unzipper</title>";
echo "<style>
  body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 40px 20px; }
  .box { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
  .success { border-left: 5px solid #10b981; }
  .error { border-left: 5px solid #ef4444; }
  h2 { margin-top: 0; }
  code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 14px; }
  .btn { display: inline-block; background: #10b981; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-weight: bold; margin-top: 15px; }
  .btn:hover { background: #059669; }
</style></head><body><div class='box'>";

if (!file_exists($zipFile)) {
    echo "<div class='error'><h2 style='color: #ef4444;'>❌ File deploy.zip Tidak Ditemukan</h2>";
    echo "<p>Pastikan Anda telah mengunggah file <code>deploy.zip</code> ke dalam folder yang sama dengan <code>unzip.php</code> (yaitu di <code>/htdocs</code>).</p></div>";
    echo "</div></body></html>";
    exit;
}

if (!class_exists('ZipArchive')) {
    echo "<div class='error'><h2 style='color: #ef4444;'>❌ Ekstensi ZipArchive Tidak Aktif</h2>";
    echo "<p>Server PHP ini tidak mengaktifkan modul ZipArchive bawaan.</p></div>";
    echo "</div></body></html>";
    exit;
}

$zip = new ZipArchive;
$res = $zip->open($zipFile);

if ($res === TRUE) {
    $zip->extractTo(__DIR__ . '/');
    $zip->close();

    echo "<div class='success'><h2 style='color: #10b981;'> Ekstraksi Berhasil Sempurna!</h2>";
    echo "<p>Seluruh berkas backend KLINIKU, vendor produksi, dan GUI Test Client telah diekstrak ke dalam <code>/htdocs</code>.</p>";
    echo "<hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;'>";
    echo "<h3 style='color: #dc2626; margin-bottom: 8px;'>⚠️ 2 Langkah Terakhir yang Wajib Dilakukan:</h3>";
    echo "<ol style='color: #334155; line-height: 1.8;'>";
    echo "<li><strong>Hapus file ini (<code>unzip.php</code>)</strong> dan <code>deploy.zip</code> melalui File Manager InfinityFree demi keamanan website Anda.</li>";
    echo "<li>Buka file <code>.env</code> di File Manager dan pastikan kredensial database MySQL Anda sudah sesuai.</li>";
    echo "</ol>";
    echo "<a href='/' class='btn'>Buka Halaman Penguji API KLINIKU ➔</a>";
    echo "</div>";
} else {
    echo "<div class='error'><h2 style='color: #ef4444;'>❌ Gagal Mengekstrak File</h2>";
    echo "<p>Kode error ZipArchive: <code>$res</code>. Periksa kembali apakah file <code>deploy.zip</code> terunggah secara penuh.</p></div>";
}

echo "</div></body></html>";
