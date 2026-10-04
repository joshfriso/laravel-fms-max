# Dokumentasi API melalui Postman

Proyek ini memakai rute web Laravel dengan autentikasi sesi. Koleksi Postman di `docs/postman/` dibuat sebagai smoke test standar: login sebagai admin, membuka halaman utama yang dilindungi autentikasi, lalu logout.

## Menggunakan koleksi

1. Jalankan aplikasi pada `http://127.0.0.1:8088`.
2. Impor `docs/postman/laravel-fms.postman_collection.json` ke Postman.
3. Impor `docs/postman/laravel-fms.postman_environment.json`.
4. Pilih environment `Laravel FMS - Lokal`.
5. Jalankan request berurutan dari atas ke bawah.

Urutan request:

- `Auth > Login`
- `Auth > Login admin`
- `Dashboard > Dashboard`
- `Folders > Daftar folder`
- `Documents > Daftar dokumen`
- `Departments > Daftar departemen`
- `Activity > Daftar aktivitas`
- `Trash > Daftar sampah`
- `Auth > Logout`

## Catatan autentikasi

Request `Auth > Login` wajib dijalankan lebih dulu agar Postman menyimpan cookie sesi Laravel dan cookie `XSRF-TOKEN`. Untuk `POST /login` dan `POST /logout`, koleksi membaca cookie tersebut, melakukan decode, lalu mengirimnya sebagai header `X-XSRF-TOKEN`.

Koleksi ini sengaja tidak membuat, mengubah, mengunggah, atau menghapus data. Tujuannya hanya memastikan login admin dan halaman utama aplikasi dapat diakses seperti pengujian manual biasa.

Untuk Postman Local Mode, gunakan berkas YAML v3 di folder `postman/`. Berkas JSON di `docs/postman/` disediakan untuk impor manual dan dokumentasi portabel.

Proyek belum menyediakan API JSON terpisah dengan awalan `/api`, sehingga contoh request mengikuti rute web Laravel dan respons Inertia.
