<footer class="footer border-top d-flex align-items-center justify-content-center bg-body" style="position: fixed; bottom: 0; right: 0; width: 83.333333%; height: 60px; z-index: 1020;">
    <span class="text-muted small">
        &copy; <?php echo date('Y'); ?> <strong>CURA-LOG</strong> - Sistem Informasi Inventaris Alat Medis. All Rights Reserved.
    </span>
</footer>

<div class="modal fade" id="modalProfil" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pb-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
                    <?php echo substr($_SESSION['nama'], 0, 1); ?>
                </div>
                <h5 class="fw-bold mb-1"><?php echo $_SESSION['nama']; ?></h5>
                <p class="text-muted mb-3"><?php echo ucfirst($_SESSION['role']); ?></p>
                <div class="bg-body-secondary rounded p-2 text-start">
                    <small class="text-muted d-block">Username / NIP:</small>
                    <strong class="d-block"><?php echo $_SESSION['username']; ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ganti Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo $base_url; ?>fungsi/proses_password.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Password Lama</label>
                        <input type="password" name="pass_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password Baru</label>
                        <input type="password" name="pass_baru" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="pass_konf" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="ganti_pass" class="btn btn-primary w-100">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;

    const currentTheme = localStorage.getItem('theme');
    
    if (currentTheme === 'dark') {
        htmlElement.setAttribute('data-bs-theme', 'dark');
        if (darkModeToggle) {
            darkModeToggle.checked = true;
        }
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                htmlElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    }
});
</script>

</body>
</html>