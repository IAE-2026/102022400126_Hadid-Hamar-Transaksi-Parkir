# Service B - Transaksi & Pembayaran (Smart Parking)

Service Laravel untuk Tugas 2 mata kuliah **BBK2HAB3 - Integrasi Aplikasi Enterprise**. Service ini mengelola transaksi parkir (tapping in, kalkulasi keluar, dan penyelesaian pembayaran). Repository ini berisi hanya Service B dari ekosistem Smart Parking (Service A dan Service C dikelola di repository terpisah oleh anggota kelompok lain).

## Identitas

| Parameter | Nilai |
|-----------|-------|
| **Mata Kuliah** | BBK2HAB3 - Integrasi Aplikasi Enterprise |
| **Mahasiswa** | Hadid Hamar |
| **NIM / `X-IAE-KEY`** | `102022400126` |
| **Resource** | `transactions` |
| **Framework** | Laravel 13 (PHP 8.4) |
| **Database** | MySQL 8.0 |
| **Port** | `3002` |

## Endpoint REST

Semua endpoint wajib menyertakan header:

```http
X-IAE-KEY: 102022400126
Content-Type: application/json
```

| Method | Path | Fungsi |
|--------|------|--------|
| `GET` | `/api/v1/transactions` | Mengambil daftar seluruh transaksi |
| `GET` | `/api/v1/transactions/{id}` | Mengambil detail satu transaksi |
| `POST` | `/api/v1/transactions` | Membuat transaksi baru (tapping in) |
| `POST` | `/api/v1/transactions/{id}/checkout` | Kalkulasi biaya saat tapping out |
| `POST` | `/api/v1/transactions/{id}/pay` | Menyelesaikan pembayaran |
| `POST` | `/api/v1/transactions/{id}` | Action gabungan (`TAP_OUT` / `PAYMENT_SUCCESS`) |

Semua respons mengikuti **Standard Integration Contract** dengan bentuk konsisten:

```json
{ "status": "success | error", "message": "...", "data": {}, "errors": null }
```

Error level framework (`404` / `405` / `422` / `500`) juga otomatis dibungkus ke format yang sama.

### Contoh body POST `/api/v1/transactions`

```json
{
  "location_id": "loc_001",
  "member_card_id": "MEM001"
}
```

### Contoh action gabungan

```json
{ "action": "TAP_OUT" }
```

```json
{ "action": "PAYMENT_SUCCESS", "payment_method": "qris" }
```

## Dokumentasi API & GraphQL

| Halaman | URL |
|---------|-----|
| Swagger UI | `http://localhost:3002/api-docs` |
| OpenAPI JSON | `http://localhost:3002/openapi.json` |
| GraphQL Playground | `http://localhost:3002/graphql` |
| Health check | `http://localhost:3002/health` |

Contoh query GraphQL:

```graphql
{
  transactions {
    id
    location_id
    duration_hours
    total_amount
    status
  }
}
```

## Integrasi dengan Service A & C (opsional)

Service B dirancang untuk berkomunikasi dengan:

- **Service A** (Lahan & Lokasi) → validasi lokasi, ambil tarif, occupy/release slot.
- **Service C** (Keanggotaan & Voucher) → validasi anggota, validasi/pemakaian voucher.

Aktifkan integrasi dengan mengisi env berikut:

```env
SERVICE_A_URL=http://<host-service-a>:3001
SERVICE_C_URL=http://<host-service-c>:3003
```

Jika kosong, Service B tetap berfungsi penuh memakai data referensi lokal (mock untuk lokasi & member) sehingga rubrikasi REST/GraphQL tetap dapat dinilai standalone.

## Menjalankan dengan Docker (Direkomendasikan)

Pastikan Docker Desktop sudah aktif.

```bash
docker compose up -d --build
```

Setelah container sehat, akses:

- `http://localhost:3002/health`
- `http://localhost:3002/api-docs`
- `http://localhost:3002/graphql`

Stack Docker terdiri dari **dua container**:

| Container | Image | Port host |
|-----------|-------|-----------|
| `service-b-app` | Build dari `Dockerfile` | `3002` |
| `service-b-mysql` | `mysql:8.0` | `3307` |

Migrasi dan seeder otomatis dijalankan oleh `docker/entrypoint.sh` setiap kontainer app start.

Hentikan stack:

```bash
docker compose down          # tanpa hapus data
docker compose down -v       # ikut menghapus volume MySQL
```

## Menjalankan Lokal (tanpa Docker)

Prasyarat: PHP 8.3+, Composer, MySQL 8.

```bash
composer install
cp .env.example .env
php artisan key:generate
# sesuaikan kredensial DB pada .env (DB_HOST, DB_DATABASE, dst.)
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=3002
```

## Pengujian

Test otomatis memakai SQLite in-memory, jadi **tidak butuh MySQL** dan bisa langsung dijalankan setelah clone:

```bash
composer install
php artisan test
```

Cakupan test (`tests/Feature/TransactionApiTest.php`): penolakan tanpa `X-IAE-KEY` (401), key tidak valid (403), `GET` daftar transaksi (200 + wrapper), detail tidak ditemukan (404 + wrapper), `POST` membuat transaksi (201), path tak dikenal (404, bukan 405), dan method tidak diizinkan (405 + wrapper).

### Smoke test cepat (setelah service jalan di port 3002)

```bash
# Tanpa key -> 401
curl -i http://localhost:3002/api/v1/transactions

# Dengan key -> 200
curl -i -H "X-IAE-KEY: 102022400126" http://localhost:3002/api/v1/transactions

# Path tak dikenal -> 404 (bukan 405)
curl -i -H "X-IAE-KEY: 102022400126" http://localhost:3002/api/v1/tidak-ada
```

## Lihat juga

- `AI_PROMPT_LOG.md` — log prompt AI selama pengembangan.
