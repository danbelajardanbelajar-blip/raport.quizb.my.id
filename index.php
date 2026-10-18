<?php
/**
 * PORTAL RAPORT MANDIRI STS GANJIL 2026/2027 - KELAS X.13
 * MA MATHOLI'UL ANWAR LAMONGAN
 * Wali Kelas: Dr. Zainul Hakim, M.H.I.
 * Arsitektur: Paket B (index.php + data_x13.json)
 */

$json_file = __DIR__ . '/data_x13.json';

// Endpoint API untuk Simpan Perubahan Data dari Mode Dashboard Wali Kelas
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save') {
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
                margin: 8mm 10mm;
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
            .break-inside-avoid {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
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

            <div class="flex items-center gap-2 flex-wrap justify-end">
                <template x-if="mode !== 'home'">
                    <button @click="backToHome()" class="px-3 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-700 text-xs font-semibold border border-emerald-600 transition flex items-center gap-1.5">
                        <span>←</span> Beranda / Cari Siswa
                    </button>
                </template>

                <!-- Tombol Navigasi Statistik Mapel -->
                <button @click="openSubjectList()" class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition flex items-center gap-1.5"
                        :class="(mode === 'subjects_list' || mode === 'subject_detail') ? 'bg-amber-400 text-slate-950 font-bold border-amber-300 shadow' : 'bg-emerald-800 hover:bg-emerald-700 text-white border-emerald-600'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>📊 Statistik Mapel</span>
                </button>

                <!-- Tombol Cetak Sesuai Mode Aktif -->
                <template x-if="mode === 'student' || mode === 'print_all'">
                    <button @click="window.print()" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span x-text="mode === 'print_all' ? 'Cetak Semua (' + db.students.length + ' Hal A4)' : 'Cetak / Simpan PDF'"></span>
                    </button>
                </template>

                <template x-if="mode === 'subject_detail'">
                    <button @click="window.print()" class="px-3.5 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-bold shadow transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak Laporan Mapel</span>
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

            <!-- BANNER SHORTCUT: STATISTIK & LAPORAN MATA PELAJARAN -->
            <div class="mt-5 bg-white rounded-2xl p-5 shadow-lg border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl shrink-0 shadow-sm border border-emerald-200">
                        📊
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-sm text-slate-800">Statistik 17 Mata Pelajaran</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Laporan Guru</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Analisis komprehensif, grafik distribusi skor, dan lembar pelaporan resmi untuk para guru pengajar.</p>
                    </div>
                </div>
                <button @click="openSubjectList()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow transition flex items-center justify-center gap-2 shrink-0">
                    <span>Lihat Statistik Mapel</span>
                    <span>→</span>
                </button>
            </div>
        </div>

        <!-- ================= 2. MODE DASHBOARD WALI KELAS ================= -->
        <div x-show="mode === 'walas'" x-cloak class="space-y-6 no-print">
            <!-- Header Dashboard -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Mode Administrator Wali Kelas</span>
                    <h2 class="text-xl font-extrabold text-slate-800 mt-1">Dashboard Evaluasi & Manajemen Raport Kelas X.13</h2>
                    <p class="text-xs text-slate-500">Anda dapat melihat peringkat lengkap, mengedit nilai susulan, mengubah catatan wali kelas, atau mencetak <span x-text="db.students.length"></span> raport sekaligus.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="openSubjectList()" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow transition flex items-center gap-1.5">
                        📊 Analisis & Laporan Per Mapel
                    </button>
                    <button @click="saveToServer()" :disabled="isSaving" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                        <span x-text="isSaving ? 'Menyimpan...' : '💾 Simpan ke Server (JSON)'"></span>
                    </button>
                    <button @click="downloadJson()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                        ⬇️ Unduh data_x13.json
                    </button>
                    <button @click="mode = 'print_all'" class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold shadow transition">
                        🖨️ Cetak Massal (<span x-text="db.students.length"></span> Siswa)
                    </button>
                </div>
            </div>

            <!-- Statistik Ringkas Kelas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">Rata-Rata Kelas X.13</p>
                    <p class="text-2xl font-extrabold text-emerald-700 mt-1" x-text="db.meta.class_overall_avg"></p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dari <span x-text="db.students.filter(s => s.completed_count > 0).length"></span> siswa aktif mengikuti STS</p>
                </div>
                <div @click="openSubjectDetail('KALAM')" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm cursor-pointer hover:border-emerald-500 hover:shadow-md transition group">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500">Mapel Rata-Rata Tertinggi</p>
                        <span class="text-xs text-emerald-600 group-hover:translate-x-1 transition font-bold">→</span>
                    </div>
                    <p class="text-sm font-extrabold text-slate-800 mt-1">Ilmu Kalam & Akhlak (86.50)</p>
                    <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Buka analisis & laporan guru ↗</p>
                </div>
                <div @click="openSubjectDetail('MTK')" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm cursor-pointer hover:border-rose-400 hover:shadow-md transition group">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500">Mapel Perlu Evaluasi Bersama</p>
                        <span class="text-xs text-rose-600 group-hover:translate-x-1 transition font-bold">→</span>
                    </div>
                    <p class="text-sm font-extrabold text-rose-700 mt-1">Matematika (24.06)</p>
                    <p class="text-[11px] text-rose-500 font-medium mt-0.5">Buka analisis & daftar remedial ↗</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-amber-200 bg-amber-50/40 shadow-sm">
                    <p class="text-xs font-semibold text-amber-800">Perhatian Nilai Kosong / Susulan</p>
                    <p class="text-sm font-extrabold text-amber-900 mt-1">1 Siswa Belum Lengkap</p>
                    <p class="text-[11px] text-amber-700 mt-0.5">Nur Farihatul Laili (2 mapel susulan)</p>
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
                        <span class="font-extrabold underline" x-text="currentStudent.rank ? 'Peringkat ' + currentStudent.rank + ' dari ' + db.students.length + ' Siswa' : 'Belum Ada Nilai'"></span>
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

        <!-- ================= 4. MODE CETAK MASSAL SISWA (A4 PAGE-BREAK) ================= -->
        <template x-if="mode === 'print_all'">
            <div class="space-y-8">
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 flex items-center justify-between no-print">
                    <div class="text-xs text-amber-900">
                        <strong>Mode Cetak Massal <span x-text="db.students.length"></span> Siswa Aktif:</strong> Tekan tombol <strong>"Cetak Semua (<span x-text="db.students.length"></span> Hal A4)"</strong> di kanan atas atau tekan <code>Ctrl + P</code>. Setiap siswa otomatis terpisah menjadi 1 halaman A4 utuh.
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

        <!-- ================= 5. MODE LIST SELURUH MATA PELAJARAN ================= -->
        <div x-show="mode === 'subjects_list'" x-cloak class="space-y-6 no-print">
            <!-- Header Direktori Mapel -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Portal Evaluasi Akademik</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">STS Ganjil 2026/2027</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">Direktori & Laporan Statistik 17 Mata Pelajaran</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Pilih mata pelajaran untuk membuka analisis statistik mendalam, distribusi nilai, daftar remedial, dan lembar laporan resmi untuk guru pengampu.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="backToHome()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                            ← Kembali ke Beranda
                        </button>
                    </div>
                </div>

                <!-- Ringkasan Eksekutif Mapel -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-5 pt-5 border-t border-slate-100 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] text-slate-500 font-medium">Total Mata Pelajaran</p>
                        <p class="text-lg font-extrabold text-slate-800" x-text="db.subjects.length + ' Mapel'"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50/60 border border-emerald-200/70">
                        <p class="text-[11px] text-emerald-700 font-medium">Rumpun Keagamaan</p>
                        <p class="text-lg font-extrabold text-emerald-800" x-text="getSubjectsByGroup('Rumpun Keagamaan (KAG)').length + ' Mapel'"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-sky-50/60 border border-sky-200/70">
                        <p class="text-[11px] text-sky-700 font-medium">Rumpun Umum & Sains</p>
                        <p class="text-lg font-extrabold text-sky-800" x-text="getSubjectsByGroup('Rumpun Umum & Sains').length + ' Mapel'"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-200/70">
                        <p class="text-[11px] text-amber-700 font-medium">Rata-Rata Seluruh Mapel</p>
                        <p class="text-lg font-extrabold text-amber-800" x-text="db.meta.class_overall_avg"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-200/70 cursor-pointer hover:border-teal-400 transition" @click="openSubjectDetail('KALAM')">
                        <p class="text-[11px] text-teal-700 font-medium">Capaian Tertinggi</p>
                        <p class="text-sm font-extrabold text-teal-900 truncate">Kalam (86.50)</p>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50/60 border border-rose-200/70 cursor-pointer hover:border-rose-400 transition" @click="openSubjectDetail('MTK')">
                        <p class="text-[11px] text-rose-700 font-medium">Prioritas Evaluasi</p>
                        <p class="text-sm font-extrabold text-rose-900 truncate">MTK (24.06)</p>
                    </div>
                </div>
            </div>

            <!-- Filter, Pencarian & Pengurutan -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <!-- Tabs Rumpun -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                    <button @click="subjectFilterRumpun = 'all'"
                            class="px-3 py-1.5 rounded-lg transition"
                            :class="subjectFilterRumpun === 'all' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900'">
                        Semua Mapel (17)
                    </button>
                    <button @click="subjectFilterRumpun = 'Rumpun Keagamaan (KAG)'"
                            class="px-3 py-1.5 rounded-lg transition"
                            :class="subjectFilterRumpun === 'Rumpun Keagamaan (KAG)' ? 'bg-emerald-700 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-emerald-700'">
                        Keagamaan (8)
                    </button>
                    <button @click="subjectFilterRumpun = 'Rumpun Umum & Sains'"
                            class="px-3 py-1.5 rounded-lg transition"
                            :class="subjectFilterRumpun === 'Rumpun Umum & Sains' ? 'bg-sky-700 text-white shadow-sm font-bold' : 'text-slate-600 hover:text-sky-700'">
                        Umum & Sains (9)
                    </button>
                </div>

                <div class="flex items-center gap-2 flex-grow sm:flex-grow-0">
                    <!-- Search Input -->
                    <div class="relative flex-grow sm:w-60">
                        <input type="text" x-model="subjectSearch" placeholder="Cari nama mapel, kode, atau guru..."
                               class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <!-- Sort Select -->
                    <select x-model="subjectSort" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold bg-white text-slate-700">
                        <option value="default">Urutan Standar</option>
                        <option value="avg_desc">Rata-Rata Tertinggi</option>
                        <option value="avg_asc">Rata-Rata Terendah (Prioritas)</option>
                        <option value="tuntas_desc">Ketuntasan Tertinggi</option>
                    </select>
                </div>
            </div>

            <!-- Grid Kartu Mata Pelajaran (17 Mapel) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <template x-for="sub in getFilteredSubjectsList()" :key="sub.code">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 hover:shadow-md hover:border-emerald-400 transition flex flex-col justify-between group">
                        <div>
                            <!-- Header Card Mapel -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                      :class="sub.group.includes('Keagamaan') ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-sky-100 text-sky-800 border border-sky-200'"
                                      x-text="sub.group.includes('Keagamaan') ? 'Keagamaan (KAG)' : 'Umum & Sains'">
                                </span>
                                <span class="font-mono text-xs font-extrabold text-slate-500 bg-slate-100 px-2 py-0.5 rounded" x-text="sub.code"></span>
                            </div>

                            <h3 class="font-bold text-base text-slate-900 group-hover:text-emerald-800 transition line-clamp-1" x-text="sub.name" :title="sub.name"></h3>
                            <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-600">
                                <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="truncate font-medium text-slate-700" :title="'Guru Pengampu: ' + (sub.guru || 'Belum ditentukan')" x-text="sub.guru || 'Guru belum diisi'"></span>
                            </div>

                            <!-- Rata-Rata Nilai Utama -->
                            <div class="mt-4 p-3 rounded-xl flex items-center justify-between border"
                                 :class="sub.avg >= 75 ? 'bg-emerald-50/80 border-emerald-200 text-emerald-950' : (sub.avg >= 55 ? 'bg-amber-50/80 border-amber-200 text-amber-950' : 'bg-rose-50/80 border-rose-200 text-rose-950')">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Rata-Rata Kelas</p>
                                    <p class="text-2xl font-black mt-0.5" x-text="sub.avg.toFixed(2)"></p>
                                </div>
                                <div class="text-right text-xs">
                                    <span class="inline-block px-2 py-0.5 rounded font-bold text-[11px]"
                                          :class="sub.avg >= 75 ? 'bg-emerald-200/80 text-emerald-900' : (sub.avg >= 55 ? 'bg-amber-200/80 text-amber-900' : 'bg-rose-200/80 text-rose-900')"
                                          x-text="sub.avg >= 75 ? 'Optimal' : (sub.avg >= 55 ? 'Cukup' : 'Perlu Evaluasi')">
                                    </span>
                                    <p class="text-[10px] text-slate-500 mt-1 font-medium">Rentang: <span x-text="sub.min + ' - ' + sub.max"></span></p>
                                </div>
                            </div>

                            <!-- Statistik Pendukung -->
                            <div class="grid grid-cols-3 gap-2 text-center text-xs mt-3">
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-[10px] text-slate-500 font-medium">Tertinggi</p>
                                    <p class="font-extrabold text-emerald-700 text-sm" x-text="sub.max"></p>
                                </div>
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-[10px] text-slate-500 font-medium">Terendah</p>
                                    <p class="font-extrabold text-rose-600 text-sm" x-text="sub.min"></p>
                                </div>
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-100">
                                    <p class="text-[10px] text-slate-500 font-medium">Median</p>
                                    <p class="font-extrabold text-slate-700 text-sm" x-text="sub.median"></p>
                                </div>
                            </div>

                            <!-- Progress Ketuntasan -->
                            <div class="mt-3.5 pt-3 border-t border-slate-100">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-slate-500 font-medium">Peserta Dinilai:</span>
                                    <span class="font-bold text-slate-800" x-text="sub.count + '/' + db.students.length + ' Siswa'"></span>
                                </div>
                                <template x-if="sub.count < db.students.length">
                                    <p class="text-[10px] text-amber-700 font-semibold mb-1">
                                        ⚠️ Terdapat <span x-text="db.students.length - sub.count"></span> siswa belum memiliki nilai (susulan)
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2">
                            <button @click="openSubjectDetail(sub.code)"
                                    class="col-span-2 py-2 px-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-sm hover:shadow transition flex items-center justify-center gap-1.5">
                                <span>📊 Lihat Laporan Guru</span>
                                <span>→</span>
                            </button>
                            <a :href="'?mapel=' + encodeURIComponent(sub.code)" target="_blank"
                               title="Buka laporan di tab baru untuk dilaporkan ke guru pengajar"
                               class="py-2 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1 text-center">
                                <span>Tab Baru</span>
                                <span>↗</span>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ================= 6. MODE STATISTIK DETAIL MATA PELAJARAN (LAPORAN GURU) ================= -->
        <div x-show="mode === 'subject_detail' && activeSubjectStats" x-cloak class="space-y-6">
            <template x-if="activeSubjectStats">
                <div class="max-w-4xl mx-auto space-y-6">
                    <!-- TOOLBAR AKSI ATAS (NON-PRINT) -->
                    <div class="p-4 rounded-2xl bg-white shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3 no-print">
                        <div class="flex items-center gap-2 flex-wrap">
                            <button @click="openSubjectList()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1">
                                <span>←</span> Daftar Mapel
                            </button>
                            <div class="flex items-center gap-1 text-xs">
                                <span class="text-slate-500 font-medium">Pilih Mapel:</span>
                                <select x-model="currentSubjectCode" @change="openSubjectDetail(currentSubjectCode)"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-300 font-bold text-xs bg-white text-slate-800">
                                    <template x-for="s in db.subjects" :key="s.code">
                                        <option :value="s.code" x-text="s.name + ' (' + s.code + ')'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Interactive KKTP Slider & Action Buttons -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex items-center gap-2 px-3 py-1 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <span class="font-bold text-slate-600">KKTP:</span>
                                <input type="range" min="50" max="85" step="1" x-model.number="subjectKktp" @input="updateActiveSubjectStats()" class="w-20 accent-emerald-600 cursor-pointer">
                                <span class="font-extrabold text-emerald-800 px-1.5 py-0.5 rounded bg-emerald-100 text-xs" x-text="subjectKktp"></span>
                            </div>

                            <button @click="copySubjectReportWa()" class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow transition flex items-center gap-1.5" title="Salin ringkasan laporan siap kirim via WhatsApp">
                                <span>📋 Salin Ringkasan WA</span>
                            </button>
                            <button @click="exportSubjectCsv()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition" title="Unduh daftar nilai mapel ini dalam format CSV">
                                <span>📥 Ekspor CSV</span>
                            </button>
                            <button @click="window.print()" class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow transition flex items-center gap-1.5">
                                <span>🖨️ Cetak / Simpan PDF (A4)</span>
                            </button>
                        </div>
                    </div>

                    <!-- LEMBAR LAPORAN RESMI (PRINT & SCREEN) -->
                    <div class="raport-sheet bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-10 print:p-0 print:border-none print:shadow-none">
                        <!-- KOP MADRASAH RESMI -->
                        <div class="border-b-4 border-double border-emerald-900 pb-3 mb-5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-800">Lembaga Pendidikan Ma'arif NU</p>
                                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight" x-text="db.meta.madrasah"></h1>
                                <p class="text-xs text-slate-600" x-text="db.meta.alamat"></p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-lg bg-emerald-900 text-white font-bold text-xs uppercase tracking-wider">
                                    Laporan Evaluasi Mata Pelajaran
                                </span>
                                <p class="text-[11px] text-slate-500 mt-1 font-semibold">Sumatif Tengah Semester (STS) Ganjil 2026/2027</p>
                            </div>
                        </div>

                        <!-- IDENTITAS MATA PELAJARAN -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50 p-4 rounded-xl border border-slate-200 mb-5 text-xs">
                            <div class="space-y-1.5">
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Mata Pelajaran</span><span class="font-extrabold text-slate-900">: <span x-text="activeSubjectStats.subject.name"></span> (<span class="font-mono text-emerald-800" x-text="activeSubjectStats.subject.code"></span>)</span></div>
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Guru Pengampu</span><span class="font-bold text-emerald-900">: <span x-text="activeSubjectStats.subject.guru || 'Belum diisi'"></span></span></div>
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Kelompok Rumpun</span><span class="font-semibold text-slate-800">: <span x-text="activeSubjectStats.subject.group"></span></span></div>
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Kelas / Program</span><span class="font-bold text-slate-900">: <span x-text="db.meta.kelas"></span></span></div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Wali Kelas</span><span class="font-bold text-slate-900">: <span x-text="db.meta.wali_kelas"></span></span></div>
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Standar KKTP</span><span class="font-bold text-slate-800">: <span class="font-mono px-2 py-0.5 rounded bg-emerald-100 text-emerald-900" x-text="activeSubjectStats.threshold"></span> (Skala 0 - 100)</span></div>
                                <div class="flex"><span class="w-32 text-slate-500 font-medium">Waktu Evaluasi</span><span class="font-semibold text-slate-800">: <span x-text="db.meta.tanggal_cetak"></span> (STS Ganjil)</span></div>
                            </div>
                        </div>

                        <!-- NOTIFIKASI JIKA ADA SISWA SUSULAN -->
                        <template x-if="activeSubjectStats.missingStudents.length > 0">
                            <div class="mb-5 p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-start gap-2.5">
                                <span class="text-base leading-none">⚠️</span>
                                <div>
                                    <p class="font-bold">Pemberitahuan Siswa Belum Mengikuti Ujian STS / Nilai Belum Terinput:</p>
                                    <p class="mt-0.5">
                                        Terdapat <strong x-text="activeSubjectStats.missingStudents.length"></strong> siswa belum memiliki nilai untuk mata pelajaran ini:
                                        <span class="font-semibold underline" x-text="activeSubjectStats.missingStudents.map(s => s.nama + ' (Absen ' + s.absen + ')').join(', ')"></span>.
                                        Mohon Bapak/Ibu Guru Pengampu dapat menjadwalkan tes susulan atau pemenuhan tugas pengganti bersama wali kelas.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- 6 KARTU STATISTIK KUNCI (KPI CARDS) -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 mb-6">
                            <!-- 1. Rata-rata -->
                            <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-200 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Rata-Rata Kelas</p>
                                <p class="text-2xl font-black text-emerald-950 mt-0.5" x-text="activeSubjectStats.avg.toFixed(2)"></p>
                                <p class="text-[10px] font-semibold"
                                   :class="activeSubjectStats.avg >= db.meta.class_overall_avg ? 'text-emerald-700' : 'text-amber-700'"
                                   x-text="activeSubjectStats.avg >= db.meta.class_overall_avg ? '▲ Di Atas Mean Kelas' : '▼ Di Bawah Mean Kelas'"></p>
                            </div>

                            <!-- 2. Nilai Tertinggi -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Nilai Tertinggi</p>
                                <p class="text-2xl font-black text-emerald-700 mt-0.5" x-text="activeSubjectStats.max"></p>
                                <p class="text-[10px] text-slate-600 font-semibold truncate"
                                   x-text="activeSubjectStats.topPerformers.length > 0 ? activeSubjectStats.topPerformers[0].nama : '-'"></p>
                            </div>

                            <!-- 3. Nilai Terendah -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Nilai Terendah</p>
                                <p class="text-2xl font-black text-rose-600 mt-0.5" x-text="activeSubjectStats.min"></p>
                                <p class="text-[10px] text-slate-500">Batas Minimal: 0</p>
                            </div>

                            <!-- 4. Median & Rentang -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Median (Nilai Tengah)</p>
                                <p class="text-2xl font-black text-slate-800 mt-0.5" x-text="activeSubjectStats.median"></p>
                                <p class="text-[10px] text-slate-500">Rentang (Range): <strong x-text="activeSubjectStats.range"></strong></p>
                            </div>

                            <!-- 5. Standar Deviasi -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Standar Deviasi (σ)</p>
                                <p class="text-2xl font-black text-teal-800 mt-0.5" x-text="activeSubjectStats.stdDev.toFixed(2)"></p>
                                <p class="text-[10px] text-slate-500">Variasi Sebaran Skor</p>
                            </div>

                            <!-- 6. Ketuntasan Belajar -->
                            <div class="p-3 rounded-xl border text-center"
                                 :class="activeSubjectStats.tuntasPercentage >= 75 ? 'bg-emerald-50/80 border-emerald-300' : (activeSubjectStats.tuntasPercentage >= 50 ? 'bg-amber-50/80 border-amber-300' : 'bg-rose-50/80 border-rose-300')">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Ketuntasan (≥ <span x-text="activeSubjectStats.threshold"></span>)</p>
                                <p class="text-2xl font-black mt-0.5"
                                   :class="activeSubjectStats.tuntasPercentage >= 75 ? 'text-emerald-900' : (activeSubjectStats.tuntasPercentage >= 50 ? 'text-amber-900' : 'text-rose-900')"
                                   x-text="activeSubjectStats.tuntasPercentage + '%'"></p>
                                <p class="text-[10px] font-semibold text-slate-700" x-text="activeSubjectStats.tuntasCount + ' Tuntas / ' + activeSubjectStats.belumTuntasCount + ' Remed'"></p>
                            </div>
                        </div>

                        <!-- VISUALISASI GRAFIK DISTRIBUSI FREKUENSI NILAI (HISTOGRAM BAR CHART) -->
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 mb-6 break-inside-avoid">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                    <span>📊</span> Distribusi Frekuensi Nilai Siswa (5 Interval Predikat)
                                </h3>
                                <span class="text-[11px] text-slate-500 font-medium">Berdasarkan total <strong x-text="activeSubjectStats.testedCount"></strong> siswa yang mengikuti tes</span>
                            </div>

                            <div class="space-y-3">
                                <template x-for="bracket in activeSubjectStats.distribution" :key="bracket.code">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-md text-[11px] font-extrabold flex items-center justify-center text-white"
                                                      :class="bracket.color" x-text="bracket.code"></span>
                                                <span class="font-bold text-slate-800" x-text="bracket.label"></span>
                                            </div>
                                            <div class="flex items-center gap-2 font-mono text-xs">
                                                <span class="font-extrabold text-slate-900" x-text="bracket.count + ' Siswa'"></span>
                                                <span class="text-slate-500 font-semibold" x-text="'(' + bracket.percent + '%)'"></span>
                                            </div>
                                        </div>
                                        <div class="w-full bg-slate-200 h-3 rounded-full overflow-hidden">
                                            <div class="h-3 rounded-full transition-all duration-500"
                                                 :class="bracket.color"
                                                 :style="'width: ' + bracket.percent + '%'"></div>
                                        </div>
                                        <template x-if="bracket.students.length > 0">
                                            <p class="text-[10px] text-slate-500 line-clamp-1 italic">
                                                Siswa: <span x-text="bracket.students.join(', ')"></span>
                                            </p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- KOMPARASI GENDER (PUTRA VS PUTRI) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 break-inside-avoid">
                            <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-blue-900">👦 Siswa Putra (Laki-laki)</span>
                                    <span class="text-xs font-bold text-blue-800 font-mono" x-text="activeSubjectStats.genderStats.male.count + ' Siswa'"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center mt-2.5">
                                    <div class="p-2 bg-white rounded-lg border border-blue-100">
                                        <p class="text-[10px] text-slate-500">Rata-Rata</p>
                                        <p class="text-base font-extrabold text-blue-900" x-text="activeSubjectStats.genderStats.male.avg.toFixed(2)"></p>
                                    </div>
                                    <div class="p-2 bg-white rounded-lg border border-blue-100">
                                        <p class="text-[10px] text-slate-500">Tertinggi</p>
                                        <p class="text-base font-extrabold text-emerald-700" x-text="activeSubjectStats.genderStats.male.max"></p>
                                    </div>
                                    <div class="p-2 bg-white rounded-lg border border-blue-100">
                                        <p class="text-[10px] text-slate-500">Terendah</p>
                                        <p class="text-base font-extrabold text-rose-600" x-text="activeSubjectStats.genderStats.male.min"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-purple-900">👧 Siswi Putri (Perempuan)</span>
                                    <span class="text-xs font-bold text-purple-800 font-mono" x-text="activeSubjectStats.genderStats.female.count + ' Siswi'"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center mt-2.5">
                                    <div class="p-2 bg-white rounded-lg border border-purple-100">
                                        <p class="text-[10px] text-slate-500">Rata-Rata</p>
                                        <p class="text-base font-extrabold text-purple-900" x-text="activeSubjectStats.genderStats.female.avg.toFixed(2)"></p>
                                    </div>
                                    <div class="p-2 bg-white rounded-lg border border-purple-100">
                                        <p class="text-[10px] text-slate-500">Tertinggi</p>
                                        <p class="text-base font-extrabold text-emerald-700" x-text="activeSubjectStats.genderStats.female.max"></p>
                                    </div>
                                    <div class="p-2 bg-white rounded-lg border border-purple-100">
                                        <p class="text-[10px] text-slate-500">Terendah</p>
                                        <p class="text-base font-extrabold text-rose-600" x-text="activeSubjectStats.genderStats.female.min"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ANALISIS PEDAGOGIS & REKOMENDASI UNTUK GURU PENGAMPU -->
                        <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-300 mb-6 break-inside-avoid">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-950 flex items-center gap-1.5">
                                    <span>💡</span> Analisis Hasil Belajar & Rekomendasi Pedagogis
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                      :class="activeSubjectStats.avg >= 70 ? 'bg-emerald-100 text-emerald-900' : (activeSubjectStats.avg >= 50 ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-900')"
                                      x-text="'Kategori: ' + activeSubjectStats.masteryLevel"></span>
                            </div>
                            <p class="text-xs text-slate-800 leading-relaxed mb-3" x-text="activeSubjectStats.summaryText"></p>
                            <div class="bg-white/80 p-3 rounded-lg border border-emerald-200">
                                <p class="text-[11px] font-bold text-emerald-900 mb-1">Rekomendasi Tindak Lanjut Guru Pengajar:</p>
                                <ul class="list-disc list-inside text-xs text-slate-700 space-y-1">
                                    <template x-for="(rec, rIdx) in activeSubjectStats.recommendations" :key="rIdx">
                                        <li x-text="rec"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- DUA KOTAK SOROTAN: TOP 5 SISWA & SISWA PERLU REMEDIAL -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 break-inside-avoid">
                            <!-- Box 1: Top 5 Capaian Tertinggi -->
                            <div class="p-4 rounded-xl bg-white border border-emerald-200 shadow-sm">
                                <div class="flex items-center justify-between mb-2.5">
                                    <h4 class="text-xs font-extrabold text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>🏆</span> Top 5 Capaian Tertinggi
                                    </h4>
                                    <span class="text-[10px] text-emerald-700 font-semibold">Apresiasi & Pengayaan</span>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="(st, idx) in activeSubjectStats.topPerformers" :key="st.username">
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 border border-emerald-100 text-xs">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="w-5 h-5 rounded-full bg-emerald-700 text-white font-extrabold text-[10px] flex items-center justify-center shrink-0" x-text="idx + 1"></span>
                                                <span class="font-bold text-slate-800 truncate" x-text="st.nama"></span>
                                            </div>
                                            <div class="text-right shrink-0 ml-2">
                                                <span class="font-mono font-extrabold text-emerald-800 text-sm" x-text="st.score"></span>
                                                <span class="text-[10px] text-slate-400"> (Absen <span x-text="st.absen"></span>)</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Box 2: Siswa Perlu Remedial & Susulan -->
                            <div class="p-4 rounded-xl bg-white border border-rose-200 shadow-sm">
                                <div class="flex items-center justify-between mb-2.5">
                                    <h4 class="text-xs font-extrabold text-rose-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>⚠️</span> Perlu Remedial & Susulan
                                    </h4>
                                    <span class="text-[10px] text-rose-700 font-semibold" x-text="'Total: ' + (activeSubjectStats.remedialStudents.length + activeSubjectStats.missingStudents.length) + ' Siswa'"></span>
                                </div>
                                <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                                    <template x-for="st in activeSubjectStats.remedialStudents" :key="st.username">
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-rose-50/60 border border-rose-100 text-xs">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                                <span class="font-semibold text-slate-800 truncate" x-text="st.nama"></span>
                                            </div>
                                            <div class="text-right shrink-0 ml-2">
                                                <span class="font-mono font-extrabold text-rose-700" x-text="st.score"></span>
                                                <span class="text-[10px] text-slate-400"> (Absen <span x-text="st.absen"></span>)</span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-for="st in activeSubjectStats.missingStudents" :key="st.username">
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-amber-50 border border-amber-200 text-xs">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                                <span class="font-bold text-amber-900 truncate" x-text="st.nama"></span>
                                            </div>
                                            <span class="text-[10px] font-bold text-amber-700">Susulan (Absen <span x-text="st.absen"></span>)</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- TABEL REKAPITULASI LENGKAP NILAI SISWA (LEDGER) -->
                        <div class="mb-6">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="font-extrabold text-sm text-slate-900">Daftar Nilai Lengkap Siswa Kelas X.13</h3>
                                    <p class="text-xs text-slate-500">Data nilai <span x-text="db.students.length"></span> siswa lengkap dengan peringkat mata pelajaran dan deviasi.</p>
                                </div>

                                <!-- Filter & Sort Ledger (Non-print) -->
                                <div class="flex items-center gap-2 flex-wrap no-print">
                                    <div class="flex items-center gap-1 p-0.5 bg-slate-100 rounded-lg text-xs font-semibold">
                                        <button @click="subjectStudentFilter = 'all'" class="px-2 py-1 rounded" :class="subjectStudentFilter === 'all' ? 'bg-white shadow-xs font-bold text-slate-900' : 'text-slate-600'">Semua</button>
                                        <button @click="subjectStudentFilter = 'tuntas'" class="px-2 py-1 rounded" :class="subjectStudentFilter === 'tuntas' ? 'bg-emerald-700 text-white font-bold' : 'text-slate-600'">Tuntas</button>
                                        <button @click="subjectStudentFilter = 'remedial'" class="px-2 py-1 rounded" :class="subjectStudentFilter === 'remedial' ? 'bg-rose-700 text-white font-bold' : 'text-slate-600'">Remedial</button>
                                        <button @click="subjectStudentFilter = 'susulan'" class="px-2 py-1 rounded" :class="subjectStudentFilter === 'susulan' ? 'bg-amber-600 text-white font-bold' : 'text-slate-600'">Susulan</button>
                                    </div>
                                    <input type="text" x-model="subjectStudentSearch" placeholder="Cari nama / NIS..." class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs w-36">
                                    <select x-model="subjectStudentSort" class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs font-semibold bg-white text-slate-700">
                                        <option value="rank">Urut Peringkat</option>
                                        <option value="absen">Urut Absen</option>
                                        <option value="nama">Urut Nama</option>
                                        <option value="score_asc">Nilai Terendah</option>
                                    </select>
                                </div>
                            </div>

                            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-900 text-white uppercase text-[10px] tracking-wider">
                                            <th class="py-2 px-2 text-center w-12 bg-emerald-950">Rank</th>
                                            <th class="py-2 px-2 text-center w-12">Absen</th>
                                            <th class="py-2 px-2.5">Username STS</th>
                                            <th class="py-2 px-3">Nama Lengkap Siswa</th>
                                            <th class="py-2 px-2 text-center w-10">JK</th>
                                            <th class="py-2 px-3 text-center w-24">Nilai STS</th>
                                            <th class="py-2 px-2.5 text-center w-20">Deviasi Mean</th>
                                            <th class="py-2 px-2 text-center w-16">Predikat</th>
                                            <th class="py-2 px-3 text-center w-28">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        <template x-for="st in getFilteredSubjectStudents(activeSubjectStats)" :key="st.username">
                                            <tr class="hover:bg-slate-50 transition"
                                                :class="st.status === 'susulan' ? 'bg-amber-50/60' : (st.status === 'remedial' ? 'bg-rose-50/30' : '')">
                                                <td class="py-2 px-2 text-center font-extrabold bg-slate-50/50"
                                                    :class="st.rank === 1 ? 'text-amber-600 bg-amber-50 font-black' : (st.rank <= 3 ? 'text-emerald-700' : 'text-slate-700')"
                                                    x-text="st.rank !== '-' ? '#' + st.rank : '-'"></td>
                                                <td class="py-2 px-2 text-center font-semibold text-slate-600" x-text="st.absen"></td>
                                                <td class="py-2 px-2.5 font-mono font-bold text-emerald-800" x-text="st.username"></td>
                                                <td class="py-2 px-3 font-bold text-slate-900" x-text="st.nama"></td>
                                                <td class="py-2 px-2 text-center font-semibold text-slate-600" x-text="st.jk"></td>
                                                <td class="py-2 px-3 text-center">
                                                    <span class="px-2.5 py-0.5 rounded font-black text-xs inline-block"
                                                          :class="st.score === null ? 'bg-rose-100 text-rose-700' : (st.score >= activeSubjectStats.threshold ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-50 text-rose-700')"
                                                          x-text="st.score !== null ? st.score : 'Kosong'"></span>
                                                </td>
                                                <td class="py-2 px-2.5 text-center font-mono font-bold text-[11px]"
                                                    :class="st.diffFromAvg === null ? 'text-slate-400' : (st.diffFromAvg >= 0 ? 'text-emerald-700' : 'text-rose-600')"
                                                    x-text="st.diffFromAvg === null ? '-' : (st.diffFromAvg >= 0 ? '+' + st.diffFromAvg : st.diffFromAvg)"></td>
                                                <td class="py-2 px-2 text-center font-extrabold"
                                                    :class="st.gradeCategory === 'A' ? 'text-emerald-700' : (st.gradeCategory === 'B' ? 'text-teal-700' : (st.gradeCategory === 'C' ? 'text-blue-700' : (st.gradeCategory === 'D' ? 'text-amber-700' : 'text-rose-600')))"
                                                    x-text="st.gradeCategory"></td>
                                                <td class="py-2 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                          :class="st.status === 'tuntas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (st.status === 'remedial' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300')"
                                                          x-text="st.status === 'tuntas' ? '✓ Tuntas' : (st.status === 'remedial' ? 'Remedial' : 'Susulan')"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- LEMBAR TANDA TANGAN & PENGESAHAN LAPORAN RESMI (PRINT FRIENDLY) -->
                        <div class="grid grid-cols-2 gap-8 text-xs text-center pt-6 border-t border-slate-200 break-inside-avoid">
                            <div>
                                <p class="text-slate-600">Mengetahui,</p>
                                <p class="font-bold text-slate-800">Wali Kelas X.13</p>
                                <div class="h-16"></div>
                                <p class="font-extrabold text-slate-900 underline" x-text="db.meta.wali_kelas"></p>
                                <p class="text-[11px] text-slate-500">NIP / NPK: -</p>
                            </div>
                            <div>
                                <p class="text-slate-600">Lamongan, <span x-text="db.meta.tanggal_cetak"></span></p>
                                <p class="font-bold text-slate-800">Guru Pengampu <span x-text="activeSubjectStats.subject.name"></span></p>
                                <div class="h-16"></div>
                                <p class="font-extrabold text-slate-900 underline" x-text="activeSubjectStats.subject.guru || '( ..................................................... )'"></p>
                                <p class="text-[11px] text-slate-500">NIP / NPK: -</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

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
                mode: 'home', // 'home' | 'student' | 'walas' | 'print_all' | 'subjects_list' | 'subject_detail'
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

                // State Manajemen & Statistik Mata Pelajaran
                currentSubjectCode: null,
                subjectKktp: 70,
                subjectFilterRumpun: 'all',
                subjectSearch: '',
                subjectSort: 'default',
                subjectStudentFilter: 'all',
                subjectStudentSort: 'rank',
                subjectStudentSearch: '',
                activeSubjectStats: null,

                initApp() {
                    const params = new URLSearchParams(window.location.search);
                    const u = params.get('u');
                    const mapel = params.get('mapel');
                    const view = params.get('view');

                    if (mapel) {
                        this.openSubjectDetail(mapel, false);
                    } else if (view === 'subjects') {
                        this.openSubjectList(false);
                    } else if (u) {
                        this.searchUsername = u;
                        this.loginStudent();
                    }
                },

                showToast(msg, type = 'success') {
                    this.toast = { show: true, message: msg, type };
                    setTimeout(() => { this.toast.show = false; }, 4000);
                },

                backToHome() {
                    document.title = "Raport Mandiri STS Ganjil 2026/2027 - Kelas X.13 | MA Matholi'ul Anwar";
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', window.location.pathname);
                    }
                    if (this.isWalas && (this.mode === 'student' || this.mode === 'subject_detail')) {
                        this.mode = 'walas';
                    } else {
                        this.mode = 'home';
                        this.currentStudent = null;
                        this.loginError = '';
                    }
                },

                // Navigasi & Operasi Direktori Mata Pelajaran
                openSubjectList(updateUrl = true) {
                    this.mode = 'subjects_list';
                    document.title = "Direktori & Statistik 17 Mata Pelajaran STS - Kelas X.13 | MA Matholi'ul Anwar";
                    if (updateUrl && window.history && window.history.replaceState) {
                        window.history.replaceState({ view: 'subjects' }, '', window.location.pathname + '?view=subjects');
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                openSubjectDetail(code, updateUrl = true) {
                    if (!code) return;
                    const clean = decodeURIComponent(code).trim().toUpperCase();
                    const found = this.db.subjects.find(s => s.code.toUpperCase() === clean);
                    if (found) {
                        this.currentSubjectCode = found.code;
                        this.subjectStudentFilter = 'all';
                        this.subjectStudentSearch = '';
                        this.updateActiveSubjectStats();
                        this.mode = 'subject_detail';
                        document.title = `Laporan Statistik STS - ${found.name} (${found.code}) | Kelas X.13`;
                        if (updateUrl && window.history && window.history.replaceState) {
                            window.history.replaceState({ mapel: found.code }, '', window.location.pathname + '?mapel=' + encodeURIComponent(found.code));
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        this.showToast(`Mata pelajaran '${code}' tidak ditemukan.`, 'error');
                        this.openSubjectList(updateUrl);
                    }
                },

                updateActiveSubjectStats() {
                    if (this.currentSubjectCode) {
                        this.activeSubjectStats = this.computeSubjectStats(this.currentSubjectCode, this.subjectKktp);
                    }
                },

                getFilteredSubjectsList() {
                    let list = [...this.db.subjects];

                    if (this.subjectFilterRumpun !== 'all') {
                        list = list.filter(s => s.group === this.subjectFilterRumpun);
                    }

                    if (this.subjectSearch && this.subjectSearch.trim() !== '') {
                        const q = this.subjectSearch.toLowerCase().trim();
                        list = list.filter(s => s.name.toLowerCase().includes(q) || s.code.toLowerCase().includes(q) || (s.guru && s.guru.toLowerCase().includes(q)));
                    }

                    if (this.subjectSort === 'avg_desc') {
                        list.sort((a, b) => b.avg - a.avg);
                    } else if (this.subjectSort === 'avg_asc') {
                        list.sort((a, b) => a.avg - b.avg);
                    } else if (this.subjectSort === 'tuntas_desc') {
                        list.sort((a, b) => {
                            const statsA = this.computeSubjectStats(a.code, this.subjectKktp);
                            const statsB = this.computeSubjectStats(b.code, this.subjectKktp);
                            return (statsB?.tuntasPercentage || 0) - (statsA?.tuntasPercentage || 0);
                        });
                    }

                    return list;
                },

                computeSubjectStats(code, kktp = null) {
                    if (!code) return null;
                    const threshold = kktp !== null ? Number(kktp) : Number(this.subjectKktp || 70);
                    const sub = this.db.subjects.find(s => s.code === code);
                    if (!sub) return null;

                    const validStudents = [];
                    const missingStudents = [];

                    this.db.students.forEach(st => {
                        const val = st.scores ? st.scores[code] : null;
                        if (val !== null && val !== undefined && !isNaN(Number(val))) {
                            validStudents.push({
                                absen: st.absen,
                                username: st.username,
                                nama: st.nama,
                                jk: st.jk,
                                score: Number(val)
                            });
                        } else {
                            missingStudents.push({
                                absen: st.absen,
                                username: st.username,
                                nama: st.nama,
                                jk: st.jk,
                                score: null
                            });
                        }
                    });

                    const testedCount = validStudents.length;
                    const totalStudents = this.db.students.length;
                    const missingCount = missingStudents.length;

                    let scores = validStudents.map(s => s.score);
                    let avg = 0;
                    let min = 0;
                    let max = 0;
                    let median = 0;
                    let stdDev = 0;
                    let range = 0;

                    if (testedCount > 0) {
                        const sum = scores.reduce((a, b) => a + b, 0);
                        avg = Number((sum / testedCount).toFixed(2));
                        min = Math.min(...scores);
                        max = Math.max(...scores);
                        range = Number((max - min).toFixed(2));

                        const sortedScores = [...scores].sort((a, b) => a - b);
                        const mid = Math.floor(testedCount / 2);
                        median = testedCount % 2 !== 0 ? sortedScores[mid] : Number(((sortedScores[mid - 1] + sortedScores[mid]) / 2).toFixed(2));

                        if (testedCount > 1) {
                            const variance = scores.reduce((acc, val) => acc + Math.pow(val - avg, 2), 0) / (testedCount - 1);
                            stdDev = Number(Math.sqrt(variance).toFixed(2));
                        }
                    }

                    const tuntasStudents = validStudents.filter(s => s.score >= threshold);
                    const belumTuntasStudents = validStudents.filter(s => s.score < threshold);
                    const tuntasCount = tuntasStudents.length;
                    const belumTuntasCount = belumTuntasStudents.length;
                    const tuntasPercentage = testedCount > 0 ? Number(((tuntasCount / testedCount) * 100).toFixed(1)) : 0;

                    // Competition ranking
                    const sortedForRank = [...validStudents].sort((a, b) => b.score - a.score || a.absen - b.absen);
                    let currentRank = 1;
                    sortedForRank.forEach((item, idx) => {
                        if (idx > 0 && item.score < sortedForRank[idx - 1].score) {
                            currentRank = idx + 1;
                        }
                        item.rank = currentRank;
                        item.diffFromAvg = Number((item.score - avg).toFixed(2));
                        item.status = item.score >= threshold ? 'tuntas' : 'remedial';
                        if (item.score >= 90) item.gradeCategory = 'A';
                        else if (item.score >= 80) item.gradeCategory = 'B';
                        else if (item.score >= 70) item.gradeCategory = 'C';
                        else if (item.score >= 60) item.gradeCategory = 'D';
                        else item.gradeCategory = 'E';
                    });

                    const rankedMap = new Map();
                    sortedForRank.forEach(s => rankedMap.set(s.username, s));

                    const allStudentsRanked = this.db.students.map(st => {
                        if (rankedMap.has(st.username)) {
                            return rankedMap.get(st.username);
                        }
                        return {
                            absen: st.absen,
                            username: st.username,
                            nama: st.nama,
                            jk: st.jk,
                            score: null,
                            rank: '-',
                            diffFromAvg: null,
                            status: 'susulan',
                            gradeCategory: '-'
                        };
                    });

                    const topPerformers = sortedForRank.slice(0, 5);

                    const distBrackets = [
                        { code: 'A', label: '90 - 100 (Sangat Baik / Mahir)', min: 90, max: 100, color: 'bg-emerald-600' },
                        { code: 'B', label: '80 - 89 (Baik / Cakap)', min: 80, max: 89.99, color: 'bg-teal-500' },
                        { code: 'C', label: '70 - 79 (Cukup / Layak)', min: 70, max: 79.99, color: 'bg-blue-500' },
                        { code: 'D', label: '60 - 69 (Kurang / Perlu Bimbingan)', min: 60, max: 69.99, color: 'bg-amber-500' },
                        { code: 'E', label: '< 60 (Sangat Kurang / Intervensi Khusus)', min: 0, max: 59.99, color: 'bg-rose-500' }
                    ];

                    const distribution = distBrackets.map(br => {
                        const matches = validStudents.filter(s => s.score >= br.min && s.score <= br.max);
                        const count = matches.length;
                        const percent = testedCount > 0 ? Number(((count / testedCount) * 100).toFixed(1)) : 0;
                        return {
                            ...br,
                            count,
                            percent,
                            students: matches.map(m => m.nama)
                        };
                    });

                    const maleScores = validStudents.filter(s => s.jk === 'L').map(s => s.score);
                    const femaleScores = validStudents.filter(s => s.jk === 'P').map(s => s.score);
                    const genderStats = {
                        male: {
                            count: maleScores.length,
                            avg: maleScores.length > 0 ? Number((maleScores.reduce((a, b) => a + b, 0) / maleScores.length).toFixed(2)) : 0,
                            max: maleScores.length > 0 ? Math.max(...maleScores) : 0,
                            min: maleScores.length > 0 ? Math.min(...maleScores) : 0
                        },
                        female: {
                            count: femaleScores.length,
                            avg: femaleScores.length > 0 ? Number((femaleScores.reduce((a, b) => a + b, 0) / femaleScores.length).toFixed(2)) : 0,
                            max: femaleScores.length > 0 ? Math.max(...femaleScores) : 0,
                            min: femaleScores.length > 0 ? Math.min(...femaleScores) : 0
                        }
                    };

                    let masteryLevel = 'Sangat Baik';
                    let summaryText = '';
                    let recommendations = [];

                    if (avg >= 80) {
                        masteryLevel = 'Sangat Tinggi (Istimewa)';
                        summaryText = `Tingkat pemahaman dan penguasaan materi siswa Kelas X.13 pada mata pelajaran ${sub.name} tergolong sangat istimewa dengan rata-rata ${avg}. Sebagian besar siswa (${tuntasPercentage}%) telah melampaui standar ketuntasan ${threshold}. Disparitas nilai terukur dengan standar deviasi ${stdDev}.`;
                        recommendations = [
                            'Lanjutkan pemberian materi pengayaan (enrichment) dan soal bernalar tingkat tinggi (HOTS).',
                            'Pertahankan ritme belajar yang positif menjelang Sumatif Akhir Semester (SAS).',
                            'Beri pendampingan personal bagi beberapa siswa yang belum melampaui ketuntasan minimal.'
                        ];
                    } else if (avg >= 70) {
                        masteryLevel = 'Baik (Optimal)';
                        summaryText = `Pencapaian hasil belajar mata pelajaran ${sub.name} berada pada kategori baik dengan rata-rata ${avg}. Ketuntasan klasikal mencapai ${tuntasPercentage}%. Masih terdapat disparitas antara nilai tertinggi (${max}) dan nilai terendah (${min}) yang memerlukan penguatan konsep.`;
                        recommendations = [
                            'Lakukan penguatan pada indikator capaian pembelajaran yang banyak mengalami salah penafsiran.',
                            'Laksanakan program remedial berbasis penugasan mandiri / bimbingan kelompok kecil.',
                            'Apresiasi siswa berprestasi sebagai tutor sebaya bagi rekan-rekan sekelas.'
                        ];
                    } else if (avg >= 50) {
                        masteryLevel = 'Cukup / Perlu Penguatan Terarah';
                        summaryText = `Rata-rata capaian mata pelajaran ${sub.name} sebesar ${avg} menunjukkan bahwa sebagian besar siswa masih mengalami kendala dalam menguasai kompetensi dasar STS. Terdapat ${belumTuntasCount} siswa (${(100 - tuntasPercentage).toFixed(1)}%) yang berada di bawah standar ketuntasan ${threshold}.`;
                        recommendations = [
                            'Disarankan mengadakan remedial klasikal terstruktur sebelum melanjutkan ke bab baru.',
                            'Gunakan pendekatan diferensiasi belajar dan ringkasan materi yang lebih aplikatif.',
                            'Koordinasikan siswa susulan dan remedial bersama wali kelas untuk tindak lanjut cepat.'
                        ];
                    } else {
                        masteryLevel = 'Prioritas Evaluasi & Intervensi Khusus';
                        summaryText = `Mata pelajaran ${sub.name} mencatat rata-rata ${avg} dengan tingkat ketuntasan ${tuntasPercentage}%. Mayoritas siswa (${belumTuntasCount} anak) berada di bawah ambang batas ketuntasan, dengan nilai terendah ${min}. Hal ini memerlukan perhatian khusus dan evaluasi bersama guru pengampu.`;
                        recommendations = [
                            'Perlu diagnosis menyeluruh mengenai faktor kesulitan belajar (tingkat kesulitan soal, alokasi waktu, atau pemahaman konsep dasar).',
                            'Adakan program remedial intensif serta pembahasan ulang kisi-kisi soal yang paling banyak dijawab keliru oleh siswa.',
                            'Fasilitasi modul ringkasan atau latihan terarah guna persiapan Sumatif Akhir Semester (SAS).'
                        ];
                    }

                    return {
                        subject: sub,
                        threshold,
                        totalStudents,
                        testedCount,
                        missingCount,
                        scores,
                        avg,
                        min,
                        max,
                        median,
                        range,
                        stdDev,
                        tuntasCount,
                        belumTuntasCount,
                        tuntasPercentage,
                        topPerformers,
                        remedialStudents: sortedForRank.filter(s => s.score < threshold),
                        missingStudents,
                        allStudentsRanked,
                        distribution,
                        genderStats,
                        masteryLevel,
                        summaryText,
                        recommendations
                    };
                },

                getFilteredSubjectStudents(stats) {
                    if (!stats || !stats.allStudentsRanked) return [];
                    let list = [...stats.allStudentsRanked];

                    if (this.subjectStudentFilter === 'tuntas') {
                        list = list.filter(s => s.status === 'tuntas');
                    } else if (this.subjectStudentFilter === 'remedial') {
                        list = list.filter(s => s.status === 'remedial');
                    } else if (this.subjectStudentFilter === 'susulan') {
                        list = list.filter(s => s.status === 'susulan');
                    }

                    if (this.subjectStudentSearch && this.subjectStudentSearch.trim() !== '') {
                        const q = this.subjectStudentSearch.toLowerCase().trim();
                        list = list.filter(s => s.nama.toLowerCase().includes(q) || s.username.toLowerCase().includes(q));
                    }

                    if (this.subjectStudentSort === 'absen') {
                        list.sort((a, b) => a.absen - b.absen);
                    } else if (this.subjectStudentSort === 'score_asc') {
                        list.sort((a, b) => {
                            if (a.score === null) return 1;
                            if (b.score === null) return -1;
                            return a.score - b.score;
                        });
                    } else if (this.subjectStudentSort === 'nama') {
                        list.sort((a, b) => a.nama.localeCompare(b.nama));
                    } else {
                        list.sort((a, b) => {
                            if (a.score === null) return 1;
                            if (b.score === null) return -1;
                            return b.score - a.score || a.absen - b.absen;
                        });
                    }

                    return list;
                },

                copySubjectReportWa() {
                    if (!this.activeSubjectStats) return;
                    const stats = this.activeSubjectStats;

                    let text = `*LAPORAN HASIL SUMATIF TENGAH SEMESTER (STS) GANJIL 2026/2027*\n`;
                    text += `*MA MATHOLI'UL ANWAR LAMONGAN*\n`;
                    text += `*Mata Pelajaran: ${stats.subject.name} (${stats.subject.code})*\n`;
                    text += `• Guru Pengampu: ${stats.subject.guru || '-'}\n`;
                    text += `• Rumpun: ${stats.subject.group}\n`;
                    text += `• Kelas: ${this.db.meta.kelas}\n`;
                    text += `• Wali Kelas: ${this.db.meta.wali_kelas}\n\n`;

                    text += `📊 *RINGKASAN STATISTIK EVALUASI:*\n`;
                    text += `• Total Siswa Terdaftar: ${stats.totalStudents} Siswa\n`;
                    text += `• Siswa Sudah Dinilai: ${stats.testedCount} Siswa\n`;
                    text += `• Siswa Belum Ujian / Susulan: ${stats.missingCount} Siswa\n`;
                    text += `• Rata-Rata Kelas: *${stats.avg}*\n`;
                    text += `• Nilai Tertinggi: *${stats.max}*\n`;
                    text += `• Nilai Terendah: *${stats.min}*\n`;
                    text += `• Median (Nilai Tengah): ${stats.median}\n`;
                    text += `• Standar Deviasi (σ): ${stats.stdDev}\n`;
                    text += `• Standar KKTP: ${stats.threshold}\n`;
                    text += `• Ketuntasan Belajar: *${stats.tuntasCount} dari ${stats.testedCount} Siswa (${stats.tuntasPercentage}%)*\n`;
                    text += `• Perlu Remedial / Pembinaan: *${stats.belumTuntasCount} Siswa*\n\n`;

                    text += `🏆 *TOP 5 CAPAIAN TERTINGGI:*\n`;
                    stats.topPerformers.forEach((s, idx) => {
                        text += `${idx + 1}. ${s.nama} (Absen ${s.absen}) - Nilai: ${s.score}\n`;
                    });
                    text += `\n`;

                    if (stats.remedialStudents.length > 0) {
                        text += `⚠️ *DAFTAR SISWA PERLU REMEDIAL (Nilai < ${stats.threshold}):*\n`;
                        stats.remedialStudents.forEach((s, idx) => {
                            text += `${idx + 1}. ${s.nama} (Absen ${s.absen}) - Nilai: ${s.score}\n`;
                        });
                        text += `\n`;
                    }

                    if (stats.missingStudents.length > 0) {
                        text += `📌 *DAFTAR SISWA BELUM MEMILIKI NILAI (SUSULAN):*\n`;
                        stats.missingStudents.forEach((s, idx) => {
                            text += `• ${s.nama} (Absen ${s.absen}) - Nilai: Kosong / Menunggu Ujian\n`;
                        });
                        text += `\n`;
                    }

                    text += `📝 *CATATAN & EVALUASI PEDAGOGIS:*\n`;
                    text += `${stats.summaryText}\n\n`;
                    text += `Laporan online lengkap dapat diakses di:\n${window.location.origin}${window.location.pathname}?mapel=${encodeURIComponent(stats.subject.code)}\n\n`;
                    text += `Demikian laporan evaluasi ini disampaikan untuk koordinasi pembelajaran ananda. Terima kasih atas dedikasi dan kerja sama Bapak/Ibu Guru Pengampu. Barokallahu fiikum.`;

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(() => {
                            this.showToast('Format laporan WhatsApp berhasil disalin ke clipboard!');
                        }).catch(() => {
                            this.fallbackCopyText(text);
                        });
                    } else {
                        this.fallbackCopyText(text);
                    }
                },

                fallbackCopyText(text) {
                    const textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        this.showToast('Format laporan WhatsApp berhasil disalin ke clipboard!');
                    } catch (err) {
                        this.showToast('Gagal menyalin otomatis. Silakan salin teks secara manual.', 'error');
                    }
                    document.body.removeChild(textArea);
                },

                exportSubjectCsv() {
                    if (!this.activeSubjectStats) return;
                    const stats = this.activeSubjectStats;

                    let csvContent = "\uFEFF"; // UTF-8 BOM
                    csvContent += `LAPORAN EVALUASI NILAI STS GANJIL 2026/2027 - KELAS X.13\n`;
                    csvContent += `Mata Pelajaran,${stats.subject.name} (${stats.subject.code})\n`;
                    csvContent += `Guru Pengampu,"${(stats.subject.guru || '-').replace(/"/g, '""')}"\n`;
                    csvContent += `Rumpun,${stats.subject.group}\n`;
                    csvContent += `Rata-Rata Kelas,${stats.avg}\n`;
                    csvContent += `Nilai Tertinggi,${stats.max}\n`;
                    csvContent += `Nilai Terendah,${stats.min}\n`;
                    csvContent += `Standar KKTP,${stats.threshold}\n`;
                    csvContent += `Ketuntasan Belajar,${stats.tuntasPercentage}%\n\n`;
                    csvContent += `Rank,No Absen,Username STS,Nama Siswa,JK,Nilai STS,Deviasi Rata2,Predikat,Status Ketuntasan\n`;

                    stats.allStudentsRanked.forEach(s => {
                        const scoreStr = s.score !== null ? s.score : "KOSONG";
                        const devStr = s.diffFromAvg !== null ? (s.diffFromAvg >= 0 ? `+${s.diffFromAvg}` : `${s.diffFromAvg}`) : "-";
                        const cleanName = `"${s.nama.replace(/"/g, '""')}"`;
                        const statusLabel = s.status === 'tuntas' ? 'TUNTAS' : (s.status === 'remedial' ? 'REMEDIAL' : 'BELUM UJIAN');
                        csvContent += `${s.rank},${s.absen},${s.username},${cleanName},${s.jk},${scoreStr},${devStr},${s.gradeCategory},${statusLabel}\n`;
                    });

                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.setAttribute("href", url);
                    link.setAttribute("download", `Rekap_STS_X13_${stats.subject.code}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                    this.showToast(`Rekap nilai ${stats.subject.code} berhasil diunduh (CSV)!`);
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
                        if (this.activeSubjectStats) {
                            this.updateActiveSubjectStats();
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
