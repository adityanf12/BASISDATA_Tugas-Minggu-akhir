# BASISDATA_Tugas-Minggu-akhir
# 📚 SISTEM MANAJEMEN PERPUSTAKAAN - PHP

Aplikasi CRUD sederhana untuk manajemen perpustakaan menggunakan **PHP** dan **MySQL/MariaDB** dengan implementasi lengkap: **VIEW**, **STORED PROCEDURE**, **FUNCTION**, dan **TRIGGER**.

---

## ✨ Fitur yang Diimplementasikan

### 🗄️ Fitur Database:
- ✅ **2 VIEW**: `book_info_view`, `library_stats`
- ✅ **3 STORED PROCEDURE**: `add_book()`, `update_stock()`, `search_books()`
- ✅ **2 FUNCTION**: `get_total_stock()`, `format_book_info()`
- ✅ **2 TRIGGER**: `before_book_delete`, `after_book_update`

### 🚀 Fitur Aplikasi:
- ✅ **CREATE**: Tambah buku baru (menggunakan Stored Procedure)
- ✅ **READ**: Tampilkan daftar buku (dari VIEW)
- ✅ **UPDATE**: Edit informasi buku & update stok (Stored Procedure)
- ✅ **DELETE**: Hapus buku (TRIGGER mencatat ke log)
- ✅ **SEARCH**: Cari buku berdasarkan judul, penulis, atau ISBN (Stored Procedure)
- ✅ **STATISTICS**: Dashboard dengan statistik perpustakaan (VIEW + FUNCTION)
- ✅ **ACTIVITY LOG**: Riwayat aktivitas dari TRIGGER

---

## 📋 Kebutuhan Sistem

### Software yang Dibutuhkan:
1. **XAMPP** / **WAMP** / **MAMP**
   - Download XAMPP: https://www.apachefriends.org/
2. **PHP 7.4** atau lebih tinggi
3. **MySQL 5.7+** atau **MariaDB 10.3+**
4. **Web Browser** (Chrome, Firefox, Edge, Safari)

---

## 🚀 Panduan Instalasi

### Langkah 1: Install XAMPP

1. Download dan install **XAMPP** dari website resmi
2. Jalankan **XAMPP Control Panel**
3. Start **Apache** dan **MySQL**


---

### Langkah 2: Setup Database

#### Cara 1: Menggunakan phpMyAdmin (Recommended)

1. Buka browser dan akses: **http://localhost/phpmyadmin**
2. Klik tab **"SQL"** di bagian atas
3. Masukkan query semua query yang diperlukan
4. Klik tombol **"Go"** atau **"Kirim"**

#### Cara 2: Menggunakan Command Line

```bash
# Masuk ke direktori MySQL di XAMPP
cd C:\xampp\mysql\bin

# Login ke MySQL
mysql -u root -p

# Import database
source C:\path\to\database_schema.sql
```

**Database akan membuat:**
- Database: `library_db`
- Tabel: `books`, `activity_log`
- 2 Views: `book_info_view`, `library_stats`
- 3 Stored Procedures: `add_book()`, `update_stock()`, `search_books()`
- 2 Functions: `get_total_stock()`, `format_book_info()`
- 2 Triggers: `before_book_delete`, `after_book_update`
- 5 Data contoh buku

---

### Langkah 3: Copy File Aplikasi

1. Copy file **`Library.php`** ke folder web server:

**Untuk XAMPP:**
```
C:\xampp\htdocs\Library\Library.php
```

**Untuk WAMP:**
```
C:\wamp64\www\Library\Library.php
```

**Untuk MAMP (Mac):**
```
/Applications/MAMP/htdocs/Library/Library.php
```

2. Buat folder `Library` jika belum ada

---

### Langkah 4: Konfigurasi Database

