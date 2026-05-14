# Log Prompt AI

Dokumen ini merangkum penggunaan asisten AI selama pengembangan **Service B - Transaksi & Pembayaran** pada Tugas 2 mata kuliah BBK2HAB3 - Integrasi Aplikasi Enterprise.

## Informasi Proyek

| Parameter | Nilai |
|-----------|-------|
| **Mata Kuliah** | BBK2HAB3 - Integrasi Aplikasi Enterprise |
| **Mahasiswa** | Hadid Hamar |
| **NIM** | 102022400126 |
| **Service** | Service B - Transaksi & Pembayaran |
| **Framework** | Laravel 13 (PHP 8.4) |
| **Database** | MySQL 8.0 |
| **Asisten AI** | Claude Opus 4 |

> Service A (Lahan & Lokasi) dan Service C (Keanggotaan & Voucher) dikembangkan secara independen oleh anggota kelompok lain pada repository terpisah.

## Prompt 1 - Inisialisasi Arsitektur Microservice

Penyusunan kerangka awal seluruh backend berdasarkan dokumen `Context1.md`, `Context2.md`, dan `Group 6 Smart Parking.md`. Setiap service diwajibkan memiliki REST API, dokumentasi Swagger, GraphQL, mekanisme keamanan berbasis API Key, dan Dockerfile.

Prompt:

```text
Mohon inisialisasi tiga backend service untuk sistem Smart Parking sesuai spesifikasi
pada dokumen konteks. Tiap service menggunakan NIM anggota sebagai nilai header
X-IAE-KEY. Sertakan Dockerfile, docker-compose.yml, dan dokumentasi penggunaan.
```

Keluaran AI:

- Membuat tiga direktori awal: `service-a/`, `service-b/`, dan `service-c/`.
- Mengimplementasikan Service B dengan endpoint REST, GraphQL, serta orkestrasi ke Service A dan Service C.
- Menyusun `Dockerfile` dan `.dockerignore` untuk masing-masing service.
- Membuat `docker-compose.yml` untuk orkestrasi container.
- Memvalidasi alur end-to-end: tapping in → checkout → pembayaran.
- Menyediakan dokumentasi penggunaan ringkas.

## Prompt 2 - Klarifikasi Navigasi Direktori

Perintah `cd service-a` menghasilkan error ketika dijalankan dari direktori `service-c`.

Prompt:

```text
Mohon penjelasan mengapa perintah `cd service-a` menghasilkan error ketika dijalankan
dari dalam direktori `service-c`, serta sarankan perbaikan dokumentasi penggunaan.
```

Keluaran AI:

- Menjelaskan bahwa `service-a` merupakan direktori sejajar (sibling), bukan subdirektori.
- Memperbarui dokumentasi agar menggunakan path absolut untuk meminimalkan kesalahan navigasi.

## Prompt 3 - Investigasi Peringatan Deprecation Node.js

Muncul peringatan `DEP0169: url.parse()` pada terminal saat service dijalankan.

Prompt:

```text
Tolong analisis arti dan dampak peringatan `DEP0169 DeprecationWarning url.parse()`
yang muncul ketika service dijalankan.
```

Keluaran AI:

- Mengidentifikasi peringatan berasal dari pustaka internal `swagger-ui-express`, bukan dari kode aplikasi.
- Menyatakan peringatan tidak memengaruhi fungsionalitas dan aman untuk diabaikan hingga pustaka dirilis pembaruan.

## Prompt 4 - Penanganan Docker Engine Tidak Aktif

Proses build container gagal karena Docker Desktop belum dijalankan.

Prompt:

