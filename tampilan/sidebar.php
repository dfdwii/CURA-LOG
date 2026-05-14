<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<nav class="navbar navbar-dark bg-primary fixed-top d-md-none shadow-sm" style="z-index: 1050;">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">CURA-LOG</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="offcanvas-md offcanvas-start bg-body shadow-sm p-0 border-end sidebar-desktop" tabindex="-1" id="sidebarMenu">
    <div class="p-4 border-bottom text-center d-none d-md-block">
        <h4 class="text-primary fw-bold m-0">CURA-LOG</h4>
    </div>
    
    <div class="offcanvas-header d-md-none border-bottom">
        <h5 class="offcanvas-title text-primary fw-bold">Navigasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
    </div>
    
    <div class="list-group list-group-flush mt-2 mt-md-0" style="height: calc(100vh - 160px); overflow-y: auto;">
        <a href="<?php echo $base_url; ?>index.php" class="list-group-item list-group-item-action border-0 <?php echo ($current_page == 'index.php') ? 'active bg-primary text-white' : 'bg-transparent'; ?>">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="<?php echo $base_url; ?>fungsi/inventory.php" class="list-group-item list-group-item-action border-0 <?php echo ($current_page == 'inventory.php') ? 'active bg-primary text-white' : 'bg-transparent'; ?>">
            <i class="bi bi-box-seam me-2"></i> Inventaris
        </a>
        <a href="<?php echo $base_url; ?>fungsi/history.php" class="list-group-item list-group-item-action border-0 <?php echo ($current_page == 'history.php') ? 'active bg-primary text-white' : 'bg-transparent'; ?>">
            <i class="bi bi-clock-history me-2"></i> <?php echo ($_SESSION['role'] == 'dokter') ? 'Histori Pinjam' : 'Laporan Peminjaman'; ?>
        </a>
    </div>

    <div class="dropup bg-body border-top" style="position: absolute; bottom: 0; left: 0; width: 100%; z-index: 1040;">
        <div class="d-flex align-items-center justify-content-between p-3" data-bs-toggle="dropdown" style="cursor: pointer;">
            <div class="d-flex align-items-center">
                <?php if(!empty($_SESSION['foto'])){ ?>
                    <img src="<?php echo $base_url; ?>assets/img/users/<?php echo $_SESSION['foto']; ?>" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover; border: 2px solid #0d6efd;">
                <?php } else { ?>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-weight: bold;"><?php echo substr($_SESSION['nama'], 0, 1); ?></div>
                <?php } ?>
                <div class="ms-2">
                    <strong class="d-block text-truncate" style="font-size: 14px; max-width: 110px;"><?php echo $_SESSION['nama']; ?></strong>
                    <span class="text-muted" style="font-size: 12px;"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <i class="bi bi-gear-fill text-secondary fs-5"></i>
        </div>
        <ul class="dropdown-menu shadow border-0 w-100 mb-2">
            <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalProfil"><i class="bi bi-person me-2"></i> Profil</a></li>
            <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalPassword"><i class="bi bi-shield-lock me-2"></i> Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li class="px-3 py-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                    <label class="form-check-label small" for="darkModeToggle">Mode Gelap</label>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 text-danger" href="<?php echo $base_url; ?>logout.php" onclick="return confirm('Yakin ingin keluar?')"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
        </ul>
    </div>
</div>