1. Buka file **`Library.php`** dengan text editor (Notepad++, VS Code, Sublime)
2. Cari baris **14-17** dan sesuaikan konfigurasi:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Isi password MySQL jika ada
define('DB_NAME', 'library_db');
```

**Catatan:**
- Jika MySQL Anda memiliki password, isi di `DB_PASS`
- Jika menggunakan port custom, ubah `DB_HOST` menjadi `localhost:3307`

---

### Langkah 5: Jalankan Aplikasi

1. Pastikan **Apache** dan **MySQL** di XAMPP sudah berjalan (hijau)
2. Buka browser
3. Akses URL: **http://localhost/Library/Library.php**
4. Aplikasi siap digunakan! 🎉

---

## 📸 Fitur-Fitur Aplikasi

### 1. Dashboard Statistik
Menampilkan 4 kartu statistik:
- **Total Buku** (dari VIEW)
- **Total Stok** (dari FUNCTION `get_total_stock()`)
- **Rata-rata Stok** per buku
- **Total Penulis** unik

### 2. Form Tambah/Edit Buku
- **Tambah Buku Baru** → menggunakan **Stored Procedure** `add_book()`
- **Edit Buku** → update langsung ke tabel
- **Update Stok** → menggunakan **Stored Procedure** `update_stock()`

### 3. Pencarian Buku
- Menggunakan **Stored Procedure** `search_books()`
- Cari berdasarkan: Judul, Penulis, atau ISBN
- Real-time search dengan tombol "Cari"

### 4. Tabel Daftar Buku
- Data diambil dari **VIEW** `book_info_view`
- Status badge otomatis:
  - 🟢 **Available** (stok ≥ 5)
  - 🟡 **Low Stock** (stok 1-4)
  - 🔴 **Out of Stock** (stok = 0)
- Aksi: Edit, Hapus, Update Stok

### 5. Log Aktivitas
- Log otomatis dari **TRIGGER**
- Mencatat semua aksi: INSERT, UPDATE, DELETE
- Menampilkan 15 aktivitas terakhir dengan timestamp

---

## 🧪 Cara Menguji Fitur Database

### Test VIEW:

```sql
-- Cek view book_info_view
SELECT * FROM book_info_view;

-- Cek view library_stats
SELECT * FROM library_stats;
```

### Test FUNCTION:

```sql
-- Test function get_total_stock
SELECT get_total_stock() AS total_stok_buku;

-- Test function format_book_info
SELECT format_book_info('Clean Code', 'Robert Martin', 2008) AS info_lengkap;
```

### Test STORED PROCEDURE:

```sql
-- Test add_book (Tambah buku baru)
CALL add_book('Laskar Pelangi', 'Andrea Hirata', '9789793062822', 2005, 15);

-- Test update_stock (Update stok)
CALL update_stock(1, 25);

-- Test search_books (Cari buku)
CALL search_books('Clean');
```

### Test TRIGGER:

```sql
-- Update buku (trigger after_book_update akan jalan)
UPDATE books SET title = 'Clean Code - Edisi Revisi' WHERE id = 1;

-- Delete buku (trigger before_book_delete akan jalan)
DELETE FROM books WHERE id = 6;

