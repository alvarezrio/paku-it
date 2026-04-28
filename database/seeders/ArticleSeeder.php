<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = $this->seedCategories();
        $authorId = User::query()->where('email', 'admin@admin.com')->value('id')
            ?? User::query()->value('id');

        foreach ($this->articles($categories) as $data) {
            $data['user_id'] = $authorId;
            $data['slug'] = Str::slug($data['title']);

            Article::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }

    /**
     * Ensure thematic categories exist and return a name => id map.
     */
    private function seedCategories(): array
    {
        $items = [
            ['name' => 'Tutorial',        'slug' => 'tutorial',        'description' => 'Panduan langkah demi langkah seputar penggunaan sistem dan perangkat IT.'],
            ['name' => 'Tips & Trik',     'slug' => 'tips-trik',       'description' => 'Tips praktis untuk produktivitas, perawatan perangkat, dan pengelolaan administrasi.'],
            ['name' => 'Troubleshooting', 'slug' => 'troubleshooting', 'description' => 'Solusi masalah teknis yang umum dihadapi pengguna kantor.'],
            ['name' => 'Keamanan',        'slug' => 'keamanan',        'description' => 'Edukasi keamanan informasi, akun, dan data instansi.'],
            ['name' => 'Berita',          'slug' => 'berita',          'description' => 'Pengumuman dan informasi terbaru seputar layanan IT.'],
        ];

        $map = [];
        foreach ($items as $item) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $item['slug']],
                $item + ['is_active' => true],
            );
            $map[$item['slug']] = $category->id;
        }

        return $map;
    }

    /**
     * Curated article content relevant to the IT administration domain.
     */
    private function articles(array $categories): array
    {
        return [
            [
                'title' => 'Tips Merawat Laptop Kantor Agar Awet dan Optimal',
                'author_name' => 'Tim IT Support',
                'category' => 'tips-tricks',
                'category_id' => $categories['tips-trik'],
                'status' => 'published',
                'published_at' => now()->subDays(30),
                'views' => 412,
                'content' => <<<'HTML'
<p>Laptop adalah salah satu aset paling penting di lingkungan kerja. Perawatan rutin tidak hanya memperpanjang usia perangkat, tetapi juga memastikan pekerjaan tidak terhambat akibat kerusakan mendadak.</p>

<h2>1. Bersihkan Layar dan Keyboard Secara Berkala</h2>
<p>Gunakan kain microfiber yang sedikit dibasahi cairan pembersih khusus layar. Untuk keyboard, manfaatkan kuas halus atau blower kecil agar debu di sela-sela tombol tidak mengganggu fungsi tombol.</p>

<h2>2. Hindari Meletakkan Laptop di Permukaan Lunak</h2>
<p>Bantal, kasur, atau pangkuan dapat menutup ventilasi udara dan menyebabkan laptop cepat panas. Gunakan meja yang rata atau cooling pad ketika beban kerja tinggi.</p>

<h2>3. Jaga Siklus Baterai</h2>
<p>Lepas charger ketika baterai sudah penuh dan hindari membiarkan laptop benar-benar mati karena baterai habis. Untuk laptop yang lebih sering digunakan dengan adaptor, banyak vendor menyediakan mode <em>battery care</em> untuk menjaga kapasitas tetap pada 80%.</p>

<h2>4. Update Sistem Operasi dan Antivirus</h2>
<p>Pembaruan keamanan menutup celah yang dapat dimanfaatkan pelaku kejahatan siber. Pastikan Windows Update dan antivirus selalu aktif. Jika ragu, hubungi tim IT melalui sistem helpdesk.</p>

<h2>5. Lapor Sejak Dini Jika Ada Gejala Aneh</h2>
<p>Suara kipas yang nyaring, layar berkedip, atau performa yang melambat adalah tanda perangkat butuh perhatian. Buat tiket helpdesk dengan deskripsi jelas agar perbaikan bisa dilakukan sebelum kerusakan meluas.</p>

<p><strong>Kesimpulan:</strong> perawatan kecil yang dilakukan rutin lebih efektif daripada perbaikan besar di kemudian hari. Laptop yang dirawat baik akan menemani pekerjaan Anda untuk waktu yang panjang.</p>
HTML,
            ],

            [
                'title' => 'Cara Membuat Tiket Helpdesk yang Efektif',
                'author_name' => 'Tim IT Support',
                'category' => 'tutorial',
                'category_id' => $categories['tutorial'],
                'status' => 'published',
                'published_at' => now()->subDays(25),
                'views' => 587,
                'content' => <<<'HTML'
<p>Tiket yang ditulis dengan baik mempercepat penyelesaian masalah. Sebaliknya, tiket yang singkat dan ambigu sering kali membuat teknisi harus bolak-balik bertanya, sehingga waktu penyelesaian molor.</p>

<h2>Struktur Tiket yang Ideal</h2>
<ol>
    <li><strong>Subjek yang spesifik</strong> &mdash; misalnya <em>"Printer ruang Tata Usaha tidak mencetak dokumen"</em>, bukan sekadar <em>"Printer rusak"</em>.</li>
    <li><strong>Kategori dan prioritas yang sesuai</strong> &mdash; pilih Hardware, Software, Jaringan, atau Printer. Tetapkan prioritas <em>Kritis</em> hanya untuk masalah yang menghentikan aktivitas kerja banyak orang.</li>
    <li><strong>Deskripsi runtut</strong> &mdash; jelaskan kapan masalah mulai terjadi, apa yang sudah Anda coba, dan apa pesan error yang muncul.</li>
    <li><strong>Lampiran pendukung</strong> &mdash; foto kerusakan, screenshot pesan error, atau berkas yang gagal dibuka membantu teknisi memetakan masalah.</li>
</ol>

<h2>Contoh Deskripsi yang Baik</h2>
<blockquote>
"Sejak pagi tadi (08.30) printer HP LaserJet di ruang Tata Usaha tidak merespons saat di-print dari laptop saya. Sudah saya coba restart printer, cek koneksi LAN, dan print dari laptop lain hasilnya sama. Lampu indikator berkedip kuning. Lampiran: foto status panel printer."
</blockquote>

<h2>Pantau Status Tiket Anda</h2>
<p>Setelah tiket dibuat, pantau status melalui dashboard. Workflow umumnya: <strong>Open &rarr; In Progress &rarr; Waiting for User &rarr; Resolved &rarr; Closed</strong>. Apabila status berubah ke <em>Waiting for User</em>, segera berikan informasi tambahan yang diminta agar tiket dapat dilanjutkan.</p>

<p>Dengan kebiasaan menulis tiket yang baik, masalah teknis dapat ditangani lebih cepat dan tepat sasaran.</p>
HTML,
            ],

            [
                'title' => 'Panduan Membuat Password yang Kuat dan Mudah Diingat',
                'author_name' => 'Tim Keamanan Informasi',
                'category' => 'security',
                'category_id' => $categories['keamanan'],
                'status' => 'published',
                'published_at' => now()->subDays(22),
                'views' => 734,
                'content' => <<<'HTML'
<p>Password yang lemah adalah pintu paling sering dimanfaatkan oleh pelaku kejahatan siber. Kabar baiknya, password yang kuat tidak harus sulit diingat.</p>

<h2>Ciri Password yang Kuat</h2>
<ul>
    <li>Panjang minimal 12 karakter.</li>
    <li>Mengandung kombinasi huruf besar, huruf kecil, angka, dan simbol.</li>
    <li>Tidak menggunakan informasi pribadi seperti tanggal lahir, NIP, atau nama anggota keluarga.</li>
    <li>Berbeda untuk setiap layanan penting.</li>
</ul>

<h2>Teknik Frasa Sandi (Passphrase)</h2>
<p>Gabungkan empat kata acak yang mudah Anda visualisasikan, tambahkan angka dan simbol di antaranya. Contoh:</p>
<p><code>Kucing!Hujan7Sepeda#Kopi</code></p>
<p>Frasa seperti ini lebih sulit dipecahkan dibanding <em>P@ssw0rd</em>, namun lebih mudah diingat.</p>

<h2>Gunakan Password Manager</h2>
<p>Aplikasi seperti Bitwarden atau KeePass dapat menyimpan dan mengisi password secara otomatis. Anda hanya perlu mengingat satu master password.</p>

<h2>Aktifkan Two-Factor Authentication</h2>
<p>Walau password kuat, lapis keamanan kedua tetap diperlukan. Aktifkan 2FA di sistem yang menyediakannya, termasuk akun email kantor dan portal administrasi.</p>

<p>Ingat: keamanan dimulai dari kebiasaan kecil. Tidak ada sistem yang sepenuhnya aman tanpa partisipasi penggunanya.</p>
HTML,
            ],

            [
                'title' => 'Mengenali dan Menghindari Email Phishing',
                'author_name' => 'Tim Keamanan Informasi',
                'category' => 'security',
                'category_id' => $categories['keamanan'],
                'status' => 'published',
                'published_at' => now()->subDays(18),
                'views' => 658,
                'content' => <<<'HTML'
<p>Phishing adalah upaya menipu korban agar memberikan data sensitif (kredensial login, nomor rekening, kode OTP) melalui email yang dibuat menyerupai komunikasi resmi.</p>

<h2>Tanda-Tanda Email Phishing</h2>
<ul>
    <li><strong>Alamat pengirim mencurigakan.</strong> Periksa domainnya dengan teliti, misalnya <code>support@micros0ft.com</code> alih-alih <code>microsoft.com</code>.</li>
    <li><strong>Bahasa yang menekan.</strong> "Akun Anda akan diblokir dalam 24 jam!" adalah taktik klasik untuk memancing keputusan terburu-buru.</li>
    <li><strong>Tautan yang berbeda dengan teks tampilannya.</strong> Arahkan kursor (tanpa mengeklik) untuk melihat URL aslinya di pojok bawah peramban.</li>
    <li><strong>Lampiran tidak terduga.</strong> File <code>.zip</code>, <code>.exe</code>, atau dokumen Office yang meminta mengaktifkan macro patut dicurigai.</li>
    <li><strong>Salam sapaan generik.</strong> "Dear Customer" atau "Pengguna yang terhormat" tanpa menyebut nama Anda.</li>
</ul>

<h2>Apa yang Harus Dilakukan</h2>
<ol>
    <li>Jangan klik tautan atau buka lampiran apapun.</li>
    <li>Jangan balas email tersebut.</li>
    <li>Laporkan ke tim IT melalui tiket helpdesk dengan kategori <em>Keamanan / Security Incident</em>, sertakan tangkapan layar utuh termasuk header email.</li>
    <li>Hapus email setelah dilaporkan.</li>
</ol>

<h2>Apabila Sudah Terlanjur Mengeklik</h2>
<p>Segera ganti password akun terkait, putuskan koneksi internet pada perangkat yang dipakai, dan laporkan secepat mungkin. Semakin cepat respons, semakin kecil dampak yang dapat ditimbulkan.</p>

<p>Kewaspadaan satu orang dapat menyelamatkan data seluruh organisasi.</p>
HTML,
            ],

            [
                'title' => 'Etika dan Prosedur Peminjaman Kendaraan Dinas Operasional',
                'author_name' => 'Subbag Umum',
                'category' => 'tutorial',
                'category_id' => $categories['tutorial'],
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'views' => 321,
                'content' => <<<'HTML'
<p>Kendaraan Dinas Operasional (KDO) adalah aset bersama yang menunjang kelancaran tugas. Pengelolaan yang tertib memastikan KDO selalu siap saat dibutuhkan oleh siapa pun.</p>

<h2>Sebelum Peminjaman</h2>
<ul>
    <li>Ajukan booking melalui sistem minimal H-1 dengan mengisi tujuan, keperluan, dan daftar penumpang.</li>
    <li>Lampirkan nomor surat tugas/perjalanan dinas pada formulir booking.</li>
    <li>Periksa kalender ketersediaan untuk menghindari konflik jadwal.</li>
</ul>

<h2>Saat Pengambilan Kendaraan</h2>
<ul>
    <li>Catat odometer awal dan level BBM di sistem.</li>
    <li>Periksa kondisi fisik kendaraan: ban, lampu, kebersihan kabin.</li>
    <li>Pastikan STNK dan SIM pengemudi masih berlaku.</li>
</ul>

<h2>Selama Penggunaan</h2>
<ul>
    <li>Gunakan kendaraan hanya untuk keperluan dinas yang tertera dalam pengajuan.</li>
    <li>Patuhi peraturan lalu lintas. Pelanggaran menjadi tanggung jawab pengemudi.</li>
    <li>Isi BBM minimal hingga level yang sama dengan saat diambil.</li>
</ul>

<h2>Saat Pengembalian</h2>
<ul>
    <li>Kembalikan kendaraan dalam kondisi bersih dan tepat waktu.</li>
    <li>Catat odometer akhir dan level BBM.</li>
    <li>Laporkan kerusakan atau insiden meskipun terlihat kecil &mdash; lebih baik melapor lebih awal.</li>
</ul>

<p>Kedisiplinan administrasi KDO membuat layanan transportasi dinas berjalan adil dan efisien bagi seluruh pegawai.</p>
HTML,
            ],

            [
                'title' => '5 Cara Mempercepat Windows untuk Produktivitas Kerja',
                'author_name' => 'Tim IT Support',
                'category' => 'tips-tricks',
                'category_id' => $categories['tips-trik'],
                'status' => 'published',
                'published_at' => now()->subDays(12),
                'views' => 902,
                'content' => <<<'HTML'
<p>Komputer yang lambat dapat memotong jam produktif Anda secara signifikan. Berikut langkah praktis yang dapat dilakukan tanpa harus install ulang sistem operasi.</p>

<h2>1. Matikan Aplikasi Startup yang Tidak Perlu</h2>
<p>Buka <strong>Task Manager &rarr; tab Startup</strong>. Disable aplikasi dengan dampak <em>High</em> yang tidak Anda gunakan saat boot. Spotify, Steam, dan aplikasi update vendor sering kali bisa dimatikan tanpa konsekuensi.</p>

<h2>2. Bersihkan File Sementara</h2>
<p>Tekan <kbd>Win</kbd> + <kbd>R</kbd>, ketik <code>%temp%</code>, lalu hapus isinya. Lanjutkan dengan menjalankan <em>Storage Sense</em> dari Settings untuk membersihkan cache sistem secara berkala.</p>

<h2>3. Tambah RAM atau Ganti ke SSD</h2>
<p>Apabila perangkat masih menggunakan HDD, peningkatan ke SSD adalah investasi paling terasa. Untuk laptop kantor, ajukan permintaan upgrade melalui tiket dengan menyebutkan beban kerja Anda.</p>

<h2>4. Tutup Tab Browser yang Berlebihan</h2>
<p>Setiap tab Chrome bisa memakan ratusan MB RAM. Manfaatkan ekstensi <em>The Great Suspender</em> alternatif modern atau fitur <em>Memory Saver</em> bawaan untuk membekukan tab yang tidak aktif.</p>

<h2>5. Lakukan Restart Berkala</h2>
<p>Sleep berhari-hari membuat memori dipenuhi residu aplikasi. Restart komputer minimal seminggu sekali agar sistem kembali segar.</p>

<p>Jika setelah langkah-langkah di atas perangkat tetap lambat, kemungkinan ada masalah perangkat keras. Buat tiket dengan kategori Hardware untuk pemeriksaan lebih lanjut.</p>
HTML,
            ],

            [
                'title' => 'Pentingnya Backup Data Rutin untuk Pegawai',
                'author_name' => 'Tim IT Support',
                'category' => 'tips-tricks',
                'category_id' => $categories['tips-trik'],
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'views' => 245,
                'content' => <<<'HTML'
<p>Kerusakan hard disk, infeksi ransomware, atau laptop hilang adalah skenario yang dapat menghapus pekerjaan berbulan-bulan dalam hitungan detik. Backup rutin adalah polis asuransi termurah yang dapat Anda lakukan.</p>

<h2>Aturan 3-2-1</h2>
<ul>
    <li><strong>3 salinan</strong> data penting.</li>
    <li><strong>2 media penyimpanan berbeda</strong> (misalnya SSD laptop dan hard disk eksternal).</li>
    <li><strong>1 salinan disimpan di lokasi terpisah</strong>, contohnya cloud storage instansi.</li>
</ul>

<h2>Apa Saja yang Perlu Di-backup?</h2>
<ul>
    <li>Dokumen kerja di folder <code>Documents</code>, <code>Desktop</code>, dan <code>Downloads</code>.</li>
    <li>Konfigurasi email dan tanda tangan digital.</li>
    <li>Bookmark dan ekstensi browser yang Anda gunakan untuk kerja.</li>
</ul>

<h2>Otomatisasi Lebih Baik Daripada Manual</h2>
<p>Manfaatkan fitur <em>OneDrive</em> atau <em>Google Drive</em> yang sudah disediakan instansi. Atur folder kerja agar otomatis tersinkron, sehingga Anda tidak perlu mengingat kapan terakhir backup dilakukan.</p>

<h2>Uji Hasil Backup Anda</h2>
<p>Backup yang tidak pernah diuji sama saja dengan tidak punya backup. Sesekali, coba pulihkan satu file dari cadangan untuk memastikan prosesnya berjalan baik.</p>

<p>Lima menit yang Anda alokasikan untuk backup hari ini bisa menyelamatkan berhari-hari pekerjaan di masa depan.</p>
HTML,
            ],

            [
                'title' => 'Troubleshooting: Printer Tidak Terdeteksi di Jaringan Kantor',
                'author_name' => 'Tim IT Support',
                'category' => 'troubleshooting',
                'category_id' => $categories['troubleshooting'],
                'status' => 'published',
                'published_at' => now()->subDays(8),
                'views' => 478,
                'content' => <<<'HTML'
<p>Printer jaringan yang tiba-tiba tidak terdeteksi adalah salah satu kasus paling sering dilaporkan ke helpdesk. Sebagian besar dapat diselesaikan sendiri dalam beberapa langkah.</p>

<h2>Langkah Pemeriksaan Mandiri</h2>
<ol>
    <li><strong>Pastikan printer dalam keadaan menyala</strong> dan tidak menampilkan pesan error di panelnya.</li>
    <li><strong>Cek lampu indikator jaringan</strong>. Lampu LAN yang mati bisa berarti kabel terlepas atau switch bermasalah.</li>
    <li><strong>Lakukan ping ke alamat IP printer</strong> dari Command Prompt: <code>ping 192.168.x.x</code>. Jika gagal, masalah ada di jaringan, bukan komputer Anda.</li>
    <li><strong>Restart antrean print</strong>. Tekan <kbd>Win</kbd> + <kbd>R</kbd>, ketik <code>services.msc</code>, cari <em>Print Spooler</em>, klik kanan &rarr; Restart.</li>
    <li><strong>Hapus dan tambahkan ulang printer</strong> dari Settings &rarr; Bluetooth &amp; devices &rarr; Printers &amp; scanners.</li>
</ol>

<h2>Apabila Masalah Belum Teratasi</h2>
<p>Buat tiket dengan kategori <em>Printer</em> dan sertakan informasi:</p>
<ul>
    <li>Merek dan model printer.</li>
    <li>Lokasi fisik printer.</li>
    <li>Pesan error yang muncul (sertakan screenshot).</li>
    <li>Hasil <em>ping</em> ke IP printer.</li>
</ul>

<p>Informasi yang lengkap memungkinkan teknisi datang dengan persiapan yang tepat, sehingga pencetakan dapat segera kembali normal.</p>
HTML,
            ],

            [
                'title' => 'Mengaktifkan Two-Factor Authentication (2FA) di Akun Anda',
                'author_name' => 'Tim Keamanan Informasi',
                'category' => 'security',
                'category_id' => $categories['keamanan'],
                'status' => 'published',
                'published_at' => now()->subDays(6),
                'views' => 389,
                'content' => <<<'HTML'
<p>Two-Factor Authentication (2FA) menambahkan lapisan keamanan kedua di samping password. Walau kredensial Anda bocor, pelaku kejahatan tetap memerlukan kode OTP yang berubah setiap 30 detik.</p>

<h2>Persiapan</h2>
<ol>
    <li>Pasang aplikasi authenticator di ponsel: <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong>, atau <strong>Authy</strong>.</li>
    <li>Pastikan jam ponsel disinkronkan otomatis. Selisih waktu menyebabkan kode dianggap tidak valid.</li>
</ol>

<h2>Langkah Aktivasi</h2>
<ol>
    <li>Login ke akun Anda dan buka menu <strong>Profil &rarr; Keamanan</strong>.</li>
    <li>Pilih <em>Aktifkan Two-Factor Authentication</em>.</li>
    <li>Pindai kode QR yang muncul menggunakan aplikasi authenticator.</li>
    <li>Masukkan kode 6 digit dari aplikasi sebagai verifikasi.</li>
    <li><strong>Simpan kode pemulihan</strong> di tempat yang aman, misalnya brankas password manager.</li>
</ol>

<h2>Apa yang Terjadi Jika Kehilangan Ponsel?</h2>
<p>Gunakan kode pemulihan yang Anda simpan untuk login. Setelah masuk, segera nonaktifkan 2FA pada perangkat lama dan aktifkan ulang pada perangkat baru. Apabila kode pemulihan juga hilang, hubungi admin sistem melalui tiket helpdesk dengan bukti identitas.</p>

<p>Beberapa menit yang dihabiskan untuk konfigurasi 2FA sebanding dengan ketenangan jangka panjang akan keamanan akun Anda.</p>
HTML,
            ],

            [
                'title' => 'Sistem Inventaris IT: Mengapa Pencatatan Aset Itu Penting',
                'author_name' => 'Subbag Umum',
                'category' => 'tutorial',
                'category_id' => $categories['tutorial'],
                'status' => 'published',
                'published_at' => now()->subDays(4),
                'views' => 198,
                'content' => <<<'HTML'
<p>Pencatatan aset IT bukan sekadar tuntutan administrasi. Inventaris yang akurat adalah fondasi pengambilan keputusan strategis tim IT.</p>

<h2>Manfaat Inventaris yang Tertib</h2>
<ul>
    <li><strong>Perencanaan anggaran</strong> &mdash; data umur perangkat membantu menyusun rencana penggantian.</li>
    <li><strong>Pelacakan garansi</strong> &mdash; perangkat masih bergaransi seharusnya tidak diperbaiki dengan biaya sendiri.</li>
    <li><strong>Pertanggungjawaban</strong> &mdash; setiap perangkat memiliki pengguna yang tercatat sehingga risiko kehilangan dapat ditekan.</li>
    <li><strong>Analisis insiden</strong> &mdash; riwayat tiket per perangkat memunculkan pola, misalnya model laptop tertentu yang sering bermasalah.</li>
</ul>

<h2>Data Minimal yang Perlu Dicatat</h2>
<ul>
    <li>Tipe perangkat (Laptop, Desktop, Printer, dll).</li>
    <li>Merek, model, dan nomor seri.</li>
    <li>Spesifikasi singkat: prosesor, RAM, storage, OS.</li>
    <li>Pengguna saat ini dan lokasi penempatan.</li>
    <li>Tanggal pengadaan dan masa berakhir garansi.</li>
    <li>Kondisi terkini.</li>
</ul>

<h2>Disiplin Update Data</h2>
<p>Inventaris paling baik adalah yang selalu diperbarui. Setiap mutasi pegawai, perbaikan, atau penggantian komponen sebaiknya langsung tercatat di sistem agar data tidak menyimpang dari kondisi nyata.</p>

<p>Aset yang tidak tercatat ibarat tidak ada &mdash; sulit dipertanggungjawabkan, sulit dirawat, dan rawan hilang.</p>
HTML,
            ],

            [
                'title' => 'Manajemen File yang Rapi di Komputer Kantor',
                'author_name' => 'Tim IT Support',
                'category' => 'tips-tricks',
                'category_id' => $categories['tips-trik'],
                'status' => 'draft',
                'published_at' => null,
                'views' => 0,
                'content' => <<<'HTML'
<p>Folder Desktop yang penuh ikon dan nama berkas seperti <em>Final-Final-Revisi3.docx</em> adalah pemandangan yang familiar. Sedikit disiplin penamaan dan struktur folder akan menghemat banyak waktu pencarian.</p>

<h2>Struktur Folder yang Konsisten</h2>
<p>Buat hirarki sederhana berdasarkan tahun, kemudian unit kerja atau jenis dokumen. Contoh:</p>
<pre>2026/
&nbsp;&nbsp;&nbsp;|-- 01-Surat-Masuk/
&nbsp;&nbsp;&nbsp;|-- 02-Surat-Keluar/
&nbsp;&nbsp;&nbsp;|-- 03-Laporan/
&nbsp;&nbsp;&nbsp;+-- 04-Anggaran/</pre>

<h2>Konvensi Penamaan File</h2>
<ul>
    <li>Awali dengan tanggal format <code>YYYY-MM-DD</code> agar urut otomatis.</li>
    <li>Gunakan tanda hubung (<code>-</code>) atau garis bawah (<code>_</code>) sebagai pemisah, hindari spasi.</li>
    <li>Sertakan versi di akhir, misalnya <code>v1</code>, <code>v2-final</code>.</li>
</ul>
<p>Contoh: <code>2026-04-25_Laporan-Bulanan_v2.docx</code>.</p>

<h2>Manfaatkan Cloud untuk Kolaborasi</h2>
<p>Hindari mengirim dokumen besar via email berulang kali. Bagikan tautan dari OneDrive atau Google Drive sehingga semua kolaborator bekerja pada versi yang sama dan riwayat perubahannya terlacak.</p>

<p>Disiplin kecil ini, bila diterapkan tim, dapat menyingkat rapat berjam-jam menjadi hitungan menit.</p>
HTML,
            ],

            [
                'title' => 'Cara Aman Menggunakan WiFi Publik Saat Perjalanan Dinas',
                'author_name' => 'Tim Keamanan Informasi',
                'category' => 'security',
                'category_id' => $categories['keamanan'],
                'status' => 'draft',
                'published_at' => null,
                'views' => 0,
                'content' => <<<'HTML'
<p>WiFi gratis di bandara, hotel, dan kafe sangat membantu, tetapi juga menjadi medan favorit pelaku kejahatan siber. Berikut panduan agar perjalanan dinas Anda tetap produktif tanpa mengorbankan keamanan data.</p>

<h2>Sebelum Terhubung</h2>
<ul>
    <li>Pastikan SSID benar-benar milik tempat tersebut. Tanyakan kepada staf, jangan menebak dari nama yang mirip.</li>
    <li>Aktifkan firewall dan matikan <em>file sharing</em> di laptop.</li>
    <li>Gunakan VPN instansi apabila tersedia.</li>
</ul>

<h2>Selama Terhubung</h2>
<ul>
    <li>Hindari mengakses portal administrasi atau internet banking pada jaringan publik.</li>
    <li>Pastikan setiap situs yang Anda buka menggunakan HTTPS (terdapat ikon gembok).</li>
    <li>Jangan mengizinkan komputer Anda <em>discoverable</em> oleh perangkat lain di jaringan.</li>
</ul>

<h2>Setelah Selesai</h2>
<p>"Lupakan" jaringan tersebut dari daftar WiFi tersimpan agar laptop tidak otomatis tersambung kembali ketika berada di lokasi yang sama. Pertimbangkan untuk mengganti password akun-akun penting setibanya di kantor, terutama jika Anda merasa pernah login pada layanan sensitif.</p>

<p>Kenyamanan dan keamanan dapat berjalan beriringan ketika Anda waspada pada hal-hal kecil seperti ini.</p>
HTML,
            ],

            [
                'title' => 'Pengumuman Lama: Migrasi Sistem Helpdesk ke Versi Baru',
                'author_name' => 'Tim IT Support',
                'category' => 'news',
                'category_id' => $categories['berita'],
                'status' => 'archived',
                'published_at' => now()->subYear(),
                'views' => 1253,
                'content' => <<<'HTML'
<p>Diberitahukan kepada seluruh pegawai bahwa sistem helpdesk lama telah dimigrasikan ke versi baru yang lebih responsif dan terintegrasi dengan modul inventaris perangkat.</p>

<h2>Perubahan Utama</h2>
<ul>
    <li>Format nomor tiket baru: <code>TKT-YYYYMMDD-XXXX</code>.</li>
    <li>Notifikasi real-time ke email pelapor dan teknisi.</li>
    <li>Lampiran maksimal 5 berkas dengan total 25 MB.</li>
    <li>Tiket terhubung otomatis dengan riwayat perangkat yang dilaporkan.</li>
</ul>

<p>Riwayat tiket lama tetap dapat diakses untuk keperluan referensi. Apabila ada kendala selama masa transisi, silakan hubungi tim IT.</p>

<p><em>Pengumuman ini diarsipkan karena migrasi telah selesai. Disimpan untuk dokumentasi.</em></p>
HTML,
            ],
        ];
    }
}
