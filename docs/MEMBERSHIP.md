# Sistem Membership AnimeStream

Dokumen ini merangkum fitur, konfigurasi, alur pembayaran, perawatan, dan pengujian sistem membership AnimeStream.

## Ringkasan Fitur

| Tier | Hierarki | Fitur utama | XP multiplier |
| --- | --- | --- | --- |
| **Free** | 0 (default) | Katalog dasar, watchlist, riwayat tonton, misi mingguan, komentar, dan review | `MEMBERSHIP_XP_BOOST_FREE` (default `1`) |
| **Premium** | 1 | Semua fitur Free, episode premium, akses awal episode, badge Premium, ekspor riwayat CSV | `MEMBERSHIP_XP_BOOST_PREMIUM` (default `1.25`) |
| **VIP** | 2 | Semua fitur Premium, episode VIP, prioritas notifikasi pembaharuan | `MEMBERSHIP_XP_BOOST_VIP` (default `1.5`) |

Hierarki bersifat kumulatif: **Free < Premium < VIP**. Service `MembershipService` menyimpan peringkat tersebut konstanta `TIERS`, lalu `hasAtLeast($user, $minimum)` membandingkan peringkat paket aktif dengan minimum yang diminta. Membership yang sudah kedaluwarsa selalu diperlakukan sebagai Free oleh `hasAtLeast()`.

Fitur lain yang termasuk sistem ini:

- Perpanjangan otomatis menjumlahkan sisa masa aktif (`renew`).
- Perpindahan paket naik/turun (`changePlan`) dengan opsi mempertahankan expiry.
- Pencabutan oleh admin (`revoke`) kembali ke Free.
- Riwayat perubahan, langganan, notifikasi in-app, dan statistik membership.
- Voucher diskon (persentase atau nominal tetap) dengan batas pemakaian.
- Pembayaran Midtrans Snap dan fallback transfer manual.
- Webhook Midtrans dengan validasi signature dan idempotensi.

## Daftar File Utama

### Service

- `app/Services/MembershipService.php` — tier, `currentMembership()`, `grant()`, `renew()`, `changePlan()`, `revoke()`, `expire()`, `activate()`, `statistics()`, `xpMultiplier()`.
- `app/Services/PaymentService.php` — checkout, Snap Midtrans via `Http`, webhook + signature SHA-512, idempotensi, verifikasi manual, refund/failed/expired.
- `app/Services/VoucherService.php` — validasi dan redeem voucher.
- `app/Services/MembershipNotificationService.php` — notifikasi expiry, expired, pembayaran berhasil, verifikasi manual.

### Controller

- `app/Http/Controllers/MembershipController.php` — halaman pricing, akun, riwayat, notifikasi.
- `app/Http/Controllers/CheckoutController.php` — form checkout, submit checkout, cek voucher.
- `app/Http/Controllers/PaymentController.php` — invoice, unggah bukti, callback, webhook.
- `app/Http/Controllers/Admin/MembershipController.php` — daftar/detail member, grant, renew, change, revoke, sync.
- `app/Http/Controllers/Admin/MembershipPlanController.php` dan `MembershipPriceController.php` — kelola paket dan harga.
- `app/Http/Controllers/Admin/MembershipHistoryController.php` — riwayat perubahan.
- `app/Http/Controllers/Admin/PaymentController.php` — verifikasi manual, failed, refunded, expired.
- `app/Http/Controllers/Admin/VoucherController.php` — kelola voucher.

### Model

- `MembershipPlan`, `MembershipPlanPrice`, `UserMembership`, `MembershipSubscription`, `MembershipHistory`, `MembershipNotification`.
- `Payment`, `PaymentWebhookEvent`, `Voucher`, `VoucherRedemption`.

### Migration dan Seeder