-- Cek activity log untuk melihat trigger bekerja
SELECT * FROM activity_log ORDER BY log_time DESC LIMIT 10;
```

## 🎯 Penjelasan Detail Fitur Database

### 1. VIEW: `book_info_view`
**Fungsi:** Menampilkan informasi lengkap buku dengan status stok

**Kolom yang ditampilkan:**
- Semua kolom dari tabel `books`
- `full_info`: Format informasi menggunakan FUNCTION
- `stock_status`: Status stok (Available/Low Stock/Out of Stock)

**Digunakan di:**
- Tabel daftar buku (line ~470)

**Query:**
```sql
SELECT * FROM book_info_view ORDER BY title;
```

---

### 2. VIEW: `library_stats`
**Fungsi:** Menampilkan statistik perpustakaan secara real-time

**Kolom yang ditampilkan:**
- `total_books`: Total buku di perpustakaan
- `total_stock`: Total stok (menggunakan FUNCTION)
- `avg_stock_per_book`: Rata-rata stok per buku
- `total_authors`: Total penulis unik

**Digunakan di:**
- Dashboard statistik (line ~195)

---

### 3. FUNCTION: `get_total_stock()`
**Fungsi:** Menghitung total stok semua buku di perpustakaan

**Return Type:** `INT`

**Digunakan di:**
- Dashboard statistik card "Total Stok" (line ~198)
- VIEW `library_stats`

**Contoh penggunaan:**
```sql
SELECT get_total_stock() AS total;
```

---

### 4. FUNCTION: `format_book_info()`
**Fungsi:** Memformat informasi buku menjadi string readable

**Parameter:**
- `book_title` (VARCHAR)
- `book_author` (VARCHAR)
- `book_year` (INT)

**Return Type:** `VARCHAR(500)`

**Return Format:** `"[Title] by [Author] ([Year])"`

**Digunakan di:**
- VIEW `book_info_view`

---

### 5. STORED PROCEDURE: `add_book()`
**Fungsi:** Menambah buku baru dan otomatis mencatat ke activity log

**Parameter:**
- `p_title` (VARCHAR)
- `p_author` (VARCHAR)
- `p_isbn` (VARCHAR)
- `p_year` (INT)
- `p_stock` (INT)

**Digunakan di:**
- Form Tambah Buku (line ~67)

**Keuntungan:**
- Mengurangi duplikasi kode
- Log otomatis tercatat
- Transaction handling

---

### 6. STORED PROCEDURE: `update_stock()`
**Fungsi:** Update stok buku dan mencatat perubahan ke log

**Parameter:**
- `p_id` (INT): ID buku
- `p_new_stock` (INT): Stok baru

**Digunakan di:**
- Button "Perbarui" di tabel (line ~117)

**Keuntungan:**
- Mencatat stok lama dan baru
- Log perubahan otomatis

---

### 7. STORED PROCEDURE: `search_books()`
**Fungsi:** Mencari buku berdasarkan keyword

**Parameter:**
- `search_term` (VARCHAR): Kata kunci pencarian

**Mencari di kolom:**
- `title` (judul)
- `author` (penulis)
- `isbn`

**Digunakan di:**
- Search bar (line ~173)

---

### 8. TRIGGER: `before_book_delete`
**Fungsi:** Mencatat informasi buku sebelum dihapus

**Timing:** `BEFORE DELETE`

**Action:**
- Insert ke `activity_log`
- Mencatat: ID, judul, penulis, detail penghapusan

**Dipicu saat:**
- Execute query `DELETE FROM books WHERE id = ?`

---

### 9. TRIGGER: `after_book_update`
**Fungsi:** Mencatat perubahan data buku

**Timing:** `AFTER UPDATE`

**Kondisi:** Hanya log jika `title` atau `author` berubah

**Action:**
- Insert ke `activity_log`
- Mencatat: judul lama → judul baru

**Dipicu saat:**
- Execute query `UPDATE books SET ... WHERE id = ?`

---

## 🔧 Troubleshooting

### ❌ Error: "Connection failed"
**Penyebab:** MySQL tidak berjalan atau konfigurasi salah

**Solusi:**
1. Pastikan MySQL di XAMPP berjalan (lampu hijau)
2. Cek username dan password di `library.php`
3. Test koneksi: buka phpMyAdmin di browser

---

### ❌ Error: "Database 'library_db' doesn't exist"
**Penyebab:** Database belum dibuat

**Solusi:**
1. Import ulang file `database_schema.sql`
2. Pastikan tidak ada error saat import
3. Cek di phpMyAdmin apakah database `library_db` ada

---

### ❌ Error: "Call to undefined function mysqli_connect()"
**Penyebab:** Extension mysqli tidak aktif

**Solusi:**
1. Buka file `php.ini` di folder XAMPP
2. Cari baris `;extension=mysqli`
3. Hapus tanda `;` (uncomment)
4. Restart Apache

---

### ❌ Halaman Blank / Error 500
**Penyebab:** PHP error yang tidak ditampilkan

**Solusi:**
1. Tambahkan di awal file `library.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
2. Cek Apache error log: `xampp/apache/logs/error.log`

