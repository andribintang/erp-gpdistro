# GPDISTRO ERP & E-Commerce

ERP dan e-commerce untuk operasional GPDISTRO Racing Indonesia.

## Menjalankan Di Lokal

Prasyarat:

- Folder proyek berada di `D:\xampp\htdocs\erp-gpdistro`
- PHP XAMPP tersedia di `D:\xampp\php\php.exe`
- Node.js dan `npm` sudah terpasang

Langkah tercepat:

1. Klik dua kali `local-setup.bat`.
2. Setelah selesai, klik dua kali `local-start.bat`.
3. Buka `http://127.0.0.1:8000`.

Login demo lokal:

```text
Email: admin@gpdistro.test
Password: change-me-now
```

Server lokal memakai SQLite, jadi Apache dan MySQL XAMPP tidak wajib
dijalankan. Data demo aman dijalankan berulang kali dan tidak akan mereset
perubahan stok yang sudah dibuat.

## Modul Aktif

- Login staf dan role admin
- Dashboard ERP
- Master gudang
- Master produk dan saldo awal
- Inventori dan ledger mutasi stok
- Penyesuaian stok
- Supplier
- Purchase order
- Approval purchase order oleh owner
- Penerimaan parsial purchase order ke inventori
- Goods receipt dan ledger mutasi stok

## Perintah Manual

Gunakan PHP XAMPP bila `php` belum tersedia pada PATH Windows:

```powershell
D:\xampp\php\php.exe artisan migrate --force
D:\xampp\php\php.exe artisan db:seed --force
npm.cmd run build
D:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

Test:

```powershell
D:\xampp\php\php.exe artisan test
```

## Dokumentasi

- Audit dan roadmap: `PROJECT_CONTINUATION_PLAN.md`
- Railway: `RAILWAY_DEPLOYMENT.md`