- `database/migrations/2026_11_23_000001` sampai `2026_11_23_000011` — tabel membership, harga, voucher, payment, webhook event, notifikasi, history, subscription, serta kolom `minimum_plan` dan `early_access_until` pada episode.
- `database/seeders/MembershipSeeder.php` — paket Free/Premium/VIP, harga 7/30/90/365 hari, dan setting awal.
- `database/seeders/AdminPanelSeeder.php` — role, permission, dan permission `membership.*`.

### Middleware, Policy, dan Command

- `app/Http/Middleware/EnsureMembership.php` — proteksi route dengan `membership:premium` atau `membership:vip`.
- `app/Http/Middleware/CheckPermission.php` — proteksi route admin dengan `permission:membership.*`.
- `app/Policies/MembershipPolicy.php` — otorisasi `manage`, `managePlans`, `verify`, dan view.
- `app/Console/Commands/MembershipSyncCommand.php` — downgrade expired, pengingat, dan penutupan invoice lama; dijadwalkan harian pukul 01:00 di `app/Console/Kernel.php`.

## Instalasi

```bash
php artisan migrate --force
php artisan db:seed --class=MembershipSeeder
php artisan db:seed --class=AdminPanelSeeder
php artisan config:clear
php artisan view:clear
php artisan storage:link
```

`storage:link` diperlukan agar bukti pembayaran manual yang disimpan di `storage/app/public/payment-proofs` dapat diakses dari `public/storage`.

## Konfigurasi `.env`

```dotenv
# Midtrans
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_API_BASE_URL=https://api.sandbox.midtrans.com
MIDTRANS_WEBHOOK_PATH=/payments/midtrans/webhook

# Pembayaran manual
MEMBERSHIP_MANUAL_PAYMENT_ENABLED=true
MEMBERSHIP_MANUAL_BANK_NAME=BCA
MEMBERSHIP_MANUAL_BANK_ACCOUNT=
MEMBERSHIP_MANUAL_BANK_HOLDER=AnimeStream

# Expiry reminder
MEMBERSHIP_EXPIRY_WARNING_DAYS=7,3,1

# XP boost
MEMBERSHIP_XP_BOOST_FREE=1
MEMBERSHIP_XP_BOOST_PREMIUM=1.25
MEMBERSHIP_XP_BOOST_VIP=1.5
```

Catatan sandbox vs produksi:

- **Sandbox**: `MIDTRANS_IS_PRODUCTION=false`, `MIDTRANS_API_BASE_URL=https://api.sandbox.midtrans.com`, serta server/client key dari dashboard Midtrans Sandbox. Gunakan kartu uji dan akun Snap uji.
- **Produksi**: `MIDTRANS_IS_PRODUCTION=true`, `MIDTRANS_API_BASE_URL=https://api.midtrans.com`, serta key produksi dari dashboard Midtrans Production.
- Jika `MIDTRANS_SERVER_KEY` kosong, checkout Midtrans otomatis dialihkan ke transfer manual dengan pesan fallback.
- `MEMBERSHIP_MANUAL_PAYMENT_ENABLED` dipakai opsi checkout untuk menampilkan/info metode manual. Isi rekening bank dengan benar sebelum mengaktifkannya.
- Nilai XP boost juga dapat ditimpa lewat `WebsiteSetting` (`membership_xp_boost_premium`, `membership_xp_boost_vip`) dari panel admin.

## Alur Pembayaran

1. User memilih paket dan durasi di `/membership`, lalu masuk ke `/membership/checkout/{membershipPrice}`.
2. POST `/membership/checkout` memanggil `PaymentService::createCheckout()`. Harga, diskon, dan total dihitung **server-side** dari `membership_plan_prices` dan voucher; nilai harga dari form diabaikan.
3. Invoice dibuat dengan status `pending`, `gateway_order_id` = nomor invoice, dan `expired_at` = 24 jam ke depan. Jika ada voucher, pemakaiannya dicatat di `voucher_redemptions`.
4. Untuk gateway `midtrans`, service membuat transaksi Snap ke `${MIDTRANS_API_BASE_URL}/v1/snap/transactions` dan menyimpan `redirect_url`. Jika request gagal atau key kosong, gateway diubah menjadi `manual`.
5. Midtrans mengirim notifikasi ke `POST /payments/midtrans/webhook` (atau `MIDTRANS_WEBHOOK_PATH` yang didaftarkan di dashboard). Webhook memvalidasi signature, lalu memproses status transaksi.
6. Hanya webhook terverifikasi dengan `transaction_status` `settlement`/`capture` (dan `fraud_status` bukan `challenge`) yang mengubah invoice menjadi `success` dan mengaktifkan membership.
7. Redirect callback `GET /payments/callback?invoice=...` **hanya menampilkan informasi status**. Callback tidak pernah mengaktifkan membership, termasuk bila payment masih `pending` atau query string menyertakan `transaction_status=settlement`.