---

### ❌ Port 80 Already in Use
**Penyebab:** Port 80 digunakan aplikasi lain (Skype, IIS)

**Solusi:**
1. Klik "Config" di XAMPP → Apache → httpd.conf
2. Ubah `Listen 80` menjadi `Listen 8080`
3. Akses: `http://localhost:8080/perpustakaan/library.php`

---

### ❌ Buku tidak bertambah saat submit form
**Penyebab:** Nama button tidak cocok dengan handler

**Solusi:**
1. Pastikan button name: `tambah_buku`
2. Handler: `if (isset($_POST['tambah_buku']))`
3. Sudah diperbaiki di versi terbaru ✅

---

## 💡 Tips & Tricks

### 1. Backup Database Regular
```bash
# Export database
mysqldump -u root -p library_db > backup_$(date +%Y%m%d).sql

# Windows (XAMPP)
cd C:\xampp\mysql\bin
mysqldump -u root library_db > D:\backup.sql
```

### 2. Reset Database ke Default
```sql
-- Drop database
DROP DATABASE library_db;

-- Import ulang
source database_schema.sql;
```

### 3. Tambah Data Sample
```sql
INSERT INTO books (title, author, isbn, published_year, stock) VALUES
('Bumi Manusia', 'Pramoedya Ananta Toer', '9789799731234', 1980, 20),
('Negeri 5 Menara', 'Ahmad Fuadi', '9786029799514', 2009, 18),
('Perahu Kertas', 'Dee Lestari', '9789792248821', 2009, 12);
```

### 4. Clear Activity Log
```sql
TRUNCATE TABLE activity_log;
```

### 5. Update Password MySQL
```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'password_baru';
FLUSH PRIVILEGES;
```

---

## 📊 Checklist Fitur (Untuk Pengumpulan Tugas)

### Database Features:
- [✅] **CREATE TABLE** (`books`, `activity_log`)
- [✅] **VIEW** (2 views: `book_info_view`, `library_stats`)
- [✅] **STORED PROCEDURE** (3 procedures)
- [✅] **FUNCTION** (2 functions)
- [✅] **TRIGGER** (2 triggers: before delete, after update)

### Application Features:
- [✅] **CREATE** - Tambah buku baru
- [✅] **READ** - Tampilkan daftar buku
- [✅] **UPDATE** - Edit buku & update stok
- [✅] **DELETE** - Hapus buku
- [✅] **SEARCH** - Pencarian buku
- [✅] **STATISTICS** - Dashboard statistik
- [✅] **ACTIVITY LOG** - Riwayat aktivitas

### UI/UX:
- [✅] Responsive design
- [✅] Modern gradient color scheme
- [✅] Font Awesome icons
- [✅] Form validation
- [✅] Confirmation dialog untuk delete
- [✅] Success/error messages
- [✅] Hover effects & animations

---


## 📞 Informasi Tambahan

### Teknologi yang Digunakan:
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3
- **Icons**: Font Awesome 6.4.0
- **Server**: Apache (via XAMPP)

### Spesifikasi Database:
- **Charset**: UTF-8 (utf8mb4)
- **Engine**: InnoDB
- **Collation**: utf8mb4_general_ci

### Browser Support:
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Edge 90+
- ✅ Safari 14+

---

## 🎉 Fitur Unggulan

1. **Single File Application** - Semua kode dalam 1 file PHP
2. **Modern UI/UX** - Gradient design dengan animasi
3. **Responsive** - Tampil baik di desktop & mobile
4. **Database Best Practices** - VIEW, SP, Function, Trigger
5. **Activity Logging** - Track semua perubahan data
6. **Real-time Statistics** - Dashboard update otomatis
7. **Secure** - Prepared statements untuk SQL injection prevention

---

## 📄 Lisensi

Project ini dibuat untuk keperluan tugas kuliah dan bebas digunakan untuk tujuan edukasi.

---


**Dibuat dengan untuk Tugas Basis Data Lanjut**

*Happy Coding! 💻📚*
