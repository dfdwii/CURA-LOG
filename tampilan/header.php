<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle).' – ' : '' ?>CURA-LOG</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
<div class="toast-box" id="toastBox"></div>
<div id="sbOvl" onclick="closeSb()"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:99"></div>
<div class="wrap">
