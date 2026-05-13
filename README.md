# CURA-LOG 🏥
**Sistem Manajemen Inventaris Alat Medis**  
Untuk tenaga medis: Admin, Dokter, Organizer

---

## 📋 Fitur Utama
- **Login aman** — CSRF token, anti-SQL Injection (PDO Prepared Statements), validasi kosong/spasi, session timeout
- **Dashboard** — Statistik real-time, notifikasi alat rusak & kalibrasi, peminjaman aktif
- **Inventaris CRUD** — Tambah, edit, hapus, update status, view grid/tabel, search & filter
- **Peminjaman & Histori** — Pinjam alat, kembalikan, riwayat lengkap per user
- **Role-Based Access** — Admin (penuh), Organizer (kelola inventaris), Dokter (lihat & pinjam)
- **Export CSV** — Export inventaris & histori ke CSV (Excel-compatible)
- **Error Handling** — Toast notification, halaman error ramah pengguna

---

## 🗂️ Struktur Direktori
```
CURALOG/
├── assets/
│   └── img/
│       └── alat_medis/      ← Gambar alat medis (.webp)
├── css/
│   └── style.css            ← Stylesheet utama (tema medis)
├── fungsi/
│   ├── inventory.php        ← Daftar & CRUD inventaris
│   ├── tambah_alat.php      ← Form tambah alat
│   ├── edit_alat.php        ← Form edit alat
│   ├── hapus_alat.php       ← Proses hapus alat
│   ├── update_status.php    ← Quick update status
│   ├── proses_pinjam.php    ← Proses peminjaman
│   ├── history.php          ← Histori peminjaman & pengembalian
│   ├── users.php            ← Manajemen user (admin)
│   ├── ruangan.php          ← Manajemen ruangan (admin)
│   └── export.php           ← Export CSV
├── tampilan/
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
├── includes/                ← (reserved)
├── uploads/                 ← Upload gambar user
├── api.php                  ← REST API endpoint (AJAX)
├── config.php               ← Konfigurasi DB & helpers
├── auth_check.php           ← Guard session & role
├── error.php                ← Halaman error
├── index.php                ← Dashboard
├── login.php                ← Halaman login
├── logout.php               ← Proses logout
└── inventaris.sql           ← Database schema + data awal
```

---

## ⚙️ Instalasi

### 1. Persyaratan
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.4+
- Web server: Apache (XAMPP/WAMP) atau Nginx
- Browser modern

### 2. Setup Database
```sql
-- Buka phpMyAdmin atau MySQL CLI, lalu jalankan:
source /path/to/CURALOG/inventaris.sql
```

### 3. Konfigurasi
Edit `config.php`:
```php
define('DB_HOST', 'localhost');   // host database
define('DB_NAME', 'inventaris');  // nama database
define('DB_USER', 'root');        // username MySQL
define('DB_PASS', '');            // password MySQL
define('BASE_URL', 'http://localhost/CURALOG/');  // URL aplikasi
```

### 4. Jalankan
Letakkan folder `CURALOG/` di dalam `htdocs/` (XAMPP) atau `www/` (WAMP), lalu buka:
```
http://localhost/CURALOG/
```

---

## 🔑 Akun Default

| Username   | Password    | Role      | Akses |
|------------|-------------|-----------|-------|
| `admin`    | `admin123`  | Admin     | Penuh: CRUD, user mgmt, export |
| `200101`   | `dokter123` | Dokter    | Lihat inventaris, pinjam/kembalikan alat |
| `organizer`| `org123`    | Organizer | Tambah/edit alat, update status |

> ⚠️ **Ganti password default** sebelum deploy ke production!

---

## 🔒 Keamanan
- **PDO Prepared Statements** — Semua query menggunakan parameter binding
- **CSRF Token** — Setiap form memiliki token unik per sesi
- **Session Guard** — Auto-logout setelah 2 jam tidak aktif
- **Input Sanitization** — `trim()` + `htmlspecialchars()` pada semua input
- **Role-Based Access** — Setiap halaman dicek role sebelum diakses
- **Password** — MD5 (demo). Untuk production, gunakan `password_hash()` + `password_verify()`

---

## 📊 Tabel Database

| Tabel | Deskripsi |
|-------|-----------|
| `alat` | Data alat medis |
| `ruangan` | Daftar ruangan RS |
| `users` | Akun pengguna sistem |
| `history_peminjaman` | Log peminjaman & pengembalian |
| `vendor` | Data vendor/pemasok |
| `standar_kalibrasi` | Standar parameter kalibrasi |

---

## 🎨 Color Palette
| Variabel | Hex | Kegunaan |
|----------|-----|----------|
| Primary Blue | `#0057B8` | Navigasi, tombol utama |
| Secondary Teal | `#00A19C` | Aksen sekunder |
| Background | `#F8FAFC` | Latar halaman |
| Surface | `#FFFFFF` | Kartu & konten |
| Text | `#1E293B` | Teks utama |

---

*CURA-LOG v1.0 — Prodi Teknik Informatika*