### Signature Webhook

```text
signature_key = hash('sha512', order_id + status_code + gross_amount + MIDTRANS_SERVER_KEY)
```

Webhook menolak signature yang tidak cocok dengan HTTP 403 dan menyimpan event `invalid_signature` untuk audit. Idempotensi dijaga oleh tabel `payment_webhook_events` dengan kunci unik `(gateway, payload_hash)`; payload duplikat hanya mengembalikan `duplicate: true` tanpa memproses ulang. `PaymentService::markSuccess()` juga memiliki pengaman tambahan dengan memeriksa subscription `purchase` untuk invoice yang sama.

Status webhook lain yang ditangani: `challenge` (ditunda), `expire` (invoice expired), `cancel`/`deny` (failed), dan `refund` (refund bila invoice sebelumnya success).

## Verifikasi Manual, Refund, Expired, dan Failed

- Admin membuka `admin.payments.index`, lalu menyetujui invoice manual melalui `admin.payments.verify`. Hanya invoice dengan `gateway = manual` **dan** `status = pending` yang dapat diverifikasi.
- Verifikasi berhasil memanggil `markSuccess()`: invoice menjadi `success`, membership aktif sesuai durasi paket, notifikasi `payment_success` dibuat, dan `verified_by`/`verified_at` dicatat.
- Invoice Midtrans tidak dapat diverifikasi manual; gunakan webhook atau perbaiki konfigurasi Snap.
- Tombol lain di panel admin:
  - `admin.payments.failed` → `PaymentService::markFailed()`, mencatat alasan kegagalan.
  - `admin.payments.expired` → `PaymentService::markExpired()`, hanya untuk invoice `pending`.
  - `admin.payments.refunded` → `PaymentService::markRefunded()`, hanya untuk invoice `success`.
- Statistik membership mengurangi pendapatan dengan total refund.

## Command `membership:sync`

```bash
php artisan membership:sync
```

Command ini dijalankan otomatis setiap hari pukul **01:00** (`app/Console/Kernel.php`) dan dapat dipicu manual dari panel admin. Tugasnya:

- Mencari membership aktif dengan `expires_at <= now()`, lalu memanggil `MembershipService::expire()` sehingga paket kembali ke **Free** (`expires_at = null`, source `system_expiry`). Proses ini hanya terjadi sekali karena setelah downgrade tidak ada lagi `expires_at`.
- Mengirim pengingat `expiry_warning` untuk 7, 3, dan 1 hari sebelum habis berdasarkan `MEMBERSHIP_EXPIRY_WARNING_DAYS`. Pengingat yang sama tidak dikirim dua kali dalam sehari.
- Menutup invoice `pending` yang sudah melewati `expired_at` menjadi `expired`.

Contoh setting: `MEMBERSHIP_EXPIRY_WARNING_DAYS=7,3,1`.

## Proteksi Episode dan XP

- Episode lokal memiliki kolom `minimum_plan` (`free`, `premium`, `vip`) dan `early_access_until`.
- `WatchController` memanggil `hasAtLeast()` untuk menolak episode dengan minimum paket tertentu. Episode dalam masa early access mensyaratkan minimal **Premium**.
- Route khusus dapat memakai middleware `membership:premium`; user tanpa hak akan diarahkan ke halaman pricing.
- `xpMultiplier()` mengembalikan boost sesuai tier aktif. `User::awardXp()` memakai multiplier tersebut.
- Jika admin mengunci XP ke mode manual (`xp_manual = true`), multiplier dipaksa `1.0` dan nilai XP manual tetap dihormati; sinkronisasi aktivitas tidak menimpanya.

