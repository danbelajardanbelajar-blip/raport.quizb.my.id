<?php
/**
 * PORTAL RAPORT MANDIRI STS GANJIL 2026/2027 - KELAS X.13
 * MA MATHOLI'UL ANWAR LAMONGAN
 * Wali Kelas: Dr. Zainul Hakim, M.H.I.
 * Arsitektur: Paket B (index.php + data_x13.json)
 */

$json_file = __DIR__ . '/data_x13.json';

// Endpoint API untuk Simpan Perubahan Data dari Mode Dashboard Wali Kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save') {
    header('Content-Type: application/json; charset=utf-8');
    $raw_input = file_get_contents('php://input');
    $payload = json_decode($raw_input, true);

    if (!$payload || !isset($payload['pin']) || !isset($payload['data'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Format data tidak valid.']);
        exit;
    }

    $current_data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : null;
    $valid_pin = $current_data['meta']['pin_walas'] ?? '1313';

    if ($payload['pin'] !== $valid_pin) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'PIN Wali Kelas tidak sesuai!']);
        exit;
    }

    $saved = file_put_contents($json_file, json_encode($payload['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($saved !== false) {
        echo json_encode(['status' => 'success', 'message' => 'Data nilai & catatan berhasil disimpan ke server (data_x13.json).']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menulis ke file data_x13.json. Pastikan permission file 644/666 di cPanel.']);
    }
    exit;
}

$initial_json = file_exists($json_file) ? file_get_contents($json_file) : '{}';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport Mandiri STS Ganjil 2026/2027 - Kelas X.13 | MA Matholi'ul Anwar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-arab { font-family: 'Amiri', serif; }
        [x-cloak] { display: none !important; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .raport-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col justify-between" x-data="raportApp()" x-init="initApp()">

    <!-- NAVBAR UTAMA (NON-PRINT) -->
    <header class="bg-emerald-900 text-white shadow-lg no-print sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 cursor-pointer" @click="backToHome()">
                <div class="w-10 h-10 rounded-xl bg-emerald-700 border border-emerald-500 flex items-center justify-center font-bold text-lg shadow-inner">
                    MA
                </div>
                <div>
                    <h1 class="font-bold text-sm sm:text-base tracking-wide leading-tight" x-text="db.meta.madrasah">MA MATHOLI'UL ANWAR</h1>
                    <p class="text-xs text-emerald-200">Portal Raport Mandiri STS Ganjil 2026/2027 • Kelas X.13</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <template x-if="mode !== 'home'">
                    <button @click="backToHome()" class="px-3 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-700 text-xs font-semibold border border-emerald-600 transition flex items-center gap-1.5">
                        <span>←</span> Beranda / Cari Siswa
                    </button>
                </template>

                <template x-if="mode === 'student' || mode === 'print_all'">
                    <button @click="window.print()" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span x-text="mode === 'print_all' ? 'Cetak Semua (33 Hal A4)' : 'Cetak / Simpan PDF'"></span>
                    </button>
                </template>

                <button @click="openWalasModal()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-300 text-xs font-semibold border border-emerald-700/50 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span x-text="isWalas ? 'Dashboard Wali Kelas' : 'Mode Wali Kelas'"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- NOTIFIKASI TOAST -->
    <div x-show="toast.show" x-cloak x-transition class="fixed bottom-5 right-5 z-50 max-w-sm px-4 py-3 rounded-xl shadow-xl border text-sm font-medium flex items-center gap-2.5 no-print"
         :class="toast.type === 'success' ? 'bg-emerald-900 text-white border-emerald-600' : 'bg-rose-900 text-white border-rose-600'">
        <span x-text="toast.message"></span>
    </div>

    <!-- MAIN CONTENT -->
    <main class="flex-grow max-w-6xl w-full mx-auto px-4 py-6">

        <!-- ================= 1. MODE HOME / LOGIN SISWA & WALI MURID ================= -->
        <div x-show="mode === 'home'" x-cloak class="max-w-xl mx-auto my-6 no-print">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-br from-emerald-800 via-emerald-900 to-teal-950 p-6 sm:p-8 text-center text-white relative">
                    <p class="font-arab text-2xl text-emerald-200 mb-1">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</p>
                    <span class="inline-block px-3 py-0.5 rounded-full bg-emerald-700/70 border border-emerald-400/30 text-[11px] font-semibold uppercase tracking-wider text-emerald-100 mb-2">
                        Sumatif Tengah Semester (STS) Ganjil 2026/2027
                    </span>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">Raport Mandiri Kelas X.13</h2>
                    <p class="text-xs sm:text-sm text-emerald-100/90 mt-1">Program Keagamaan (KAG) • MA Matholi'ul Anwar</p>
                    <p class="text-xs text-amber-300 font-medium mt-2">Wali Kelas: <span x-text="db.meta.wali_kelas"></span></p>
                </div>

                <div class="p-6 sm:p-8">
                    <form @submit.prevent="loginStudent()" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Masukkan Username Kartu STS Siswa
                            </label>
                            <input type="text" x-model="searchUsername" placeholder="Contoh: L11MA10410"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-3 focus:ring-emerald-500/20 text-base font-bold uppercase tracking-wider text-slate-800 placeholder:font-normal placeholder:tracking-normal"
                                   required>
                            <p class="text-[11px] text-slate-500 mt-1.5">
                                *Gunakan Username resmi STS (10 karakter, misal: <code class="font-mono font-bold text-emerald-700">L11MA10410</code>) untuk menjaga privasi nilai ananda.
                            </p>
                        </div>

                        <template x-if="loginError">
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="loginError"></div>
                        </template>

                        <button type="submit" class="w-full py-3.5 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                            <span>Buka Laporan Hasil Belajar</span>
                            <span>→</span>
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-3 gap-2 text-center">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <p class="text-[11px] text-slate-500 font-medium">Jumlah Siswa</p>
                            <p class="text-base font-extrabold text-slate-800" x-text="db.students.length + ' Siswa'"></p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <p class="text-[11px] text-slate-500 font-medium">Mapel Aktif KAG</p>
                            <p class="text-base font-extrabold text-emerald-700" x-text="db.subjects.length + ' Mapel'"></p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <p class="text-[11px] text-slate-500 font-medium">Rata-Rata Kelas</p>
                            <p class="text-base font-extrabold text-amber-600" x-text="db.meta.class_overall_avg"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 2. MODE DASHBOARD WALI KELAS ================= -->
        <div x-show="mode === 'walas'" x-cloak class="space-y-6 no-print">
            <!-- Header Dashboard -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Mode Administrator Wali Kelas</span>
                    <h2 class="text-xl font-extrabold text-slate-800 mt-1">Dashboard Evaluasi & Manajemen Raport Kelas X.13</h2>
                    <p class="text-xs text-slate-500">Anda dapat melihat peringkat lengkap, mengedit nilai susulan, mengubah catatan wali kelas, atau mencetak 33 raport sekaligus.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="saveToServer()" :disabled="isSaving" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                        <span x-text="isSaving ? 'Menyimpan...' : '💾 Simpan ke Server (JSON)'"></span>
                    </button>
                    <button @click="downloadJson()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                        ⬇️ Unduh data_x13.json
                    </button>
                    <button @click="mode = 'print_all'" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow transition">
                        🖨️ Siapkan Cetak Massal (33 Siswa)
                    </button>
                </div>
            </div>

            <!-- Statistik Ringkas Kelas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Rata-Rata Kelas X.13</p>
                    <p class="text-2xl font-extrabold text-emerald-700 mt-1" x-text="db.meta.class_overall_avg"></p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dari 32 siswa aktif mengikuti STS</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Mapel Rata-Rata Tertinggi</p>
                    <p class="text-sm font-extrabold text-slate-800 mt-1">Ilmu Kalam & Akhlak (86.50)</p>
                    <p class="text-[11px] text-emerald-600 font-medium mt-0.5">SKI (70.88) • Ilmu Tafsir (70.38)</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Mapel Perlu Evaluasi Bersama</p>
                    <p class="text-sm font-extrabold text-rose-700 mt-1">Matematika (24.06)</p>
                    <p class="text-[11px] text-rose-500 font-medium mt-0.5">B. Inggris (32.50) • Ilmu Hadis (35.50)</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-amber-200 bg-amber-50/40 shadow-sm">
                    <p class="text-xs font-semibold text-amber-800">Perhatian Nilai Kosong / Susulan</p>
                    <p class="text-sm font-extrabold text-amber-900 mt-1">2 Siswa Belum Lengkap</p>
                    <p class="text-[11px] text-amber-700 mt-0.5">Faza (17 mapel) & Nur Farihatul (2 mapel)</p>
                </div>
            </div>

            <!-- Tabel Rekap & Peringkat Kelas X.13 -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-slate-800">Daftar Peringkat & Rekap Nilai Siswa Kelas X.13</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="walasFilter" placeholder="Cari nama atau username..."
                               class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:border-emerald-600">
                        <select x-model="walasSort" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold bg-white">
                            <option value="absen">Urut Nomor Absen</option>
                            <option value="rank">Urut Peringkat (Ranking)</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-800 text-white uppercase tracking-wider text-[10px]">
                                <th class="py-2.5 px-3 text-center">Absen</th>
                                <th class="py-2.5 px-3">Username</th>
                                <th class="py-2.5 px-3">Nama Siswa</th>
                                <th class="py-2.5 px-2 text-center">JK</th>
                                <th class="py-2.5 px-3 text-center bg-emerald-900">Rank</th>
                                <th class="py-2.5 px-3 text-center">Total</th>
                                <th class="py-2.5 px-3 text-center">Rata-Rata</th>
                                <th class="py-2.5 px-3 text-center">Mapel Tuntas</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="st in filteredStudents()" :key="st.username">
                                <tr class="hover:bg-slate-50 transition" :class="st.completed_count < 17 ? 'bg-amber-50/60' : ''">
                                    <td class="py-2.5 px-3 text-center font-semibold" x-text="st.absen"></td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-emerald-700" x-text="st.username"></td>
                                    <td class="py-2.5 px-3 font-bold text-slate-800" x-text="st.nama"></td>
                                    <td class="py-2.5 px-2 text-center" x-text="st.jk"></td>
                                    <td class="py-2.5 px-3 text-center font-extrabold text-emerald-800 bg-emerald-50/60" x-text="st.rank ? '#' + st.rank : '-'"></td>
                                    <td class="py-2.5 px-3 text-center font-semibold" x-text="st.total"></td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full font-bold"
                                              :class="st.avg >= db.meta.class_overall_avg ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'"
                                              x-text="st.avg.toFixed(2)"></span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold"
                                              :class="st.completed_count === 17 ? 'text-emerald-700 bg-emerald-50' : 'text-amber-800 bg-amber-200/70'"
                                              x-text="st.completed_count + '/17 Mapel'"></span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center space-x-1">
                                        <button @click="viewStudentReport(st)" class="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                                            Lihat / Cetak
                                        </button>
                                        <button @click="openEditModal(st)" class="px-2.5 py-1 rounded bg-slate-700 hover:bg-slate-800 text-white font-semibold">
                                            Edit Nilai
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= 3. MODE RAPORT INDIVIDU SISWA (TAMPILAN LAYAR & CETAK A4) ================= -->
        <template x-if="mode === 'student' && currentStudent">
            <div class="max-w-4xl mx-auto">
                <!-- Bar Navigasi Cepat khusus jika Wali Kelas sedang membuka raport siswa -->
                <div x-show="isWalas" class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 flex flex-wrap items-center justify-between gap-2 no-print">
                    <div class="text-xs text-amber-900 font-semibold">
                        👁️ Mode Pratinjau Wali Kelas • Peringkat Kelas Siswa Ini:
                        <span class="font-extrabold underline" x-text="currentStudent.rank ? 'Peringkat ' + currentStudent.rank + ' dari 33 Siswa' : 'Belum Ada Nilai'"></span>
                        <span class="text-amber-700 font-normal">(Angka peringkat ini disembunyikan dari layar siswa/wali murid)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openEditModal(currentStudent)" class="px-3 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold">
                            ✏️ Edit Nilai / Catatan Siswa Ini
                        </button>
                        <button @click="mode = 'walas'" class="px-3 py-1 rounded-lg bg-slate-800 text-white text-xs font-semibold">
                            Kembali ke Tabel Kelas
                        </button>
                    </div>
                </div>

                <!-- LEMBAR RAPORT RESMI -->
                <div class="raport-sheet bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-10 print:p-0">
                    <!-- KOP MADRASAH -->
                    <div class="border-b-4 border-double border-emerald-900 pb-3 mb-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-800">Lembaga Pendidikan Ma'arif NU</p>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight" x-text="db.meta.madrasah"></h1>
                            <p class="text-xs text-slate-600" x-text="db.meta.alamat"></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-lg bg-emerald-900 text-white font-bold text-xs uppercase tracking-wider">
                                Raport Mandiri Wali Kelas
                            </span>
                            <p class="text-[11px] text-slate-500 mt-1 font-semibold">Sumatif Tengah Semester (STS) Ganjil 2026/2027</p>
                        </div>
                    </div>

                    <!-- IDENTITAS SISWA -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200 mb-4 text-xs">
                        <div class="space-y-1">
                            <div class="flex"><span class="w-28 text-slate-500 font-medium">Nama Lengkap</span><span class="font-bold text-slate-900">: <span x-text="currentStudent.nama"></span></span></div>
                            <div class="flex"><span class="w-28 text-slate-500 font-medium">Username STS</span><span class="font-mono font-bold text-emerald-800">: <span x-text="currentStudent.username"></span></span></div>
                            <div class="flex"><span class="w-28 text-slate-500 font-medium">Nomor Urut / JK</span><span class="font-semibold text-slate-800">: Absen <span x-text="currentStudent.absen"></span> (<span x-text="currentStudent.jk === 'L' ? 'Laki-laki' : 'Perempuan'"></span>)</span></div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex"><span class="w-28 text-slate-500 font-medium">Kelas / Program</span><span class="font-bold text-slate-900">: <span x-text="db.meta.kelas"></span></span></div>
                            <div class="flex"><span class="w-28 text-slate-500 font-medium">Semester / T.A.</span><span class="font-semibold text-slate-800">: <span x-text="db.meta.semester + ' / ' + db.meta.tahun_ajaran"></span></span></div>
                            <div class="flex"><span class="w-28 text-slate-500 font-medium">Wali Kelas</span><span class="font-bold text-emerald-900">: <span x-text="db.meta.wali_kelas"></span></span></div>
                        </div>
                    </div>

                    <!-- PERINGATAN NILAI SUSULAN JIKA ADA -->
                    <template x-if="currentStudent.missing_subjects && currentStudent.missing_subjects.length > 0">
                        <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-start gap-2.5">
                            <span class="text-base leading-none">⚠️</span>
                            <div>
                                <p class="font-bold">Pemberitahuan Nilai Susulan / Belum Terinput:</p>
                                <p class="mt-0.5">Terdapat <strong x-text="currentStudent.missing_subjects.length"></strong> mata pelajaran yang belum memiliki nilai STS: <span class="font-semibold underline" x-text="currentStudent.missing_subjects.join(', ')"></span>. Mohon ananda segera menghubungi guru pengampu mata pelajaran terkait.</p>
                            </div>
                        </div>
                    </template>

                    <!-- KARTU RINGKASAN EKSEKUTIF -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-5">
                        <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Rata-Rata Ananda</p>
                            <p class="text-xl font-extrabold text-emerald-950 mt-0.5" x-text="currentStudent.avg.toFixed(2)"></p>
                            <p class="text-[10px] text-emerald-700">Total Skor: <strong x-text="currentStudent.total"></strong></p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Rata-Rata Kelas X.13</p>
                            <p class="text-xl font-extrabold text-slate-800 mt-0.5" x-text="db.meta.class_overall_avg"></p>
                            <p class="text-[10px] font-semibold" :class="currentStudent.avg >= db.meta.class_overall_avg ? 'text-emerald-700' : 'text-amber-700'"
                               x-text="currentStudent.avg >= db.meta.class_overall_avg ? 'Di Atas Rata-Rata Kelas' : 'Perlu Ditingkatkan Lagi'"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Melampaui Rata2 Kelas</p>
                            <p class="text-xl font-extrabold text-teal-800 mt-0.5"><span x-text="currentStudent.above_class_avg_count"></span> <span class="text-xs font-semibold text-slate-500">/ <span x-text="currentStudent.completed_count"></span> Mapel</span></p>
                            <p class="text-[10px] text-slate-500">Posisi Relatif per Mapel</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Capaian Tertinggi</p>
                            <p class="text-xs font-extrabold text-slate-900 mt-1 truncate" x-text="currentStudent.top_subjects.length ? currentStudent.top_subjects[0].name : '-'"></p>
                            <p class="text-[11px] font-bold text-emerald-700" x-text="currentStudent.top_subjects.length ? 'Nilai: ' + currentStudent.top_subjects[0].score : '-'"></p>
                        </div>
                    </div>

                    <!-- TABEL NILAI 17 MATA PELAJARAN -->
                    <div class="overflow-x-auto mb-5">
                        <table class="w-full text-left border-collapse text-xs border border-slate-300">
                            <thead>
                                <tr class="bg-emerald-950 text-white text-[10px] uppercase tracking-wider">
                                    <th class="py-2 px-2.5 border border-slate-300 text-center w-8">No</th>
                                    <th class="py-2 px-3 border border-slate-300">Mata Pelajaran</th>
                                    <th class="py-2 px-2.5 border border-slate-300 text-center w-20">Nilai Ananda</th>
                                    <th class="py-2 px-2.5 border border-slate-300 text-center w-20">Rata-Rata Kelas</th>
                                    <th class="py-2 px-2.5 border border-slate-300 text-center w-20">Tertinggi Kelas</th>
                                    <th class="py-2 px-3 border border-slate-300 w-44">Visualisasi & Posisi Relatif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(groupName, gIdx) in ['Rumpun Keagamaan (KAG)', 'Rumpun Umum & Sains']" :key="groupName">
                                    <ReactFragment>
                                        <tr class="bg-slate-200/80 font-extrabold text-slate-800 text-[11px]">
                                            <td colspan="6" class="py-1.5 px-3 border border-slate-300" x-text="(gIdx === 0 ? 'A. ' : 'B. ') + groupName"></td>
                                        </tr>
                                        <template x-for="(sub, sIdx) in getSubjectsByGroup(groupName)" :key="sub.code">
                                            <tr class="border-b border-slate-200 hover:bg-slate-50">
                                                <td class="py-1.5 px-2.5 border border-slate-300 text-center font-medium text-slate-600" x-text="sIdx + 1"></td>
                                                <td class="py-1.5 px-3 border border-slate-300 font-semibold text-slate-800" x-text="sub.name"></td>
                                                <td class="py-1.5 px-2.5 border border-slate-300 text-center font-extrabold text-sm"
                                                    :class="getScoreColorClass(currentStudent.scores[sub.code], sub.avg)"
                                                    x-text="currentStudent.scores[sub.code] !== null ? currentStudent.scores[sub.code] : 'Kosong'">
                                                </td>
                                                <td class="py-1.5 px-2.5 border border-slate-300 text-center text-slate-600 font-medium" x-text="sub.avg.toFixed(1)"></td>
                                                <td class="py-1.5 px-2.5 border border-slate-300 text-center text-slate-500" x-text="sub.max"></td>
                                                <td class="py-1.5 px-3 border border-slate-300">
                                                    <template x-if="currentStudent.scores[sub.code] !== null">
                                                        <div>
                                                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                                                <div class="h-2 rounded-full"
                                                                     :class="currentStudent.scores[sub.code] >= sub.avg ? 'bg-emerald-600' : 'bg-amber-500'"
                                                                     :style="'width: ' + Math.min(100, currentStudent.scores[sub.code]) + '%'"></div>
                                                            </div>
                                                            <div class="flex justify-between text-[10px] mt-0.5 font-semibold">
                                                                <span :class="currentStudent.scores[sub.code] >= sub.avg ? 'text-emerald-700' : 'text-amber-700'"
                                                                      x-text="currentStudent.scores[sub.code] >= sub.avg ? '✓ Di Atas Rata2 Kelas' : 'Perlu Penguatan'"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="currentStudent.scores[sub.code] === null">
                                                        <span class="text-[10px] font-bold text-rose-600">Menunggu Nilai Susulan</span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </ReactFragment>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- KOTAK CATATAN & EVALUASI WALI KELAS -->
                    <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-300 mb-6">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-900 mb-1 flex items-center gap-1.5">
                            <span>📌</span> Catatan & Motivasi Wali Kelas
                        </h3>
                        <p class="text-xs text-slate-800 leading-relaxed italic" x-text="currentStudent.catatan_walas"></p>
                    </div>

                    <!-- BLOK TANDA TANGAN -->
                    <div class="grid grid-cols-2 gap-8 text-xs text-center pt-2">
                        <div>
                            <p class="text-slate-600">Mengetahui,</p>
                            <p class="font-bold text-slate-800">Orang Tua / Wali Siswa</p>
                            <div class="h-16"></div>
                            <p class="font-semibold text-slate-700">( ..................................................... )</p>
                        </div>
                        <div>
                            <p class="text-slate-600">Lamongan, <span x-text="db.meta.tanggal_cetak"></span></p>
                            <p class="font-bold text-slate-800">Wali Kelas X.13</p>
                            <div class="h-16"></div>
                            <p class="font-extrabold text-slate-900 underline" x-text="db.meta.wali_kelas"></p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ================= 4. MODE CETAK MASSAL 33 SISWA SEKALIGUS (A4 PAGE-BREAK) ================= -->
        <template x-if="mode === 'print_all'">
            <div class="space-y-8">
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 flex items-center justify-between no-print">
                    <div class="text-xs text-amber-900">
                        <strong>Mode Cetak Massal 33 Siswa Aktif:</strong> Tekan tombol <strong>"Cetak Semua (33 Hal A4)"</strong> di kanan atas atau tekan <code>Ctrl + P</code>. Setiap siswa otomatis terpisah menjadi 1 halaman A4 utuh.
                    </div>
                    <button @click="mode = 'walas'" class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-xs font-bold">← Kembali ke Dashboard</button>
                </div>

                <template x-for="st in db.students" :key="st.username">
                    <div class="raport-sheet page-break bg-white rounded-2xl shadow border border-slate-200 p-8 print:p-0">
                        <!-- KOP -->
                        <div class="border-b-4 border-double border-emerald-900 pb-2.5 mb-3 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-800">Lembaga Pendidikan Ma'arif NU</p>
                                <h1 class="text-lg font-extrabold text-slate-900" x-text="db.meta.madrasah"></h1>
                                <p class="text-[11px] text-slate-600" x-text="db.meta.alamat"></p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2.5 py-0.5 rounded bg-emerald-900 text-white font-bold text-[10px] uppercase">Raport Mandiri STS Ganjil 2026/2027</span>
                            </div>
                        </div>

                        <!-- IDENTITAS -->
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-lg border border-slate-200 mb-3 text-[11px]">
                            <div>
                                <p>Nama Siswa: <strong x-text="st.nama"></strong></p>
                                <p>Username STS: <strong class="font-mono text-emerald-800" x-text="st.username"></strong> (Absen <span x-text="st.absen"></span>)</p>
                            </div>
                            <div class="text-right">
                                <p>Kelas: <strong x-text="db.meta.kelas"></strong></p>
                                <p>Rata-Rata Siswa: <strong x-text="st.avg.toFixed(2)"></strong> (Rata2 Kelas: <span x-text="db.meta.class_overall_avg"></span>)</p>
                            </div>
                        </div>

                        <!-- TABEL RINGKAS 17 MAPEL -->
                        <table class="w-full text-left border-collapse text-[11px] border border-slate-300 mb-3">
                            <thead>
                                <tr class="bg-emerald-950 text-white text-[10px] uppercase">
                                    <th class="py-1 px-2 border border-slate-300 text-center w-8">No</th>
                                    <th class="py-1 px-2.5 border border-slate-300">Mata Pelajaran</th>
                                    <th class="py-1 px-2 border border-slate-300 text-center w-20">Nilai Siswa</th>
                                    <th class="py-1 px-2 border border-slate-300 text-center w-20">Rata2 Kelas</th>
                                    <th class="py-1 px-2 border border-slate-300 text-center w-20">Tertinggi</th>
                                    <th class="py-1 px-2.5 border border-slate-300">Keterangan Capaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(sub, idx) in db.subjects" :key="sub.code">
                                    <tr class="border-b border-slate-200">
                                        <td class="py-1 px-2 border border-slate-300 text-center" x-text="idx + 1"></td>
                                        <td class="py-1 px-2.5 border border-slate-300 font-semibold" x-text="sub.name"></td>
                                        <td class="py-1 px-2 border border-slate-300 text-center font-bold" x-text="st.scores[sub.code] !== null ? st.scores[sub.code] : 'Kosong'"></td>
                                        <td class="py-1 px-2 border border-slate-300 text-center" x-text="sub.avg.toFixed(1)"></td>
                                        <td class="py-1 px-2 border border-slate-300 text-center" x-text="sub.max"></td>
                                        <td class="py-1 px-2.5 border border-slate-300 text-[10px] font-semibold"
                                            x-text="st.scores[sub.code] === null ? 'Menunggu Nilai Susulan' : (st.scores[sub.code] >= sub.avg ? '✓ Melampaui Rata-Rata Kelas' : 'Perlu Penguatan Menuju SAS')"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- CATATAN WALAS -->
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-300 mb-4 text-[11px]">
                            <p class="font-bold text-emerald-950 mb-0.5">Catatan & Evaluasi Wali Kelas:</p>
                            <p class="italic text-slate-800" x-text="st.catatan_walas"></p>
                        </div>

                        <!-- TTD -->
                        <div class="grid grid-cols-2 gap-8 text-[11px] text-center">
                            <div>
                                <p>Orang Tua / Wali Siswa</p>
                                <div class="h-14"></div>
                                <p>( ..................................................... )</p>
                            </div>
                            <div>
                                <p>Lamongan, <span x-text="db.meta.tanggal_cetak"></span> • Wali Kelas X.13</p>
                                <div class="h-14"></div>
                                <p class="font-bold underline" x-text="db.meta.wali_kelas"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

    </main>

    <!-- MODAL LOGIN WALI KELAS -->
    <div x-show="showPinModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 no-print">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200" @click.outside="showPinModal = false">
            <h3 class="text-base font-extrabold text-slate-800">Akses Mode Wali Kelas</h3>
            <p class="text-xs text-slate-500 mt-1">Masukkan PIN Wali Kelas untuk membuka tabel rekapitulasi, peringkat, dan editor nilai.</p>
            <form @submit.prevent="verifyPin()" class="mt-4 space-y-3">
                <input type="password" x-model="pinInput" placeholder="Masukkan PIN (Default: 1313)"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-center font-bold tracking-widest text-sm focus:border-emerald-600" autofocus>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showPinModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 text-white text-xs font-bold">Buka Dashboard</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT NILAI & CATATAN SISWA -->
    <div x-show="editingStudent !== null" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto no-print">
        <template x-if="editingStudent">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 my-8 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800" x-text="'Edit Data: ' + editingStudent.nama"></h3>
                        <p class="text-xs text-slate-500" x-text="'Username: ' + editingStudent.username + ' • Absen ' + editingStudent.absen"></p>
                    </div>
                    <button @click="editingStudent = null" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                    <template x-for="sub in db.subjects" :key="sub.code">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 truncate" x-text="sub.name"></label>
                            <input type="number" step="0.01" min="0" max="100"
                                   x-model="editingStudent.scores[sub.code]"
                                   placeholder="Kosong"
                                   class="w-full mt-1 px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold">
                        </div>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan & Motivasi Wali Kelas:</label>
                    <textarea x-model="editingStudent.catatan_walas" rows="3"
                              class="w-full p-2.5 rounded-xl border border-slate-300 text-xs leading-relaxed"></textarea>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                    <span class="text-[11px] text-slate-500">*Rata-rata & peringkat akan dihitung ulang otomatis.</span>
                    <div class="flex gap-2">
                        <button @click="editingStudent = null" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold">Tutup</button>
                        <button @click="applyStudentEdit()" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Terapkan Perubahan</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- FOOTER (NON-PRINT) -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 no-print mt-8">
        <p>Sistem Informasi Raport Mandiri Kelas X.13 • <strong>MA Matholi'ul Anwar Lamongan</strong></p>
        <p class="mt-0.5">Wali Kelas: <strong class="text-slate-700" x-text="db.meta.wali_kelas"></strong></p>
    </footer>

    <script>
        const INITIAL_DATA = <?php echo $initial_json; ?>;

        function raportApp() {
            return {
                db: INITIAL_DATA,
                mode: 'home', // 'home' | 'student' | 'walas' | 'print_all'
                searchUsername: '',
                loginError: '',
                currentStudent: null,
                isWalas: false,
                showPinModal: false,
                pinInput: '',
                walasFilter: '',
                walasSort: 'absen',
                editingStudent: null,
                isSaving: false,
                toast: { show: false, message: '', type: 'success' },

                initApp() {
                    const params = new URLSearchParams(window.location.search);
                    const u = params.get('u');
                    if (u) {
                        this.searchUsername = u;
                        this.loginStudent();
                    }
                },

                showToast(msg, type = 'success') {
                    this.toast = { show: true, message: msg, type };
                    setTimeout(() => { this.toast.show = false; }, 4000);
                },

                backToHome() {
                    if (this.isWalas && this.mode === 'student') {
                        this.mode = 'walas';
                    } else {
                        this.mode = 'home';
                        this.currentStudent = null;
                        this.loginError = '';
                    }
                },

                loginStudent() {
                    this.loginError = '';
                    const clean = this.searchUsername.trim().toUpperCase();
                    const found = this.db.students.find(s => s.username.toUpperCase() === clean);
                    if (found) {
                        this.currentStudent = found;
                        this.mode = 'student';
                    } else {
                        this.loginError = 'Username "' + clean + '" tidak ditemukan di Kelas X.13. Pastikan penulisan sesuai kartu STS (contoh: L11MA10410).';
                    }
                },

                openWalasModal() {
                    if (this.isWalas) {
                        this.mode = 'walas';
                    } else {
                        this.pinInput = '';
                        this.showPinModal = true;
                    }
                },

                verifyPin() {
                    const validPin = this.db.meta.pin_walas || '1313';
                    if (this.pinInput === validPin) {
                        this.isWalas = true;
                        this.showPinModal = false;
                        this.mode = 'walas';
                        this.showToast('Berhasil masuk ke Dashboard Wali Kelas');
                    } else {
                        this.showToast('PIN Wali Kelas tidak tepat!', 'error');
                    }
                },

                filteredStudents() {
                    let list = [...this.db.students];
                    if (this.walasFilter.trim() !== '') {
                        const q = this.walasFilter.toLowerCase();
                        list = list.filter(s => s.nama.toLowerCase().includes(q) || s.username.toLowerCase().includes(q));
                    }
                    if (this.walasSort === 'rank') {
                        list.sort((a, b) => (a.rank || 999) - (b.rank || 999));
                    } else {
                        list.sort((a, b) => a.absen - b.absen);
                    }
                    return list;
                },

                viewStudentReport(st) {
                    this.currentStudent = st;
                    this.mode = 'student';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                getSubjectsByGroup(groupName) {
                    return this.db.subjects.filter(s => s.group === groupName);
                },

                getScoreColorClass(score, avg) {
                    if (score === null || score === undefined || score === '') return 'text-rose-600 bg-rose-50';
                    if (score >= avg) return 'text-emerald-800 bg-emerald-50/70';
                    return 'text-amber-800 bg-amber-50/50';
                },

                openEditModal(st) {
                    this.editingStudent = JSON.parse(JSON.stringify(st));
                },

                applyStudentEdit() {
                    const idx = this.db.students.findIndex(s => s.username === this.editingStudent.username);
                    if (idx !== -1) {
                        // Normalize scores
                        for (const sub of this.db.subjects) {
                            const raw = this.editingStudent.scores[sub.code];
                            this.editingStudent.scores[sub.code] = (raw === '' || raw === null || isNaN(Number(raw))) ? null : Number(raw);
                        }
                        this.db.students[idx] = this.editingStudent;
                        this.recalculateAllMetrics();
                        if (this.currentStudent && this.currentStudent.username === this.editingStudent.username) {
                            this.currentStudent = this.db.students[idx];
                        }
                        this.editingStudent = null;
                        this.showToast('Perubahan nilai diterapkan. Klik "Simpan ke Server" untuk mempermanenkan.');
                    }
                },

                recalculateAllMetrics() {
                    // 1. Recalculate subject stats
                    for (const sub of this.db.subjects) {
                        const vals = this.db.students.map(s => s.scores[sub.code]).filter(v => v !== null && !isNaN(v));
                        if (vals.length > 0) {
                            const sum = vals.reduce((a, b) => a + b, 0);
                            sub.avg = Number((sum / vals.length).toFixed(2));
                            sub.min = Math.min(...vals);
                            sub.max = Math.max(...vals);
                            sub.count = vals.length;
                        }
                    }

                    // 2. Recalculate student totals & averages
                    for (const st of this.db.students) {
                        const valid = [];
                        const missing = [];
                        for (const sub of this.db.subjects) {
                            const val = st.scores[sub.code];
                            if (val !== null && !isNaN(val)) {
                                valid.push({ code: sub.code, name: sub.name, score: val, avg: sub.avg });
                            } else {
                                missing.push(sub.name);
                            }
                        }
                        st.missing_subjects = missing;
                        st.completed_count = valid.length;
                        if (valid.length > 0) {
                            const total = valid.reduce((a, b) => a + b.score, 0);
                            st.total = Number(total.toFixed(2));
                            st.avg = Number((total / valid.length).toFixed(2));
                            st.above_class_avg_count = valid.filter(v => v.score >= v.avg).length;
                            const desc = [...valid].sort((a, b) => b.score - a.score);
                            st.top_subjects = desc.slice(0, 3);
                            const asc = [...valid].sort((a, b) => a.score - b.score);
                            st.priority_subjects = asc.slice(0, 3);
                        } else {
                            st.total = 0;
                            st.avg = 0;
                            st.above_class_avg_count = 0;
                            st.top_subjects = [];
                            st.priority_subjects = [];
                        }
                    }

                    // 3. Recalculate ranks
                    const ranked = [...this.db.students].sort((a, b) => (b.avg - a.avg) || (b.total - a.total));
                    ranked.forEach((item, i) => {
                        item.rank = item.completed_count > 0 ? (i + 1) : null;
                    });

                    // 4. Class overall average
                    const activeAvgs = this.db.students.filter(s => s.completed_count > 0).map(s => s.avg);
                    if (activeAvgs.length > 0) {
                        this.db.meta.class_overall_avg = Number((activeAvgs.reduce((a, b) => a + b, 0) / activeAvgs.length).toFixed(2));
                    }
                },

                async saveToServer() {
                    this.isSaving = true;
                    try {
                        const res = await fetch('?action=save', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                pin: this.pinInput || this.db.meta.pin_walas,
                                data: this.db
                            })
                        });
                        const result = await res.json();
                        if (res.ok && result.status === 'success') {
                            this.showToast(result.message, 'success');
                        } else {
                            this.showToast(result.message || 'Gagal menyimpan ke server.', 'error');
                        }
                    } catch (e) {
                        this.showToast('Gagal menghubungi server PHP. Gunakan tombol Unduh JSON jika sedang mode offline.', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                downloadJson() {
                    const blob = new Blob([JSON.stringify(this.db, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'data_x13.json';
                    a.click();
                    URL.revokeObjectURL(url);
                }
            };
        }
    </script>
</body>
</html>
