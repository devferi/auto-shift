# AutoShiftLog – Automated Attendance Scheduler (Laravel + WhatsApp)

Sistem ini melakukan **absensi otomatis via WhatsApp** berdasarkan **jadwal shift** yang sudah ditentukan.  
Pesan dikirim ke WA dengan format:

- Login  : `log#in#<kode_tempat>#<kode_orang>`
- Logout : `log#out#<kode_tempat>#<kode_orang>`

Contoh:

```text
log#in#cs1#af1
    cs1 = kode tempat / unit kerja (misal: CSSD 1)
    af1 = kode orang / karyawan (misal: Andi Fredi)
```

---

## ✨ Fitur Utama

- Master **Shift** dengan jam berbeda untuk:
  - **Pagi**
    - Senin–Kamis : 08:00 – 16:00  
    - Jumat       : 08:00 – 13:00  
  - **Siang**
    - Senin–Kamis : 13:00 – 21:00  
    - Jumat       : 14:00 – 19:30  
- Master **Tempat / Unit Kerja** (contoh: `cs1 = CSSD 1`).
- Master **Karyawan** dengan **kode orang** (contoh: `af1 = Andi Fredi`).
- Input **jadwal shift harian** per karyawan + tempat.
- Input **pola shift mingguan** (misal: 2 minggu Pagi, 3 minggu Siang → repeat).
- Auto-generate:
  - Jadwal harian dari pola minggu.
  - **Attendance jobs** (login/logout) dengan jam **random** (±15 menit).
- Integrasi ke **WhatsApp Gateway** (HTTP GET).
- **Rekap** jadwal & status pengiriman (login / logout).

---

## 🧱 Arsitektur Singkat

### 1. Format Pesan WA

- Login  : `log#in#<kode_tempat>#<kode_orang>`
- Logout : `log#out#<kode_tempat>#<kode_orang>`

Contoh:

```text
log#in#cs1#af1
log#out#cs1#af1
```

### 2. Endpoint WhatsApp

```http
GET https://wa.posyandudigital.my.id/message/send-text
  ?session=waiskak
  &to=<wa_number>
  &text=<message>
```

- `session` : nama session WA (bisa diset di `.env`).
- `to`      : nomor WhatsApp karyawan (format 62…).
- `text`    : isi pesan (`log#in#...` / `log#out#...`).

---

## 🗄️ Tabel Utama (Database)

### `work_places` – Master Tempat / Unit Kerja

- `id`
- `code` (unik) – contoh: `cs1`
- `name` – contoh: `CSSD 1`
- `description`
- `is_active`
- timestamps

### `employees` – Master Karyawan

- `id`
- `name` – contoh: `Andi Fredi`
- `person_code` (unik) – contoh: `af1`
- `wa_number` – contoh: `62812xxxxxx`
- `default_work_place_id` (nullable)
- `session_key` (nullable)
- `is_active`
- timestamps

### `shifts` – Jenis Shift

- `id`
- `code` – contoh: `PAGI`, `SIANG`
- `name` – contoh: `Shift Pagi`
- `random_before_minutes` (default 15)
- `random_after_minutes` (default 15)
- `is_active`
- timestamps

### `shift_time_rules` – Jam Kerja per Hari

Mapping shift ke hari (Senin–Kamis vs Jumat).

- `id`
- `shift_id` (FK → `shifts`)
- `day_of_week` (1=Senin, ..., 7=Minggu)
- `start_time`
- `end_time`
- `is_active`
- timestamps

Contoh data:

- Pagi:
  - day 1–4 : 08:00 – 16:00
  - day 5   : 08:00 – 13:00
- Siang:
  - day 1–4 : 13:00 – 21:00
  - day 5   : 14:00 – 19:30

### `employee_shift_schedules` – Jadwal Harian

- `id`
- `employee_id` (FK → `employees`)
- `work_place_id` (FK → `work_places`)
- `shift_id` (FK → `shifts`)
- `date`
- `login_message` (nullable)
- `logout_message` (nullable)
- timestamps

> Jika `login_message` / `logout_message` kosong → akan di-generate otomatis:  
> `log#in#<work_places.code>#<employees.person_code>`  
> `log#out#<work_places.code>#<employees.person_code>`

### `shift_week_patterns` – Pola Shift Mingguan