## Panduan Testing

```bash
php artisan test --filter=MembershipFlowTest
php artisan test
```

`MembershipFlowTest` memakai `DatabaseTransactions` (bukan `RefreshDatabase`) agar data lokal tetap utuh, membuat paket/harga sendiri, dan tidak bergantung pada hasil `MembershipSeeder`. Skenario yang dicakup:

1. Hierarki Free < Premium < VIP, `hasAtLeast()`, dan XP multiplier sesuai konfigurasi.
2. Grant admin Premium 30 hari: ekspektasi `expires_at`, subscription, dan history tercatat.
3. Renew menjumlah dari expiry lama, upgrade/downgrade, dan revoke kembali ke Free.
4. `membership:sync` menurunkan membership kedaluwarsa ke Free hanya sekali.
5. Checkout manual memakai harga server-side, voucher diskon valid, invoice `pending`, dan membership belum aktif.
6. Webhook Midtrans: signature valid mengaktifkan, signature invalid ditolak, payload duplikat tidak mengaktifkan ulang.
7. Callback `payments.callback` tidak pernah mengaktifkan membership yang masih `pending`.
8. Verifikasi manual admin mengaktifkan membership, dan menolak invoice non-manual atau sudah diproses.
9. User biasa tidak bisa mengakses `admin.membership.index` dan `admin.membership-plans.index`.
10. Helper paket aman dipanggil ulang saat database sudah berisi hasil `MembershipSeeder` (idempoten berdasarkan slug dan pasangan `plan_id`, `duration_days`).
11. Halaman pricing, akun membership, dan profile tetap render tanpa error dengan setup test ini.

## Catatan Produksi

- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda.com`, dan `SESSION_SECURE_COOKIE=true`.
- Daftarkan webhook di Midtrans Dashboard dengan URL persis: `https://domain-anda.com/payments/midtrans/webhook`.
- Pastikan `APP_KEY` sudah di-generate dan kredensial Midtrans produksi tidak tercampur dengan sandbox.
- `route:cache` **belum dipakai** karena route existing di `routes/web.php` masih menggunakan closure. Jangan menjalankan `php artisan route:cache` sampai closure tersebut dipindahkan ke controller.

## Troubleshooting

