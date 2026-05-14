<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CURA-LOG - Sistem Inventaris Medis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body, html { overflow-x: hidden; }

        @media (min-width: 768px) {
            .sidebar-desktop { position: fixed; top: 0; left: 0; bottom: 0; width: 16.666667%; z-index: 1030; }
            .main-content { margin-left: 16.666667%; width: 83.333333%; padding-bottom: 80px; }
            .footer-desktop { width: 83.333333%; }
        }

        @media (max-width: 767.98px) {
            .main-content { margin-left: 0; width: 100%; padding-top: 75px; padding-bottom: 80px; }
            .footer-desktop { width: 100%; right: 0; }
        }
        .table-responsive { border: none; }
    </style>
</head>
<body class="bg-body">