- `id`
- `employee_id`
- `work_place_id` (optional: kalau tempatnya fixed)
- `start_date`
- `cycle_length_weeks`
- `description` (ex: "2 minggu Pagi, 3 minggu Siang")
- `is_active`
- timestamps

### `shift_week_pattern_items`

- `id`
- `shift_week_pattern_id`
- `order_index`
- `duration_weeks`
- `shift_id`
- timestamps

### `attendance_jobs` – Job Kirim WA

- `id`
- `employee_id`
- `work_place_id`
- `shift_id`
- `date` (tanggal shift)
- `type` (`login` / `logout`)
- `message` (text WA final)
- `run_at` (datetime)
- `status` (`pending` / `done` / `failed`)
- `api_url`
- `api_response`
- `attempts`
- timestamps

---

## ⚙️ Konfigurasi `.env`

Tambahkan variabel:

```env
WA_BASE_URL=https://wa.posyandudigital.my.id/message/send-text
WA_DEFAULT_SESSION=waiskak
ATTENDANCE_RANDOM_BEFORE_DEFAULT=15
ATTENDANCE_RANDOM_AFTER_DEFAULT=15
```

---

## 🧩 Cron & Scheduler (Laravel)

### 1. Generate Jadwal Harian dari Pola Mingguan

- **Waktu jalan**: 00:05 setiap hari.
- Tugas:
  - Baca `shift_week_patterns` yang aktif.
  - Tentukan minggu ke berapa (week_position).
  - Tentukan `shift_id` (PAGI / SIANG).
  - Insert / update `employee_shift_schedules` untuk `today`.

### 2. Generate `attendance_jobs` Harian

- **Waktu jalan**: 00:10 setiap hari.
- Tugas:
  - Baca semua `employee_shift_schedules` untuk `today`.
  - Cari `shift_time_rules` berdasarkan `shift_id` + `day_of_week(today)`.
  - Hitung:
    - `login_window_start` = start_time - random_before_minutes  
    - `login_window_end`   = start_time  
    - `logout_window_start`= end_time  
    - `logout_window_end`  = end_time + random_after_minutes
  - Generate 2 job:
    - `type = login`, message `log#in#...`
    - `type = logout`, message `log#out#...`

### 3. Eksekusi `attendance_jobs`

- **Waktu jalan**: setiap menit.
- Tugas:
  - Ambil job dengan:
    - `status = 'pending'`
    - `run_at <= now()`
  - Panggil API WA:
    - `to   = employees.wa_number`
    - `text = attendance_jobs.message`
  - Update:
    - Jika sukses → `status = done`
    - Jika gagal  → `attempts++`, jika `attempts >= 3` → `status = failed`

---

## 🚀 Setup Cepat

1. Clone repo & install dependencies:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2. Setup database di `.env`, lalu:

```bash
php artisan migrate
```

3. (Opsional) Tambah seeder untuk:
   - `shifts` (PAGI, SIANG)
   - `shift_time_rules` (jam Senin–Kamis & Jumat)
   - `work_places` (cs1 = CSSD 1, dst)
   - `employees` (af1 = Andi Fredi, dst)

4. Daftarkan scheduler di cron server:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Contoh Alur

1. Admin input:
   - `work_place`: `cs1` → CSSD 1  
   - `employee`: `af1` → Andi Fredi  
   - shift pattern: 2 minggu Pagi, 3 minggu Siang.

2. Malam hari:
   - Sistem generate jadwal harian: Andi Fredi di `cs1`, shift Pagi.

3. Jam 00:10:
   - Sistem generate:
     - Job login (random antara 07:45–08:00 Senin–Kamis / 07:45–08:00 Jumat).
     - Job logout (random antara 16:00–16:15 Senin–Kamis / 13:00–13:15 Jumat).

4. Saat `run_at` tercapai:
   - Sistem kirim:
     - `log#in#cs1#af1` saat masuk.
     - `log#out#cs1#af1` saat pulang.

---

## 📝 Status

Dokumen ini adalah **README teknis** untuk developer Laravel yang akan mengimplementasikan:

- Struktur database
- Scheduler
- Integrasi ke WhatsApp Gateway
- Halaman admin sederhana (master + jadwal + rekap)

Silakan sesuaikan nama project (misal: `AutoShiftLog`, `AutoAttend`, dll) sesuai branding yang kamu pilih.