| Gejala | Penyebab umum | Solusi |
| --- | --- | --- |
| Halaman pricing kosong / paket tidak ada | Seeder belum dijalankan atau paket nonaktif | Jalankan `php artisan db:seed --class=MembershipSeeder`, cek `is_active` |
| Webhook ditolak 403 `invalid_signature` | Server key salah, payload diubah, atau beda environment | Samakan `MIDTRANS_SERVER_KEY` dengan dashboard yang dipakai; pastikan `order_id + status_code + gross_amount + server_key` identik |
| Checkout Midtrans jadi transfer manual | Key kosong atau request Snap gagal | Isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`, cek `MIDTRANS_API_BASE_URL`, lihat log `Midtrans snap transaction failed` |
| Membership tidak turun ke Free setelah habis | Command sync belum jalan | Jalankan `php artisan membership:sync`, cek jadwal cron/scheduler |
| XP tidak bertambah sesuai boost | XP mode manual aktif atau setting boost tertimpa | Cek `xp_manual` dan `WebsiteSetting` boost |
| Bukti pembayaran tidak tampil | Storage link belum dibuat | Jalankan `php artisan storage:link` |

## Identitas Visual Tier dan Batas Kualitas

`MembershipService::visual()` menjadi satu-satunya sumber kelas CSS badge komentar, bingkai avatar profil, dan label kualitas:

| Tier | Warna komentar | Bingkai profil | Kualitas maksimal |
| --- | --- | --- | --- |
| Free | abu/slate | abu | `720p` |
| Premium | biru brand | biru brand | `1080p` |
| VIP | amber | amber | `4K UHD` (`2160`) |

Pembatasan kualitas diterapkan di server (`MembershipService::normalizeRequestedQuality()` + `WatchController`), bukan hanya di tampilan. Permintaan kualitas di atas hak akses diturunkan; source server yang tidak diizinkan tidak dikirim ke player dan tombolnya mengarah ke halaman paket. Nilai frontend tidak dipercaya.

## Pemulihan Anime dan Jumlah Katalog API

- `php artisan anime:restore-deleted` memulihkan entri blacklist ke tabel `animes` lokal, lalu menghapus entri blacklist tersebut. Tombol **Pulihkan** di **Admin > Blacklist Anime** melakukan hal yang sama per entri.
- Hapus massal konten (**delete-all** / purge) hanya menghapus baris lokal dan **tidak** membuat entri blacklist baru, supaya judul dari API tetap tampil di katalog publik. Blacklist dipakai hanya untuk penyembunyian eksplisit oleh admin.
- `AnimeCatalogController` menghitung total dari jumlah item yang benar-benar tersaring, bukan dari jumlah variasi identifier blacklist (`slug`, `db_slug`, `title`, `title-slug`).
- `OtakudesuApiService::mergeSnapshotAnimeSummaries()` hanya menggabungkan metadata snapshot ke item yang sudah ada di `animeList`; tidak menambah item hantu. Dengan demikian total katalog mengikuti jumlah `animeList` API.
- `animeListApi()` kini mengisi `thumb`, `score`, `studio`, dan metadata lain dari snapshot lokal yang sudah ada. Jumlah dan urutan item tidak berubah; item tanpa snapshot dibiarkan apa adanya.
- `searchApi()` punya fallback ke daftar `animeList` yang sudah di-cache saat scraper halaman pencarian gagal, jadi pencarian tidak kembali kosong hanya karena sumber sementara bermasalah.
- `catalog()` memakai `searchApi()` yang sama, sehingga pencarian di halaman katalog dan endpoint API memakai source of truth yang identik.
- Detail anime/episode yang tidak ditemukan kini mengembalikan `404` dengan `status: not_found`, bukan `200 success` dengan array kosong. Deteksi memakai `hasAnimeDetailData()` / `hasEpisodeDetailData()` agar judul placeholder hasil fallback tidak dianggap data asli.
- Endpoint `/api/v1/*` kini tersedia sebagai alias kompatibilitas ke controller yang sudah ada: `home`, `catalog`, `anime-list`, `ongoing-anime`, `complete-anime`, `genres`, `genres/{genre}`, `search`, `anime/{animeId}`, dan `episode/{episodeId}`. Tidak ada controller atau file duplikat yang dibuat.
- Snapshot bisa dihangatkan dengan `php artisan scraper:refresh-snapshots --pages=68 --all-genres --deep --anime-limit=300`. Perintah ini mengisi metadata/poster dari arsip, genre, dan halaman detail tanpa mengubah jumlah katalog.

Test terkait: `php artisan test --filter=ApiContractFixTest`.

## Mode Pemeliharaan

Pengaturan di **Admin > Pengaturan > Keamanan** menulis `maintenance_mode` dan `maintenance_message`, lalu menyinkronkan key lama `site_maintenance` dan `site_maintenance_message`. `CheckSiteMaintenance` membaca keduanya sehingga toggle admin langsung berlaku.

- Pengunjung biasa: halaman web `503` dengan pesan pemeliharaan; endpoint `/api/*` `503` JSON.
- Administrator dan halaman `/login`: tetap bisa diakses.
- Webhook Midtrans (`/payments/midtrans/webhook`) dikecualikan agar transaksi tetap diproses.

Test terkait: `php artisan test --filter=CatalogAndMaintenanceFixTest` dan `php artisan test --filter=ApiContractFixTest`.
