<div class="col-md-2 sidebar p-0 d-flex flex-column" style="min-height: 100vh;">
    <div>
        <div class="sb-brand">
            <h4>CURA-LOG</h4>
        </div>
        <div class="list-group list-group-flush mt-2">
            <a href="<?php echo $base_url; ?>index.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo $base_url; ?>fungsi/inventory.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">Inventaris</a>
            <a href="<?php echo $base_url; ?>fungsi/history.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">Histori Pinjam</a>
        </div>
    </div>

    <div class="mt-auto p-3 border-top">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary text-white d-flex justify-content-center align-items-center rounded-circle me-2" style="width: 40px; height: 40px; font-weight: bold;">
                <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
            </div>
            <div style="line-height: 1.2;">
                <strong class="d-block text-truncate" style="max-width: 120px; font-size: 0.9rem;">
                    <?php echo $_SESSION['nama']; ?>
                </strong>
                <small class="text-muted text-capitalize"><?php echo $_SESSION['role']; ?></small>
            </div>
        </div>
        <a href="<?php echo $base_url; ?>logout.php" class="btn btn-outline-danger btn-sm w-100">
            <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
    </div>
</div>