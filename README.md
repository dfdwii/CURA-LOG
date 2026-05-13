# 🏥 CURA-LOG - Sistem Informasi Manajemen Inventaris Alat Medis

CURA-LOG adalah aplikasi berbasis web yang dirancang untuk memudahkan rumah sakit atau klinik dalam mengelola, melacak, dan memonitor inventaris alat medis. Aplikasi ini dikembangkan menggunakan **PHP (Prosedural/MySQLi)** dan antarmuka **Bootstrap 5**, sangat cocok sebagai referensi tugas mata kuliah Pemrograman Web / Basis Data.

## ✨ Fitur Utama

Aplikasi ini menggunakan sistem *Role-Based Access Control* (RBAC) dengan 3 jenis hak akses: **Admin**, **Organizer**, dan **Dokter**.

### 👨‍⚕️ Fitur Dokter:
* **Registrasi Mandiri:** Dokter dapat membuat akun sendiri melalui halaman pendaftaran.
* **Melihat Inventaris:** Melihat daftar alat medis yang tersedia beserta detail kelengkapannya.
* **Peminjaman Alat:** Meminjam alat medis yang berstatus "Tersedia" dengan mencatat keperluan dan ruangan tujuan.
* **Histori Pribadi:** Melihat riwayat peminjaman alat yang hanya dilakukan oleh dokter yang bersangkutan.
* **Pengembalian Alat:** Mengembalikan alat medis yang telah selesai digunakan.

### 👨‍💻 Fitur Admin & Organizer (Staff Inventaris):
* **Dashboard Interaktif:** Menampilkan statistik total alat, alat tersedia, sedang dipinjam, serta notifikasi peringatan jika ada alat yang *Rusak* atau sedang *Maintenance*.
* **Manajemen Inventaris (CRUD):** Menambah, mengedit, dan menghapus data alat medis beserta gambar (mendukung *upload* gambar atau *preset*).
* **Update Status & Kondisi:** Mengubah status alat secara cepat (Tersedia, Dipinjam, Rusak, Maintenance, Perlu Kalibrasi).
* **Monitoring Histori Keseluruhan:** Memantau seluruh aktivitas peminjaman dan pengembalian alat dari semua pengguna.

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP 8+ (Gaya Penulisan Prosedural / MySQLi)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
* **Framework CSS:** Bootstrap 5.3
* **Icon:** Bootstrap Icons

---

## 🚀 Cara Instalasi (Localhost)

1. **Persiapan:** Pastikan kamu sudah menginstal web server lokal seperti **XAMPP**, **Laragon**, atau **MAMP**.
2. **Download Proyek:** Unduh atau *clone* repositori ini, lalu letakkan folder proyek di dalam direktori root server lokal kamu (contoh: `C:\xampp\htdocs\CURALOG`).
3. **Konfigurasi Database:**
   * Buka phpMyAdmin (biasanya di `http://localhost/phpmyadmin`).
   * Buat database baru dengan nama **`inventaris`**.
   * Import file **`inventaris.sql`** yang ada di dalam folder proyek ke database tersebut.
4. **Penyesuaian Koneksi:**
   * Buka file `config.php` (atau `koneksi.php`).
   * Pastikan detail koneksi sudah sesuai dengan server lokal kamu:
     ```php
     $host = "localhost";
     $user = "root";
     $pass = "";
     $db   = "inventaris";
     $base_url = "http://localhost/CURALOG/"; // Sesuaikan dengan nama folder kamu
     ```
5. **Jalankan Aplikasi:** Buka browser dan akses `http://localhost/CURALOG/`.

---

## 🔑 Akun Demo (Default Login)

Karena *password* dienkripsi menggunakan MD5, kamu dapat menggunakan akun bawaan berikut untuk menguji sistem:

| Role | Username (ID) | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` |
| **Organizer** | `organizer` | `org123` |
| **Dokter** | `200101` | `dokter123` |

---

## 📂 Struktur Folder Utama

```text
/CURALOG
├── assets/
│   └── img/
│       └── alat_medis/    # Folder penyimpanan gambar alat & preset
├── css/
│   └── style.css          # File CSS kustom tambahan
├── fungsi/                # Folder berisi logika CRUD dan proses utama
│   ├── edit_alat.php
│   ├── hapus_alat.php
│   ├── history.php
│   ├── inventory.php
│   ├── proses_pinjam.php
│   ├── tambah_alat.php
│   └── update_status.php
├── tampilan/              # Folder untuk memisahkan komponen UI (Header, Footer, Sidebar)
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── auth_check.php         # Penjaga sesi (Middleware pengecekan login)
├── config.php             # Konfigurasi koneksi database
├── index.php              # Halaman Dashboard Utama
├── login.php              # Halaman Login
├── registrasi.php         # Halaman Registrasi Dokter
└── inventaris.sql         # File export database MySQL