```text
Saat menjalankan `docker-compose up --build` muncul error koneksi ke API Docker pada
`npipe:////./pipe/dockerDesktopLinuxEngine`. Mohon panduan penyelesaiannya.
```

Keluaran AI:

- Memastikan Docker Desktop harus berjalan sebelum eksekusi perintah Docker.
- Menghapus atribut `version: '3.8'` yang telah deprecated dari `docker-compose.yml`.

## Prompt 5 - Penanganan Konflik Port (EADDRINUSE)

Port 3001, 3002, dan 3003 telah digunakan oleh proses Node.js terdahulu.

Prompt:

```text
Service tidak dapat start karena port 3002 telah dialokasikan. Mohon panduan untuk
mengidentifikasi dan menghentikan proses yang menempati port tersebut.
```

Keluaran AI:

- Mendeteksi proses Node.js yang masih berjalan dari sesi sebelumnya.
- Menggunakan `netstat -ano` untuk mengidentifikasi PID terkait.
- Menggunakan `taskkill /F` untuk menghentikan proses yang menempati port.

## Prompt 6 - Konflik Eksekusi Lokal dan Container

Konflik port terjadi karena instans lokal dan container Docker berjalan bersamaan.

Prompt:

```text
Saat menjalankan `docker-compose up`, daemon melaporkan port `0.0.0.0:3003` sudah
terpakai padahal tidak ada container lain. Tolong jelaskan akar masalahnya.
```

Keluaran AI:

- Menjelaskan bahwa menjalankan service secara lokal (mis. `php artisan serve`) bersamaan dengan Docker akan mengakibatkan benturan port.
- Merekomendasikan untuk memilih salah satu mode eksekusi, tidak dijalankan bersamaan.

## Prompt 7 - Akses GraphQL Playground Tertolak (401)

Middleware autentikasi memblokir permintaan `GET /graphql` sehingga GraphiQL gagal dimuat.

Prompt:

```text
Halaman GraphQL Playground mengembalikan respon `Unauthorized` saat diakses melalui
browser. Tolong rancang penyesuaian routing yang tetap menjaga keamanan endpoint.
```

Keluaran AI:

- Memisahkan handler GraphQL menjadi dua route:
  - `GET /graphql` untuk menyajikan halaman GraphiQL playground tanpa autentikasi.
  - `POST /graphql` untuk pemrosesan query dengan validasi `X-IAE-KEY`.

## Prompt 8 - Penyederhanaan Input Header pada GraphiQL

Editor header bawaan GraphiQL memerlukan JSON yang valid sehingga kurang praktis bagi pengguna.

Prompt:

```text
Tolong tambahkan kolom input khusus di GraphQL Playground agar NIM dapat dimasukkan
langsung (misal `102022400126`) tanpa harus menulis JSON header secara manual.
```

Keluaran AI:

- Menonaktifkan editor header bawaan GraphiQL (`headerEditorEnabled: false`).
- Menambahkan input field khusus berlabel `X-IAE-KEY` pada bagian atas halaman.
- Memodifikasi fetcher agar membaca nilai input dan menyisipkannya ke header HTTP.
- Memvalidasi: input kosong menghasilkan respons `Unauthorized`; NIM valid memuat data sesuai query.

## Prompt 9 - Migrasi Service B ke Laravel

Migrasi Service B dari Node.js/Express ke Laravel sambil menyelaraskan kembali alur integrasi dengan Service A dan Service C.

Prompt:

```text
Mohon konversi Service B (Hadid Hamar) menjadi aplikasi Laravel, dengan tetap
mempertahankan kepatuhan terhadap `Context1.md`, `Context2.md`, dan `Group 6 Smart
Parking.md`. Pastikan integrasi occupy/release slot, penggunaan voucher, serta aksi
frontend (TAP_OUT dan PAYMENT_SUCCESS) berjalan sesuai spesifikasi.
```

Keluaran AI:

- Mengimplementasikan ulang Service B di atas Laravel beserta REST controller, middleware `X-IAE-KEY`, dokumentasi Swagger/OpenAPI, dan endpoint GraphQL.
- Memperbaiki interaksi dengan Service A: aksi `occupy-spot` dan `release-spot` menggunakan metode `POST`.
- Menyelaraskan alur voucher: status `terpakai` ditetapkan setelah pembayaran berhasil.
- Memetakan aksi frontend: `TAP_OUT` → `/checkout`, `PAYMENT_SUCCESS` → `/pay`.

## Prompt 10 - Restrukturisasi Repository Menjadi Single-Service dengan MySQL

Penyesuaian struktur repository karena tugas dikerjakan dan disubmit secara individual per service.

Prompt:

```text
Tugas akhir dikerjakan secara individual per service dan setiap anggota mengelola
repository terpisah. Saya bertanggung jawab atas Service B. Mohon rombak struktur
proyek ini agar berfokus pada Service B (hapus Service A, Service C, dan kebutuhan
multi-repo terkait). Pindahkan seluruh isi folder `service-b/` ke root project agar
repository ini dapat berdiri sendiri. Konfigurasikan stack Docker sehingga `docker
compose up` menjalankan dua container terhubung: aplikasi Service B dan MySQL.
Pastikan implementasi tetap selaras dengan `Context1.md` dan `Context2.md`.
```

Keluaran AI:

- Menghapus direktori `service-a/`, `service-c/`, dan `frontend/` beserta dokumentasi integrasi multi-repo yang tidak lagi relevan.
- Memindahkan seluruh isi `service-b/` ke root project, menjadikan repository sebagai proyek Service B standalone.
- Menulis ulang `docker-compose.yml` menjadi stack dua container: `service-b-app` (port `3002`) dan `service-b-mysql` (port host `3307`) lengkap dengan healthcheck dan volume `service-b-mysql-data` untuk persistensi data.
- Memperbarui `Dockerfile`: pemasangan ekstensi `pdo_mysql`, penambahan `docker/entrypoint.sh` yang menunggu MySQL siap kemudian menjalankan `php artisan migrate --seed --force` sebelum `php artisan serve`.
- Mengganti `TransactionStore` berbasis JSON file dengan Eloquent Model `Transaction`, migrasi `transactions`, dan seeder awal (`trx_001`–`trx_003`).
- Menjadikan integrasi terhadap Service A dan Service C bersifat opsional (`SERVICE_A_URL`, `SERVICE_C_URL` via environment). Apabila tidak diisi, Service B memakai data referensi lokal sehingga REST dan GraphQL tetap dapat dievaluasi secara standalone.
- Memperbarui `README.md` dan `.env.example` agar selaras dengan struktur baru.

## Prompt 11 - Stabilisasi Skrip Entrypoint pada Container Docker

Container `service-b-app` menggantung pada tahap menunggu MySQL karena perintah `mysqladmin ping` di entrypoint tidak menghasilkan exit-code sukses dengan kredensial pengguna aplikasi.

Prompt:

```text
Saat dijalankan dengan `docker compose up`, container aplikasi tertahan pada pesan
"Menunggu MySQL" walau container MySQL telah `healthy`. Mohon analisis akar masalah
dan terapkan perbaikan yang lebih andal pada skrip entrypoint.
```

Keluaran AI:

- Mengidentifikasi penyebab: ketergantungan pada `mysqladmin ping` yang berperilaku tidak konsisten ketika menggunakan kredensial non-root.
- Mengganti mekanisme pengecekan kesiapan database dengan koneksi PDO native dari PHP yang sejalan dengan konfigurasi runtime Laravel.
- Menambahkan retry terbatas (maksimum 30 percobaan dengan jeda dua detik) untuk mencegah loop tak terhingga apabila MySQL benar-benar gagal start.
- Menambahkan `php artisan config:clear` sebelum migrasi untuk memastikan konfigurasi termuat dari environment terkini.

## Prompt 12 - Halaman Beranda Service di Root URL

Akses `http://localhost:3002/` semestinya menampilkan halaman beranda informatif, bukan respon JSON, untuk mempermudah evaluasi visual dan navigasi dokumentasi.

Prompt:

```text
Saat membuka `http://localhost:3002/`, sebaiknya muncul halaman landing Laravel yang
menampilkan identitas service serta tautan menuju dokumentasi, bukan respon JSON.
Mohon implementasikan tampilan landing yang representatif.
```

Keluaran AI:

- Mengubah route `/` pada `routes/web.php` agar mengembalikan halaman HTML dengan tema gelap yang konsisten dengan tampilan Swagger.
- Menampilkan identitas service: nama mahasiswa, NIM, framework, serta resource utama.
- Menyediakan kartu navigasi cepat ke `Swagger UI`, `GraphQL Playground`, `OpenAPI JSON`, `GET /api/v1/transactions`, dan `GET /health`.
- Menyertakan catatan kaki mengenai kewajiban penyertaan header `X-IAE-KEY` pada seluruh endpoint `/api/v1/*`.
