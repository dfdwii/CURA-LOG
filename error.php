<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Error – CURA-LOG</title>
<style>*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#F8FAFC;
display:flex;align-items:center;justify-content:center;min-height:100vh}
.b{background:#fff;border-radius:16px;box-shadow:0 4px 32px rgba(0,87,184,.1);
padding:52px 44px;text-align:center;max-width:440px;width:90%}
.i{font-size:56px;margin-bottom:16px}h1{font-size:1.4rem;color:#0057B8;margin-bottom:10px}
p{color:#64748B;line-height:1.7;margin-bottom:26px;font-size:.88rem}
a{background:#0057B8;color:#fff;padding:10px 28px;border-radius:8px;
text-decoration:none;font-weight:600;font-size:.88rem}
a:hover{background:#0041A3}</style></head><body>
<div class="b"><div class="i">⚠️</div><h1>Terjadi Kesalahan</h1>
<p><?= isset($error_msg)?htmlspecialchars($error_msg):'Halaman tidak ditemukan atau terjadi kesalahan sistem.' ?></p>
<a href="<?= defined('BASE_URL')?BASE_URL:'' ?>index.php">← Kembali ke Dashboard</a>
</div></body></html>
