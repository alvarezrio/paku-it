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

        $allArticles = array_merge(
            $this->articles($categories),
            $this->troubleshootingArticles($categories),
        );

        foreach ($allArticles as $data) {
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

            // ── EXISTING LAST ARTICLE FOLLOWS ──
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

    // ─────────────────────────────────────────────────────────────────────────
    // 50 Artikel Troubleshooting Hardware & Software
    // ─────────────────────────────────────────────────────────────────────────
    private function troubleshootingArticles(array $c): array
    {
        $ts  = $c['troubleshooting'];
        $tip = $c['tips-trik'];
        $tut = $c['tutorial'];

        return [

            /* ── HARDWARE 1-25 ────────────────────────────────────────────── */

            [
                'title'        => 'Troubleshooting: Komputer Tidak Mau Menyala',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(45),
                'views'        => 621,
                'content'      => <<<'HTML'
<p>Komputer yang tidak bereaksi sama sekali saat tombol power ditekan adalah masalah yang mengkhawatirkan. Namun sebagian besar penyebabnya dapat didiagnosis secara mandiri sebelum memanggil teknisi.</p>

<h2>Langkah Diagnosis Mandiri</h2>
<ol>
  <li><strong>Periksa kabel power.</strong> Pastikan kabel terhubung ke stopkontak yang aktif. Coba stopkontak lain atau test dengan perangkat lain.</li>
  <li><strong>Cek tombol power di casing.</strong> Pastikan tidak macet. Pada desktop, coba hubungkan langsung pin power di motherboard dengan obeng untuk memastikan tombol tidak bermasalah.</li>
  <li><strong>Periksa indikator LED pada PSU atau motherboard.</strong> Jika lampu PSU tidak menyala sama sekali, kemungkinan besar PSU mati atau kabel longgar.</li>
  <li><strong>Lepas semua perangkat eksternal</strong> (USB, monitor tambahan, dll.) lalu coba nyalakan kembali.</li>
  <li><strong>Untuk laptop:</strong> lepas baterai dan coba hanya dengan adaptor, atau sebaliknya.</li>
</ol>

<h2>Penyebab Umum</h2>
<ul>
  <li>Kabel power longgar atau stopkontak mati.</li>
  <li>PSU (Power Supply Unit) rusak.</li>
  <li>Baterai laptop habis total (perlu charge minimal 10 menit).</li>
  <li>Modul RAM tidak terpasang sempurna (bunyi beep saat menyala).</li>
</ul>

<h2>Kapan Harus Lapor ke IT?</h2>
<p>Jika setelah semua langkah di atas komputer tetap tidak menyala, buat tiket dengan kategori <strong>Hardware</strong>, sertakan model perangkat, dan kronologi kejadian. Jangan mencoba membuka casing sendiri karena dapat menggugurkan garansi.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Layar Laptop Blank/Hitam saat Dinyalakan',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(44),
                'views'        => 543,
                'content'      => <<<'HTML'
<p>Laptop menyala (kipas berputar, lampu LED aktif) tetapi layar tetap hitam adalah situasi yang membingungkan. Berikut cara menelusurinya secara sistematis.</p>

<h2>Langkah Pemeriksaan</h2>
<ol>
  <li><strong>Tekan tombol <kbd>Fn</kbd> + tombol layar</strong> (biasanya F4, F5, atau F8 bergantung merek). Laptop mungkin berada di mode extended display atau layar dinonaktifkan.</li>
  <li><strong>Hubungkan monitor eksternal via HDMI/VGA.</strong> Jika gambar muncul di monitor eksternal, masalah ada di layar atau koneksi kabel internal laptop.</li>
  <li><strong>Sorot layar dengan senter.</strong> Jika samar-samar terlihat gambar, backlight layar mati — bukan masalah sistem operasi.</li>
  <li><strong>Hard reset:</strong> tahan tombol power 10 detik hingga laptop mati, tunggu 30 detik, nyalakan kembali.</li>
  <li><strong>Lepas semua perangkat USB</strong> dan coba boot tanpa aksesori.</li>
</ol>

<h2>Penyebab Umum</h2>
<ul>
  <li>Driver kartu grafis corrupt setelah update Windows.</li>
  <li>Kabel fleksibel layar longgar (akibat engsel sering dibuka-tutup).</li>
  <li>Backlight inverter rusak.</li>
  <li>RAM tidak terpasang sempurna.</li>
</ul>

<p>Segera buat tiket helpdesk jika layar tetap hitam setelah langkah di atas. Sertakan hasil percobaan monitor eksternal dalam deskripsi tiket Anda.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Laptop Overheat dan Mati Mendadak',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(43),
                'views'        => 489,
                'content'      => <<<'HTML'
<p>Laptop yang tiba-tiba mati saat digunakan, terutama ketika menjalankan aplikasi berat, sering kali disebabkan oleh suhu prosesor yang melampaui batas aman. Sistem mati paksa untuk mencegah kerusakan permanen.</p>

<h2>Tanda-Tanda Overheat</h2>
<ul>
  <li>Bagian bawah laptop sangat panas saat disentuh.</li>
  <li>Kipas berputar kencang terus-menerus.</li>
  <li>Performa menurun drastis (throttling) sebelum mati.</li>
  <li>Layar bergaris atau artefak sebelum shutdown.</li>
</ul>

<h2>Solusi Mandiri</h2>
<ol>
  <li><strong>Pastikan ventilasi tidak tersumbat.</strong> Gunakan alas keras (meja), bukan bantal atau kasur.</li>
  <li><strong>Bersihkan ventilasi dengan blower kecil</strong> dari jarak 10 cm. Debu yang menumpuk di heatsink adalah penyebab paling umum.</li>
  <li><strong>Gunakan cooling pad</strong> — terutama jika beban kerja tinggi (desain grafis, video call panjang).</li>
  <li><strong>Tutup aplikasi yang tidak perlu.</strong> Buka Task Manager, urutkan berdasarkan CPU, dan tutup proses berbeban tinggi yang tidak dikenal.</li>
  <li><strong>Hindari langsung menyalakan ulang</strong> setelah mati akibat panas. Tunggu 5-10 menit agar komponen dingin.</li>
</ol>

<h2>Perlu Perhatian Teknisi</h2>
<p>Jika masalah berulang meski ventilasi bersih, pasta thermal prosesor mungkin perlu diganti. Hubungi IT melalui tiket helpdesk — jangan coba bongkar heatsink sendiri.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Keyboard Laptop Tidak Merespons atau Mengetik Karakter Salah',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(42),
                'views'        => 412,
                'content'      => <<<'HTML'
<p>Keyboard yang tidak merespons atau menampilkan karakter berbeda dari tombol yang ditekan bisa disebabkan oleh masalah software maupun hardware.</p>

<h2>Pemeriksaan Awal</h2>
<ol>
  <li><strong>Coba keyboard eksternal via USB.</strong> Jika keyboard USB berfungsi normal, masalah ada di keyboard internal laptop.</li>
  <li><strong>Periksa Filter Keys.</strong> Buka <em>Settings → Accessibility → Keyboard</em>, pastikan Filter Keys tidak aktif (dapat menyebabkan keyboard seperti lambat/tidak merespons).</li>
  <li><strong>Cek pengaturan bahasa input.</strong> Tekan <kbd>Win</kbd> + <kbd>Space</kbd> untuk melihat layout aktif. Pastikan tidak berganti ke layout keyboard asing (AZERTY, Dvorak, dll.).</li>
  <li><strong>Restart laptop.</strong> Kadang driver keyboard macet dan perlu diinisialisasi ulang.</li>
  <li><strong>Periksa apakah ada tumpahan cairan.</strong> Cairan yang masuk ke sela keyboard dapat menyebabkan tombol lengket atau korsleting.</li>
</ol>

<h2>Update/Reinstall Driver</h2>
<p>Buka <em>Device Manager → Keyboards</em>, klik kanan driver keyboard, pilih <em>Uninstall device</em>, lalu restart. Windows akan menginstal ulang driver secara otomatis.</p>

<h2>Tombol Tertentu Tidak Berfungsi</h2>
<p>Jika hanya beberapa tombol yang tidak merespons, kemungkinan ada sambungan ribbon kabel internal yang longgar atau tombol fisik rusak. Ini memerlukan pemeriksaan teknisi.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Mouse Tidak Bergerak atau Kursor Hilang',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(41),
                'views'        => 387,
                'content'      => <<<'HTML'
<p>Kursor yang tiba-tiba berhenti bergerak atau menghilang dari layar dapat sangat mengganggu produktivitas. Berikut panduan penanganannya.</p>

<h2>Untuk Mouse Eksternal (USB/Wireless)</h2>
<ol>
  <li><strong>Cabut dan pasang ulang</strong> receiver USB atau kabel mouse ke port yang berbeda.</li>
  <li><strong>Ganti baterai</strong> jika mouse wireless. Baterai lemah sering menyebabkan kursor tidak stabil atau tidak merespons.</li>
  <li><strong>Bersihkan sensor bagian bawah mouse</strong> dengan kain kering. Permukaan yang reflektif (kaca, logam mengkilap) dapat mengacaukan sensor optik.</li>
  <li><strong>Coba di komputer lain</strong> untuk memastikan mouse-nya yang bermasalah, bukan portnya.</li>
</ol>

<h2>Untuk Touchpad Laptop</h2>
<ol>
  <li>Tekan <kbd>Fn</kbd> + tombol touchpad (biasanya F6 atau F9) — tombol ini toggle touchpad on/off.</li>
  <li>Pastikan tidak ada mouse USB terpasang yang menonaktifkan touchpad secara otomatis (cek di pengaturan BIOS atau driver).</li>
  <li>Perbarui driver touchpad melalui Device Manager.</li>
</ol>

<h2>Kursor Hilang saat di Atas Jendela Tertentu</h2>
<p>Beberapa aplikasi menyembunyikan kursor default. Tekan <kbd>Esc</kbd> atau pindah ke aplikasi lain. Jika masalah berulang di satu aplikasi, reinstall aplikasi tersebut.</p>

<p>Masalah yang tidak teratasi dengan langkah di atas kemungkinan memerlukan penggantian unit mouse atau pemeriksaan port USB pada motherboard. Buat tiket helpdesk dengan kategori Hardware.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Hard Disk Berbunyi Keras (Klik atau Gerinda)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(40),
                'views'        => 534,
                'content'      => <<<'HTML'
<p>Bunyi klik berulang atau suara gerinda dari hard disk adalah tanda peringatan serius yang tidak boleh diabaikan. Hard disk yang berbunyi demikian berpotensi gagal total dalam waktu singkat.</p>

<h2>⚠️ Langkah Pertama: Backup Segera</h2>
<p>Sebelum melakukan apapun, salin semua file penting ke media lain (flashdisk, hard disk eksternal, atau cloud). Jangan menunda — data dapat hilang kapan saja.</p>

<h2>Bedakan Jenis Bunyi</h2>
<ul>
  <li><strong>Klik-klik periodik:</strong> sering disebabkan head read/write yang gagal menemukan titik referensi (click of death). Tanda kerusakan mekanis serius.</li>
  <li><strong>Gerinda atau desis:</strong> bearing motor mulai aus.</li>
  <li><strong>Bunyi keras saat akses:</strong> bisa hanya getaran konektor longgar, tapi tetap perlu diperiksa.</li>
</ul>

<h2>Cek Kesehatan HDD</h2>
<p>Jalankan <strong>CrystalDiskInfo</strong> (gratis) untuk melihat status S.M.A.R.T. hard disk. Status <em>Caution</em> berarti ada parameter yang tidak normal; status <em>Bad</em> berarti segera ganti.</p>

<h2>Apa yang Harus Dilakukan Selanjutnya</h2>
<p>Buat tiket helpdesk dengan kategori <strong>Hardware</strong> dan sertakan:</p>
<ul>
  <li>Jenis bunyi yang terdengar (klik/gerinda).</li>
  <li>Screenshot hasil CrystalDiskInfo jika bisa dijalankan.</li>
  <li>File penting apa saja yang ada di drive tersebut.</li>
</ul>
<p>Teknisi akan memprioritas penggantian HDD sebelum terjadi kegagalan total.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Flashdisk atau USB Tidak Terdeteksi',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(39),
                'views'        => 445,
                'content'      => <<<'HTML'
<p>USB yang tidak muncul di File Explorer padahal sudah tertancap adalah masalah yang cukup umum dan umumnya bisa diatasi tanpa bantuan teknisi.</p>

<h2>Langkah Pemeriksaan</h2>
<ol>
  <li><strong>Coba port USB lain.</strong> Port mungkin mati atau rusak. Coba juga di komputer lain untuk memastikan flashdisk-nya yang bermasalah.</li>
  <li><strong>Buka Disk Management</strong> (<kbd>Win</kbd>+<kbd>R</kbd> → <code>diskmgmt.msc</code>). Jika USB terdeteksi di sini tapi tidak muncul di File Explorer, USB mungkin tidak memiliki drive letter — klik kanan → <em>Change Drive Letter and Paths</em>.</li>
  <li><strong>Update atau reinstall driver USB.</strong> Buka Device Manager, cari entri dengan tanda seru kuning di <em>Universal Serial Bus controllers</em>, lalu update driver.</li>
  <li><strong>Nonaktifkan USB Selective Suspend.</strong> Buka Power Options → Change plan settings → Change advanced power settings → USB settings → USB selective suspend setting → Disabled.</li>
  <li><strong>Restart Windows Explorer.</strong> Buka Task Manager, cari <em>Windows Explorer</em>, klik kanan → Restart.</li>
</ol>

<h2>USB Terdeteksi tapi Tidak Bisa Dibuka</h2>
<p>Kemungkinan sistem file rusak. Jalankan: buka Command Prompt sebagai Administrator, ketik <code>chkdsk X: /f</code> (ganti X dengan letter drive USB). Proses ini akan memperbaiki error sistem file.</p>

<p>Jika USB tetap tidak terdeteksi di berbagai komputer, kemungkinan besar unit flashdisk sudah rusak.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Monitor Menampilkan Resolusi atau Warna yang Salah',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(38),
                'views'        => 298,
                'content'      => <<<'HTML'
<p>Monitor yang menampilkan resolusi rendah, warna aneh, atau tampilan "gepeng" biasanya disebabkan oleh masalah driver atau koneksi kabel, bukan kerusakan hardware.</p>

<h2>Memperbaiki Resolusi</h2>
<ol>
  <li>Klik kanan desktop → <em>Display settings</em>.</li>
  <li>Pada <em>Display resolution</em>, pilih resolusi yang ditandai <em>(Recommended)</em>. Ini adalah resolusi native monitor.</li>
  <li>Jika opsi resolusi terbatas (hanya sampai 1024×768), kemungkinan driver kartu grafis belum terinstal atau corrupt.</li>
</ol>

<h2>Memperbaiki Driver Kartu Grafis</h2>
<ol>
  <li>Buka <em>Device Manager → Display adapters</em>.</li>
  <li>Jika tertulis <em>Microsoft Basic Display Adapter</em>, driver GPU belum terinstal.</li>
  <li>Klik kanan → <em>Update driver → Search automatically</em>. Jika tidak berhasil, unduh driver dari situs resmi (NVIDIA, AMD, atau Intel) sesuai model GPU.</li>
</ol>

<h2>Masalah Warna (Terlalu Merah/Hijau/Biru)</h2>
<ul>
  <li>Periksa kabel VGA/HDMI — pin yang bengkok di konektor VGA dapat menyebabkan warna hilang.</li>
  <li>Coba kabel berbeda atau port berbeda di monitor.</li>
  <li>Reset pengaturan warna monitor melalui menu OSD (On-Screen Display) tombol fisik monitor.</li>
</ul>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Baterai Laptop Tidak Mau Mengisi Daya',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(37),
                'views'        => 367,
                'content'      => <<<'HTML'
<p>Indikator charger menyala tapi persentase baterai tidak naik, atau laptop hanya bisa hidup saat dicolokan — ini tanda masalah pengisian daya yang perlu didiagnosis dengan cermat.</p>

<h2>Diagnosa Langkah Demi Langkah</h2>
<ol>
  <li><strong>Periksa indikator LED charger.</strong> Jika lampu pada charger tidak menyala, adaptor bermasalah.</li>
  <li><strong>Bersihkan pin konektor charger</strong> dari debu atau kotoran dengan kapas dan alkohol isopropil.</li>
  <li><strong>Coba cabut baterai (laptop lama) dan jalankan hanya dari adaptor.</strong> Jika berhasil, baterai perlu diganti.</li>
  <li><strong>Kalibrasi baterai:</strong> biarkan laptop mati total karena habis baterai, lalu charge tanpa dinyalakan selama 2 jam penuh.</li>
  <li><strong>Update BIOS.</strong> Beberapa laptop membutuhkan update BIOS untuk mengenali baterai pengganti atau memperbaiki bug pengisian.</li>
</ol>

<h2>Pesan "Plugged in, not charging"</h2>
<p>Buka Device Manager → Batteries → klik kanan <em>Microsoft ACPI-Compliant Control Method Battery</em> → <em>Uninstall device</em>. Cabut adaptor, tunggu 30 detik, pasang kembali. Windows akan mendeteksi ulang baterai.</p>

<h2>Baterai Sudah Tua</h2>
<p>Jalankan <code>powercfg /batteryreport</code> di Command Prompt Administrator. Laporan akan menampilkan kapasitas desain vs kapasitas saat ini. Jika kurang dari 60%, pertimbangkan penggantian baterai.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Komputer Tidak Ada Suara (No Audio)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(36),
                'views'        => 401,
                'content'      => <<<'HTML'
<p>Suara yang tiba-tiba hilang bisa disebabkan hal sepele seperti volume yang di-mute, hingga masalah driver yang perlu penanganan lebih lanjut.</p>

<h2>Pemeriksaan Cepat</h2>
<ol>
  <li><strong>Periksa volume.</strong> Klik ikon speaker di taskbar, pastikan tidak mute dan level volume cukup. Cek juga volume di aplikasi yang digunakan (YouTube, Media Player, dll.).</li>
  <li><strong>Periksa perangkat output default.</strong> Klik kanan ikon speaker → <em>Open Sound settings</em> → pastikan output device yang benar dipilih (bukan perangkat yang tidak ada).</li>
  <li><strong>Cabut headset jika terpasang.</strong> Beberapa sistem otomatis beralih ke headset sehingga speaker utama tidak berbunyi.</li>
  <li><strong>Jalankan Audio Troubleshooter.</strong> Klik kanan ikon speaker → <em>Troubleshoot sound problems</em>.</li>
</ol>

<h2>Reinstall Driver Audio</h2>
<ol>
  <li>Buka Device Manager → <em>Sound, video and game controllers</em>.</li>
  <li>Klik kanan driver audio → <em>Uninstall device</em> → centang hapus driver.</li>
  <li>Restart komputer. Windows akan menginstal driver default.</li>
  <li>Untuk driver optimal, unduh dari situs motherboard/laptop (Realtek, IDT, dll.).</li>
</ol>

<h2>Tidak Ada Suara Setelah Update Windows</h2>
<p>Update Windows kadang menimpa driver audio. Solusi cepat: buka <em>Device Manager</em>, klik kanan driver audio → <em>Update driver</em> → <em>Browse my computer → Let me pick</em> → pilih versi driver sebelumnya.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Printer Kertas Macet (Paper Jam)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(35),
                'views'        => 523,
                'content'      => <<<'HTML'
<p>Paper jam adalah masalah paling sering pada printer kantor. Penanganan yang salah justru dapat memperparah kerusakan. Ikuti langkah berikut dengan hati-hati.</p>

<h2>Prosedur Pembersihan Paper Jam</h2>
<ol>
  <li><strong>Matikan printer terlebih dahulu.</strong> Jangan menarik kertas saat printer masih menyala.</li>
  <li><strong>Buka semua cover akses</strong> yang tersedia (cover depan, belakang, dan tray). Periksa setiap jalur kertas secara visual.</li>
  <li><strong>Tarik kertas perlahan searah jalur normal</strong> (biasanya ke bawah atau ke belakang). Jangan tarik berlawanan arah karena dapat merusak roller.</li>
  <li><strong>Periksa sisa kertas robek.</strong> Potongan kecil kertas yang tersisa adalah penyebab paper jam berulang. Gunakan pinset jika diperlukan.</li>
  <li><strong>Nyalakan kembali printer</strong> dan cetak halaman uji (test page) untuk memastikan printer berfungsi normal.</li>
</ol>

<h2>Pencegahan Paper Jam</h2>
<ul>
  <li>Kipas-kipaskan kertas sebelum dimasukkan ke tray agar tidak saling menempel.</li>
  <li>Jangan isi tray melebihi batas maksimum yang tertera.</li>
  <li>Gunakan kertas dengan gramasi sesuai spesifikasi printer (umumnya 70–90 gsm).</li>
  <li>Bersihkan roller kertas dengan kain lembab setiap 3 bulan sekali.</li>
</ul>

<h2>Paper Jam Berulang</h2>
<p>Jika paper jam terjadi terus-menerus meski prosedur di atas sudah dilakukan, roller kertas mungkin sudah aus dan perlu diganti. Laporkan ke IT melalui tiket helpdesk.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Hasil Cetak Printer Bergaris atau Buram',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(34),
                'views'        => 334,
                'content'      => <<<'HTML'
<p>Hasil cetak yang bergaris horizontal, buram, atau bercak menunjukkan masalah pada kartrid atau mekanisme cetak yang biasanya bisa diperbaiki tanpa memanggil teknisi.</p>

<h2>Untuk Printer Inkjet</h2>
<ol>
  <li><strong>Jalankan Head Cleaning</strong> dari software printer (<em>Printer Properties → Maintenance → Head Cleaning</em>). Lakukan 2-3 kali jika perlu.</li>
  <li><strong>Jalankan Nozzle Check</strong> untuk melihat pola mana yang tersumbat.</li>
  <li><strong>Kocok kartrid tinta</strong> (untuk kartrid yang masih ada tinta tapi bermasalah). Keluarkan kartrid, kocok perlahan secara horizontal, pasang kembali.</li>
  <li>Jika tinta habis, ganti kartrid. Gunakan kartrid original atau yang direkomendasikan untuk menghindari masalah lanjutan.</li>
</ol>

<h2>Untuk Printer Laser</h2>
<ol>
  <li><strong>Keluarkan toner cartridge</strong>, kocok perlahan dari kiri ke kanan untuk meratakan toner, pasang kembali. Ini sering memperpanjang usia toner yang hampir habis.</li>
  <li><strong>Bersihkan drum unit</strong> dengan kain kering yang tidak berbulu.</li>
  <li>Jika garis muncul di posisi yang sama pada setiap halaman, kemungkinan ada goresan pada drum — perlu penggantian drum atau toner.</li>
</ol>

<h2>Hasil Cetak Kabur/Berbayang</h2>
<p>Pada printer laser, hasil kabur sering disebabkan fuser unit yang tidak memanaskan dengan baik. Ini memerlukan penggantian komponen oleh teknisi. Buat tiket helpdesk dengan menyertakan sampel hasil cetak bermasalah.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Proyektor Tidak Tampil saat Dihubungkan ke Laptop',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(33),
                'views'        => 456,
                'content'      => <<<'HTML'
<p>Situasi yang memalukan: proyektor sudah terpasang tapi layar tetap kosong saat presentasi. Dengan beberapa langkah sederhana, ini bisa diatasi dalam hitungan detik.</p>

<h2>Langkah Cepat saat Presentasi</h2>
<ol>
  <li><strong>Tekan <kbd>Win</kbd> + <kbd>P</kbd></strong> dan pilih mode tampilan: <em>Duplicate</em> (sama dengan layar laptop), <em>Extend</em>, atau <em>Second screen only</em>.</li>
  <li><strong>Periksa sumber input di proyektor.</strong> Tekan tombol <em>Source</em> atau <em>Input</em> di remote/panel proyektor dan pilih input yang sesuai (HDMI, VGA, atau lainnya).</li>
  <li><strong>Periksa kabel.</strong> Kencangkan konektor di kedua ujung. Kabel VGA yang bengkok pinnya sering menyebabkan tidak ada sinyal.</li>
  <li><strong>Restart proyektor.</strong> Matikan, tunggu 30 detik, nyalakan kembali.</li>
</ol>

<h2>Resolusi Tidak Cocok</h2>
<p>Jika gambar muncul tapi terpotong atau tidak proporsional, atur resolusi laptop ke 1024×768 atau 1280×720 — resolusi yang umum didukung proyektor kantor. Ubah di <em>Display Settings → Resolution</em>.</p>

<h2>Menggunakan Adaptor</h2>
<p>Jika laptop hanya memiliki USB-C atau Mini DisplayPort, pastikan adaptor yang digunakan berkualitas baik. Adaptor murah sering menjadi sumber masalah sinyal tidak stabil.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Webcam Tidak Bekerja di Aplikasi Video Call',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(32),
                'views'        => 378,
                'content'      => <<<'HTML'
<p>Kamera yang tidak muncul saat video call bisa mengganggu rapat penting. Masalah ini umumnya berkaitan dengan izin akses aplikasi atau driver.</p>

<h2>Periksa Izin Kamera Windows</h2>
<ol>
  <li>Buka <em>Settings → Privacy & security → Camera</em>.</li>
  <li>Pastikan <em>Camera access</em> dalam kondisi <strong>On</strong>.</li>
  <li>Gulir ke bawah dan pastikan aplikasi yang digunakan (Teams, Zoom, Google Meet) memiliki izin kamera.</li>
</ol>

<h2>Periksa Tutup Privasi Fisik</h2>
<p>Banyak laptop modern memiliki penutup privasi fisik di atas webcam. Geser atau buka penutup tersebut jika ada.</p>

<h2>Pilih Kamera yang Benar di Aplikasi</h2>
<p>Di pengaturan video aplikasi, pilih kamera yang tepat. Jika tersambung beberapa kamera (webcam eksternal + internal), aplikasi mungkin memilih yang salah.</p>

<h2>Reinstall Driver Webcam</h2>
<ol>
  <li>Buka <em>Device Manager → Cameras</em> atau <em>Imaging devices</em>.</li>
  <li>Klik kanan driver webcam → <em>Uninstall device</em>.</li>
  <li>Scan for hardware changes atau restart untuk instal ulang driver.</li>
</ol>

<h2>Konflik Aplikasi</h2>
<p>Hanya satu aplikasi yang dapat mengakses webcam secara bersamaan. Tutup aplikasi lain yang mungkin menggunakan kamera (Skype, Teams di background, dll.) sebelum membuka aplikasi video call.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Komputer Restart Sendiri Berulang Kali',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(31),
                'views'        => 467,
                'content'      => <<<'HTML'
<p>Komputer yang tiba-tiba restart sendiri — terutama tanpa peringatan — bisa disebabkan masalah hardware, driver, atau sistem operasi. Identifikasi penyebabnya sebelum mengambil tindakan.</p>

<h2>Nonaktifkan Automatic Restart untuk Melihat Error</h2>
<ol>
  <li>Klik kanan <em>This PC → Properties → Advanced system settings</em>.</li>
  <li>Di bagian <em>Startup and Recovery</em>, klik <em>Settings</em>.</li>
  <li>Hapus centang <em>Automatically restart</em>. Sekarang jika terjadi crash, akan muncul BSOD dengan kode error yang bisa dilaporkan.</li>
</ol>

<h2>Penyebab dan Solusi Umum</h2>
<ul>
  <li><strong>Overheating</strong> — periksa suhu prosesor dengan HWMonitor. Bersihkan kipas dan ventilasi.</li>
  <li><strong>Driver rusak</strong> — terutama driver kartu grafis atau chipset. Update atau rollback driver setelah update terbaru.</li>
  <li><strong>RAM bermasalah</strong> — jalankan <em>Windows Memory Diagnostic</em> (cari di Start Menu) untuk memindai error memori.</li>
  <li><strong>PSU tidak stabil</strong> — power supply yang lemah tidak mampu menyuplai daya saat beban puncak.</li>
  <li><strong>Malware</strong> — jalankan full scan antivirus.</li>
</ul>

<h2>Cara Membaca Event Log</h2>
<p>Buka <em>Event Viewer</em> (cari di Start Menu) → <em>Windows Logs → System</em>. Cari entri <em>Critical</em> atau <em>Error</em> dengan waktu tepat sebelum restart. Catat Event ID dan sertakan dalam tiket helpdesk jika perlu eskalasi.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Touchpad Laptop Tidak Responsif',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(30),
                'views'        => 289,
                'content'      => <<<'HTML'
<p>Touchpad yang tidak merespons sentuhan atau tidak bergerak sama sekali seringkali dapat diselesaikan dalam beberapa langkah sederhana.</p>

<h2>Pemeriksaan Cepat</h2>
<ol>
  <li><strong>Cek tombol Fn.</strong> Hampir semua laptop memiliki kombinasi <kbd>Fn</kbd> + tombol touchpad untuk mengaktifkan/menonaktifkan touchpad. Tekan sekali untuk toggle.</li>
  <li><strong>Cabut mouse USB.</strong> Beberapa laptop menonaktifkan touchpad otomatis saat mouse terpasang. Cabut mouse dan periksa apakah touchpad aktif kembali.</li>
  <li><strong>Restart laptop.</strong> Driver touchpad yang crash akan dipulihkan saat reboot.</li>
  <li><strong>Periksa pengaturan touchpad.</strong> Settings → Bluetooth & devices → Touchpad → pastikan toggle <em>Touchpad</em> dalam posisi On.</li>
</ol>

<h2>Update Driver Touchpad</h2>
<p>Unduh driver touchpad terbaru dari situs resmi produsen laptop (Dell, HP, Lenovo, ASUS, dll.). Driver Synaptics atau ELAN yang sudah usang sering menyebabkan touchpad tidak responsif setelah update Windows.</p>

<h2>Touchpad Bergerak Sendiri</h2>
<p>Jika kursor bergerak sendiri tanpa disentuh, kemungkinan ada tekanan dari bagian bawah laptop (baterai mengembung) atau sensitivity touchpad terlalu tinggi. Kurangi sensitivity di Settings → Touchpad → Sensitivity.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Monitor Berkedip-kedip (Flicker)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(29),
                'views'        => 312,
                'content'      => <<<'HTML'
<p>Monitor yang berkedip dapat melelahkan mata dan mengganggu fokus. Penyebabnya bisa dari refresh rate, kabel, driver, atau kondisi fisik monitor itu sendiri.</p>

<h2>Atur Refresh Rate yang Tepat</h2>
<ol>
  <li>Klik kanan desktop → <em>Display settings → Advanced display settings</em>.</li>
  <li>Ubah <em>Refresh rate</em> ke nilai yang disarankan (biasanya 60Hz untuk monitor kantor standar, 75Hz untuk yang lebih baru).</li>
</ol>

<h2>Periksa Kabel</h2>
<ul>
  <li>Ganti kabel HDMI atau VGA dengan yang baru. Kabel yang rusak atau terlalu panjang dapat menyebabkan sinyal tidak stabil.</li>
  <li>Hindari menekuk kabel secara tajam.</li>
  <li>Coba port output yang berbeda di laptop/GPU.</li>
</ul>

<h2>Update Driver Grafis</h2>
<p>Driver kartu grafis yang usang atau corrupt adalah penyebab flicker yang sering terlewatkan. Unduh dan instal driver terbaru dari NVIDIA, AMD, atau Intel sesuai GPU Anda.</p>

<h2>Flicker Hanya di Satu Sisi atau Area Tertentu</h2>
<p>Ini mengindikasikan backlight yang mulai rusak atau panel monitor bermasalah — bukan masalah software. Segera buat tiket helpdesk karena kerusakan dapat meluas jika dibiarkan.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Port HDMI Laptop Tidak Mengeluarkan Gambar',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(28),
                'views'        => 267,
                'content'      => <<<'HTML'
<p>Port HDMI yang tidak berfungsi membuat Anda tidak bisa menghubungkan laptop ke monitor atau proyektor eksternal. Berikut cara mendiagnosisnya.</p>

<h2>Langkah Diagnosis</h2>
<ol>
  <li><strong>Coba kabel HDMI berbeda.</strong> Kabel HDMI bisa rusak secara internal tanpa tanda fisik yang terlihat.</li>
  <li><strong>Coba port HDMI berbeda di monitor/TV tujuan.</strong> Monitor biasanya memiliki lebih dari satu port HDMI.</li>
  <li><strong>Tekan <kbd>Win</kbd>+<kbd>P</kbd></strong> dan pilih <em>Duplicate</em> atau <em>Second screen only</em>.</li>
  <li><strong>Update driver kartu grafis.</strong> Driver yang usang sering menyebabkan HDMI tidak dideteksi.</li>
  <li><strong>Periksa di Display Settings</strong> apakah monitor kedua terdeteksi. Jika ya, klik <em>Detect</em> atau atur tampilan secara manual.</li>
</ol>

<h2>Port HDMI Fisik Bermasalah</h2>
<p>Jika konektor HDMI terasa longgar atau bergoyang saat dipasang, mungkin pin di dalam port bengkok atau solder di motherboard retak. Ini memerlukan perbaikan hardware oleh teknisi.</p>

<h2>Alternatif Sementara</h2>
<p>Gunakan adaptor USB-C to HDMI atau docking station sebagai solusi sementara sambil menunggu perbaikan. Hubungi IT untuk ketersediaan perangkat peminjaman.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Komputer Berbunyi Beep saat Dinyalakan',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(27),
                'views'        => 398,
                'content'      => <<<'HTML'
<p>Bunyi beep saat POST (Power-On Self Test) adalah bahasa komunikasi motherboard tentang kerusakan yang ditemukannya. Pola bunyi membantu mengidentifikasi komponen yang bermasalah.</p>

<h2>Tabel Pola Beep Umum (BIOS AMI/AWARD)</h2>
<table style="border-collapse:collapse;width:100%">
  <tr style="background:#f3f4f6"><th style="padding:8px;border:1px solid #ddd">Pola Beep</th><th style="padding:8px;border:1px solid #ddd">Kemungkinan Penyebab</th></tr>
  <tr><td style="padding:8px;border:1px solid #ddd">1 beep pendek</td><td style="padding:8px;border:1px solid #ddd">POST berhasil — normal</td></tr>
  <tr><td style="padding:8px;border:1px solid #ddd">1 beep panjang terus-menerus</td><td style="padding:8px;border:1px solid #ddd">Masalah RAM (tidak terpasang atau rusak)</td></tr>
  <tr><td style="padding:8px;border:1px solid #ddd">1 panjang + 2 pendek</td><td style="padding:8px;border:1px solid #ddd">Masalah kartu grafis</td></tr>
  <tr><td style="padding:8px;border:1px solid #ddd">3 beep berulang</td><td style="padding:8px;border:1px solid #ddd">Kegagalan RAM</td></tr>
  <tr><td style="padding:8px;border:1px solid #ddd">5 beep</td><td style="padding:8px;border:1px solid #ddd">Masalah prosesor</td></tr>
</table>

<h2>Yang Bisa Dicoba Sendiri</h2>
<ol>
  <li><strong>Perbaiki posisi RAM.</strong> Matikan, cabut RAM dari slotnya, bersihkan konektor emas dengan penghapus pensil, pasang kembali dengan kencang.</li>
  <li><strong>Coba satu stik RAM</strong> jika ada dua, bergantian, untuk mengetahui stik mana yang bermasalah.</li>
  <li><strong>Cabut kartu grafis discrete</strong> (jika ada) dan coba boot dengan grafis onboard.</li>
</ol>

<p>Catat pola beep dengan teliti dan sertakan dalam tiket helpdesk — informasi ini sangat membantu teknisi menyiapkan komponen pengganti yang tepat.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Headset atau Mikrofon Tidak Terdeteksi',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(26),
                'views'        => 276,
                'content'      => <<<'HTML'
<p>Headset yang tidak dikenali sistem dapat mengganggu rapat online dan komunikasi. Masalah ini umumnya mudah diselesaikan.</p>

<h2>Headset Jack 3.5mm</h2>
<ol>
  <li><strong>Pastikan menggunakan port yang benar.</strong> Laptop modern sering menggabungkan port headphone dan mikrofon menjadi satu jack TRRS (4 kutub). Headset dengan konektor TRRS mungkin tidak berfungsi penuh di port TRS biasa — gunakan splitter jika perlu.</li>
  <li><strong>Set sebagai default device.</strong> Klik kanan ikon speaker → <em>Open Sound settings</em> → pilih headset sebagai output dan input default.</li>
  <li><strong>Periksa di Recording tab.</strong> Klik kanan ikon speaker → <em>Sound</em> → tab <em>Recording</em> → klik kanan area kosong → <em>Show Disabled Devices</em>. Aktifkan mikrofon headset jika tersembunyi.</li>
</ol>

<h2>Headset USB</h2>
<ol>
  <li>Coba port USB berbeda.</li>
  <li>Buka <em>Device Manager → Sound, video and game controllers</em> — pastikan headset USB muncul tanpa tanda error.</li>
  <li>Update driver USB audio controller.</li>
</ol>

<h2>Izin Privasi Mikrofon</h2>
<p>Buka <em>Settings → Privacy & security → Microphone</em> → pastikan akses mikrofon dan izin untuk aplikasi yang Anda gunakan dalam kondisi On.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Charger Laptop Panas Berlebihan',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(25),
                'views'        => 445,
                'content'      => <<<'HTML'
<p>Adaptor charger yang panas wajar terjadi, tetapi panas berlebihan (terasa membakar saat disentuh) adalah tanda bahaya yang perlu segera ditangani.</p>

<h2>Batas Normal vs Berbahaya</h2>
<ul>
  <li><strong>Hangat (~40-50°C):</strong> Normal saat mengisi daya.</li>
  <li><strong>Panas (~55-65°C):</strong> Perlu diperhatikan. Pastikan ventilasi charger tidak tertutup.</li>
  <li><strong>Sangat panas (&gt;70°C / tidak bisa dipegang):</strong> <strong>Berbahaya.</strong> Segera cabut dari stopkontak.</li>
</ul>

<h2>Penyebab Charger Terlalu Panas</h2>
<ul>
  <li>Charger terkubur di bawah bantal atau karpet yang menghambat disipasi panas.</li>
  <li>Charger tidak original atau palsu — kualitas komponen lebih rendah.</li>
  <li>Kabel atau konektor yang rusak menyebabkan hambatan listrik berlebih.</li>
  <li>Charger berkapasitas tidak sesuai (watt terlalu rendah untuk laptop).</li>
</ul>

<h2>Tindakan yang Harus Dilakukan</h2>
<ol>
  <li>Letakkan charger di permukaan keras dengan ruang terbuka.</li>
  <li>Periksa kabel — jika ada bagian yang terkelupas, bengkok, atau terbakar, <strong>hentikan penggunaan segera.</strong></li>
  <li>Hubungi IT untuk penggantian charger original. Jangan gunakan charger yang tidak sesuai spesifikasi laptop karena berisiko merusak baterai dan motherboard.</li>
</ol>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: RAM Tidak Terbaca Semua oleh Sistem',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(24),
                'views'        => 223,
                'content'      => <<<'HTML'
<p>Windows melaporkan RAM lebih kecil dari yang terpasang? Ini masalah yang dapat terjadi karena berbagai alasan mulai dari setting BIOS hingga pembatasan sistem operasi.</p>

<h2>Langkah Pemeriksaan</h2>
<ol>
  <li><strong>Cek di System Information.</strong> Tekan <kbd>Win</kbd>+<kbd>R</kbd> → <code>msinfo32</code>. Lihat <em>Installed Physical Memory (RAM)</em> vs <em>Total Physical Memory</em>. Perbedaan kecil (misalnya 16 GB terpasang, 15.9 GB tersedia) adalah normal karena digunakan oleh BIOS/GPU onboard.</li>
  <li><strong>Periksa Windows edition.</strong> Windows Home 32-bit hanya mendukung maksimal 4 GB RAM. Pastikan Anda menggunakan edisi 64-bit.</li>
  <li><strong>Cek Maximum Memory di MSCONFIG.</strong> Tekan <kbd>Win</kbd>+<kbd>R</kbd> → <code>msconfig</code> → tab <em>Boot → Advanced options</em> → pastikan <em>Maximum memory</em> tidak dicentang atau nilainya benar.</li>
  <li><strong>Periksa slot RAM.</strong> Buka Device Manager atau HWiNFO untuk melihat apakah semua slot terdeteksi. Slot yang rusak tidak akan menampilkan stik RAM yang terpasang di dalamnya.</li>
</ol>

<h2>Perbaikan di BIOS</h2>
<p>Beberapa BIOS memiliki pengaturan memory remap atau iGPU memory yang mempengaruhi RAM yang terlihat sistem. Minta bantuan IT untuk menyesuaikan pengaturan BIOS — jangan mengubah BIOS sendiri tanpa pengetahuan memadai.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Scanner Tidak Mendeteksi Dokumen',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(23),
                'views'        => 198,
                'content'      => <<<'HTML'
<p>Scanner yang tidak merespons atau tidak mendeteksi dokumen dapat disebabkan oleh masalah koneksi, driver, atau konfigurasi software.</p>

<h2>Pemeriksaan Dasar</h2>
<ol>
  <li><strong>Pastikan scanner dalam posisi Ready</strong> (lampu indikator hijau stabil, bukan berkedip).</li>
  <li><strong>Periksa koneksi USB atau jaringan.</strong> Untuk scanner jaringan, pastikan IP address scanner masih sama — IP bisa berubah jika DHCP melakukan re-assign.</li>
  <li><strong>Letakkan dokumen dengan benar.</strong> Pastikan tidak ada kertas miring yang memicu sensor keamanan. Beberapa scanner ADF memiliki sensor kecil yang harus tertutup dokumen.</li>
</ol>

<h2>Masalah Driver dan Software</h2>
<ol>
  <li>Buka <em>Devices and Printers</em> — scanner seharusnya terlihat. Jika ada tanda seru, klik kanan → troubleshoot.</li>
  <li>Reinstall software scanner dari CD bawaan atau situs resmi produsen (Canon, Epson, HP, dll.).</li>
  <li>Restart layanan Windows Image Acquisition: <kbd>Win</kbd>+<kbd>R</kbd> → <code>services.msc</code> → cari <em>Windows Image Acquisition (WIA)</em> → Restart.</li>
</ol>

<h2>Scan dari Aplikasi Berbeda</h2>
<p>Coba gunakan <em>Windows Fax and Scan</em> (bawaan Windows) sebagai pengganti software scanner. Jika berhasil di sini tapi tidak di aplikasi lain, masalah ada di software, bukan driver.</p>
HTML,
            ],

            /* ── SOFTWARE 26-50 ───────────────────────────────────────────── */

            [
                'title'        => 'Troubleshooting: Windows Tidak Bisa Booting (Stuck di Loading)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(22),
                'views'        => 678,
                'content'      => <<<'HTML'
<p>Windows yang stuck di layar loading (logo Windows berputar terus) atau langsung restart loop setelah splash screen adalah salah satu kasus yang paling sering membutuhkan intervensi IT.</p>

<h2>Langkah Pemulihan Mandiri</h2>
<ol>
  <li><strong>Boot ke Safe Mode.</strong> Restart komputer, tekan <kbd>F8</kbd> atau <kbd>Shift</kbd>+<kbd>F8</kbd> berulang saat logo muncul. Pilih <em>Safe Mode with Networking</em>. Jika berhasil masuk, penyebabnya adalah driver atau aplikasi startup yang rusak.</li>
  <li><strong>Gunakan Startup Repair.</strong> Boot dari USB/DVD Windows → pilih <em>Repair your computer → Troubleshoot → Advanced options → Startup Repair</em>.</li>
  <li><strong>Jalankan System Restore</strong> dari Advanced Options untuk kembali ke titik pemulihan sebelum masalah terjadi.</li>
  <li><strong>Perbaiki Boot Record:</strong> di Command Prompt dari Advanced Options, jalankan:
    <pre>bootrec /fixmbr
bootrec /fixboot
bootrec /rebuildbcd</pre>
  </li>
</ol>

<h2>Penyebab Umum</h2>
<ul>
  <li>Windows Update yang gagal di tengah proses.</li>
  <li>Driver yang tidak kompatibel terpasang.</li>
  <li>File sistem Windows corrupt.</li>
  <li>Hard disk/SSD mulai rusak.</li>
</ul>

<p>Jika semua langkah gagal, buat tiket dengan prioritas <strong>High</strong> — teknisi perlu membawa laptop untuk pemeriksaan lebih lanjut.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Blue Screen of Death (BSOD) pada Windows',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(21),
                'views'        => 721,
                'content'      => <<<'HTML'
<p>BSOD (layar biru dengan pesan error) terjadi ketika Windows mengalami kegagalan kritis yang tidak dapat ditangani. Kode error yang ditampilkan adalah kunci untuk diagnosis.</p>

<h2>Kode BSOD yang Paling Umum</h2>
<ul>
  <li><code>DRIVER_IRQL_NOT_LESS_OR_EQUAL</code> — driver yang tidak kompatibel atau corrupt, paling sering driver jaringan atau GPU.</li>
  <li><code>MEMORY_MANAGEMENT</code> — masalah RAM (fisik atau driver).</li>
  <li><code>CRITICAL_PROCESS_DIED</code> — file sistem Windows corrupt.</li>
  <li><code>SYSTEM_SERVICE_EXCEPTION</code> — driver pihak ketiga bermasalah.</li>
  <li><code>NTFS_FILE_SYSTEM</code> — hard disk/SSD bermasalah.</li>
</ul>

<h2>Langkah Penanganan</h2>
<ol>
  <li><strong>Catat kode BSOD</strong> atau foto layar biru tersebut sebelum komputer restart.</li>
  <li><strong>Periksa Windows Event Viewer</strong> (cari di Start) → <em>Windows Logs → System</em> → filter event Critical untuk melihat log crash.</li>
  <li><strong>Update semua driver</strong>, terutama driver GPU dan chipset.</li>
  <li><strong>Jalankan SFC:</strong> buka Command Prompt Administrator, ketik <code>sfc /scannow</code>. Proses ini memeriksa dan memperbaiki file sistem Windows yang rusak.</li>
  <li><strong>Jalankan Windows Memory Diagnostic</strong> untuk memeriksa RAM.</li>
</ol>

<h2>BSOD Terjadi Setelah Instal Hardware/Software Baru</h2>
<p>Uninstal perangkat keras atau software tersebut dan periksa apakah BSOD berhenti. Buat tiket helpdesk dengan menyertakan kode BSOD dan foto layar biru untuk penanganan lebih lanjut.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Aplikasi Tiba-tiba Not Responding',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(20),
                'views'        => 534,
                'content'      => <<<'HTML'
<p>Aplikasi yang tiba-tiba freeze dan menampilkan "(Not Responding)" di judul jendela bisa disebabkan oleh memori penuh, proses yang berbenturan, atau bug dalam aplikasi itu sendiri.</p>

<h2>Penanganan Segera</h2>
<ol>
  <li><strong>Tunggu beberapa saat.</strong> Aplikasi mungkin sedang memproses tugas berat. Jangan klik berulang kali — itu justru menambah antrian perintah yang memperparah freeze.</li>
  <li><strong>Force close melalui Task Manager.</strong> Tekan <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>Esc</kbd>, klik kanan aplikasi bermasalah → <em>End Task</em>. Simpan pekerjaan sebelumnya jika ada dialog recovery.</li>
</ol>

<h2>Mengatasi Masalah Berulang</h2>
<ul>
  <li><strong>Kosongkan RAM.</strong> Tutup aplikasi yang tidak perlu. Tambahkan RAM jika komputer sering kehabisan memori.</li>
  <li><strong>Reinstall aplikasi</strong> jika hanya satu aplikasi yang sering hang. Hapus folder cache aplikasi sebelum reinstall.</li>
  <li><strong>Perbarui aplikasi</strong> ke versi terbaru — bug yang menyebabkan freeze biasanya diperbaiki di update.</li>
  <li><strong>Periksa hard disk.</strong> Aplikasi yang lambat membaca/menulis data dari disk yang hampir penuh atau bermasalah dapat menyebabkan not responding. Kosongkan space minimal 15% kapasitas total.</li>
</ul>

<h2>Aplikasi Office (Word/Excel) Not Responding</h2>
<p>Matikan Add-in yang tidak perlu: <em>File → Options → Add-ins → Manage: COM Add-ins → Go</em> → nonaktifkan add-in pihak ketiga satu per satu untuk menemukan penyebabnya.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Windows Update Gagal atau Stuck',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(19),
                'views'        => 389,
                'content'      => <<<'HTML'
<p>Windows Update yang gagal dengan kode error atau stuck di persentase tertentu adalah masalah umum. Berikut cara mengatasinya tanpa install ulang.</p>

<h2>Cara 1: Windows Update Troubleshooter</h2>
<p>Buka <em>Settings → System → Troubleshoot → Other troubleshooters → Windows Update → Run</em>. Tool ini secara otomatis mendeteksi dan memperbaiki masalah update umum.</p>

<h2>Cara 2: Reset Windows Update Components</h2>
<p>Buka Command Prompt sebagai Administrator, jalankan perintah berikut satu per satu:</p>
<pre>net stop wuauserv
net stop cryptSvc
net stop bits
net stop msiserver
ren C:\Windows\SoftwareDistribution SoftwareDistribution.old
ren C:\Windows\System32\catroot2 Catroot2.old
net start wuauserv
net start cryptSvc
net start bits
net start msiserver</pre>
<p>Restart komputer, lalu coba update kembali.</p>

<h2>Cara 3: Manual Update via Catalog</h2>
<p>Jika update tertentu terus gagal, unduh secara manual dari <em>catalog.update.microsoft.com</em> menggunakan nomor KB yang ditampilkan di detail error. Instal secara manual dengan klik dua kali file .msu yang diunduh.</p>

<h2>Ruang Disk Tidak Cukup</h2>
<p>Windows Update membutuhkan minimal 10-20 GB ruang kosong. Bersihkan disk dengan <em>Storage Sense</em> atau hapus file Temporary Windows Update yang lama dari <code>C:\Windows\SoftwareDistribution\Download</code>.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Driver Perangkat Tidak Terinstal dengan Benar',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(18),
                'views'        => 312,
                'content'      => <<<'HTML'
<p>Tanda seru kuning di Device Manager menunjukkan driver yang bermasalah. Ini dapat menyebabkan perangkat tidak bekerja optimal atau sama sekali.</p>

<h2>Cara Membaca Device Manager</h2>
<ol>
  <li>Klik kanan <em>This PC → Properties → Device Manager</em> atau cari "Device Manager" di Start.</li>
  <li>Ikon tanda seru (!) kuning = driver error.</li>
  <li>Ikon tanda tanya (?) = driver tidak dikenali.</li>
  <li>Ikon panah bawah = perangkat dinonaktifkan.</li>
</ol>

<h2>Cara Menginstal Driver yang Benar</h2>
<ol>
  <li><strong>Update otomatis:</strong> klik kanan perangkat → <em>Update driver → Search automatically</em>. Cocok untuk driver umum.</li>
  <li><strong>Download manual:</strong> cari tahu model perangkat (klik kanan → Properties → tab Details → Hardware Ids), lalu unduh driver dari situs produsen.</li>
  <li><strong>Gunakan software driver:</strong> untuk laptop merek tertentu, gunakan Dell Support Assist, HP Support Assistant, atau Lenovo Vantage yang otomatis mendeteksi dan menginstal driver yang diperlukan.</li>
</ol>

<h2>Setelah Windows Update</h2>
<p>Windows Update kadang mengganti driver pihak ketiga dengan versi generic. Jika perangkat bermasalah setelah update, coba rollback driver: klik kanan perangkat → Properties → tab Driver → <em>Roll Back Driver</em>.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Browser Tidak Bisa Membuka Halaman Web',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(17),
                'views'        => 498,
                'content'      => <<<'HTML'
<p>"This site can't be reached" atau halaman error adalah gangguan yang bisa disebabkan oleh koneksi, DNS, atau konfigurasi browser itu sendiri.</p>

<h2>Diagnosa Cepat</h2>
<ol>
  <li><strong>Cek koneksi internet.</strong> Buka Command Prompt, ketik <code>ping 8.8.8.8</code>. Jika ada reply, internet terhubung dan masalah ada di DNS atau browser.</li>
  <li><strong>Coba browser lain</strong> (Edge, Firefox, Chrome). Jika berhasil di browser lain, masalah spesifik di browser tertentu.</li>
  <li><strong>Buka dalam mode Incognito/Private.</strong> Jika berhasil, masalah ada di ekstensi atau cache browser.</li>
</ol>

<h2>Bersihkan Cache dan Cookie</h2>
<p>Di Chrome: <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>Delete</kbd> → pilih rentang waktu <em>All time</em> → centang Cached images/files dan Cookies → Clear data.</p>

<h2>Reset DNS</h2>
<p>Buka Command Prompt Administrator:</p>
<pre>ipconfig /flushdns
ipconfig /release
ipconfig /renew
netsh winsock reset</pre>
<p>Restart komputer setelahnya.</p>

<h2>Nonaktifkan Ekstensi Browser</h2>
<p>Ekstensi VPN, ad-blocker, atau proxy yang salah konfigurasi dapat memblokir akses web. Nonaktifkan semua ekstensi di <em>Settings → Extensions</em> dan coba kembali.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: File Microsoft Word Rusak (Corrupt) Tidak Bisa Dibuka',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(16),
                'views'        => 445,
                'content'      => <<<'HTML'
<p>Dokumen Word yang tiba-tiba tidak bisa dibuka atau menampilkan pesan error sering kali masih bisa dipulihkan dengan beberapa teknik berikut.</p>

<h2>Metode Pemulihan Bawaan Word</h2>
<ol>
  <li>Buka Word (tanpa membuka file).</li>
  <li>Klik <em>File → Open → Browse</em>, navigasi ke file yang bermasalah.</li>
  <li>Klik panah kecil di sebelah tombol <em>Open</em> → pilih <strong>Open and Repair</strong>.</li>
</ol>

<h2>Gunakan Fitur Text Recovery Converter</h2>
<ol>
  <li><em>File → Open</em>, di dropdown tipe file pilih <em>Recover Text from Any File (*.*)</em>.</li>
  <li>Pilih file yang rusak. Word akan mengekstrak teks semampunya, meski pemformatan mungkin hilang.</li>
</ol>

<h2>AutoRecover</h2>
<p>Word secara otomatis menyimpan file sementara. Buka Word, klik <em>File → Info → Manage Document → Recover Unsaved Documents</em>. Cari file dengan nama dan tanggal yang sesuai.</p>

<h2>Gunakan Previous Versions</h2>
<p>Klik kanan file di File Explorer → <em>Properties → Previous Versions</em>. Windows atau OneDrive mungkin memiliki versi cadangan sebelum file rusak.</p>

<h2>Pencegahan</h2>
<ul>
  <li>Aktifkan AutoSave dan simpan ke OneDrive agar ada versi cloud yang selalu tersedia.</li>
  <li>Jangan tutup paksa Word saat sedang menyimpan.</li>
  <li>Cabut flashdisk dengan aman (Safely Remove) sebelum mencabut.</li>
</ul>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Excel Formula Tidak Menghasilkan Nilai yang Benar',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(15),
                'views'        => 367,
                'content'      => <<<'HTML'
<p>Formula Excel yang menampilkan hasil salah, #ERROR, atau teks literal formula adalah masalah yang sangat umum di lingkungan kantor.</p>

<h2>Formula Menampilkan Teks Literal (Tidak Dihitung)</h2>
<ul>
  <li>Sel mungkin diformat sebagai <em>Text</em>. Ubah format sel ke <em>General</em> atau <em>Number</em> melalui <em>Home → Number Format</em>, lalu tekan <kbd>F2</kbd> → <kbd>Enter</kbd> untuk memaksa kalkulasi ulang.</li>
  <li>Pastikan formula dimulai dengan tanda sama dengan (<code>=</code>).</li>
  <li>Cek apakah <em>Show Formulas</em> aktif: <kbd>Ctrl</kbd>+<kbd>`</kbd> (backtick) untuk toggle.</li>
</ul>

<h2>Kode Error Umum</h2>
<ul>
  <li><code>#DIV/0!</code> — pembagian dengan nol atau sel kosong. Gunakan <code>=IFERROR(A1/B1, 0)</code>.</li>
  <li><code>#REF!</code> — referensi sel tidak valid (sel yang dirujuk dihapus). Perbarui formula.</li>
  <li><code>#VALUE!</code> — tipe data tidak sesuai (teks di sel yang seharusnya angka). Periksa data sumber.</li>
  <li><code>#N/A</code> — nilai tidak ditemukan (umum pada VLOOKUP). Pastikan nilai pencarian ada di tabel referensi.</li>
  <li><code>#NAME?</code> — nama fungsi salah ketik. Periksa ejaan nama fungsi.</li>
</ul>

<h2>Calculation Mode Manual</h2>
<p>Jika nilai tidak berubah meski data sudah diedit, periksa <em>Formulas → Calculation Options → Automatic</em>. Mode manual tidak menghitung ulang formula secara otomatis.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Outlook Tidak Bisa Mengirim atau Menerima Email',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(14),
                'views'        => 412,
                'content'      => <<<'HTML'
<p>Email yang terjebak di Outbox atau inbox yang tidak memperbarui pesan baru adalah gangguan produktivitas yang perlu diselesaikan segera.</p>

<h2>Email Terjebak di Outbox</h2>
<ol>
  <li>Buka folder <em>Outbox</em>, pindahkan email ke <em>Drafts</em> (drag & drop) untuk menghentikan proses pengiriman yang macet.</li>
  <li>Offline mode mungkin aktif: cek menu <em>Send/Receive → Work Offline</em> — pastikan tidak ada tanda centang.</li>
  <li>Periksa ukuran lampiran — batas ukuran email server biasanya 25 MB.</li>
</ol>

<h2>Inbox Tidak Memperbarui Pesan</h2>
<ol>
  <li>Tekan <kbd>F9</kbd> atau klik <em>Send/Receive All Folders</em> untuk paksa sinkronisasi.</li>
  <li>Periksa indikator koneksi di pojok kanan bawah Outlook. Jika tertulis <em>Disconnected</em> atau <em>Trying to connect</em>, ada masalah koneksi ke server.</li>
  <li>Restart Outlook dalam Safe Mode: <kbd>Win</kbd>+<kbd>R</kbd> → <code>outlook /safe</code>. Jika berhasil di Safe Mode, masalah disebabkan Add-in.</li>
</ol>

<h2>Repair Profil Outlook</h2>
<p>Buka <em>Control Panel → Mail → Show Profiles → pilih profil → Properties → Email Accounts → Repair</em>. Ikuti wizard untuk memperbaiki konfigurasi akun secara otomatis.</p>

<h2>Rebuild OST/PST File</h2>
<p>File data Outlook yang besar atau corrupt perlu diperbaiki dengan SCANPST.EXE (cari di folder instalasi Office). Hubungi IT jika tidak menemukan tool ini.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Tidak Bisa Menginstal Aplikasi di Windows',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(13),
                'views'        => 289,
                'content'      => <<<'HTML'
<p>Pesan "You don't have permission to install" atau installer yang langsung menutup tanpa proses adalah masalah yang umumnya berkaitan dengan hak akses atau Group Policy.</p>

<h2>Penyebab Umum dan Solusinya</h2>

<h3>1. Kurang Hak Administrator</h3>
<p>Klik kanan file installer → <em>Run as administrator</em>. Jika diminta password, hubungi IT untuk proseskan instalasi.</p>

<h3>2. UAC Memblokir Instalasi</h3>
<p>User Account Control (UAC) dapat memblokir aplikasi tertentu. Pastikan Anda mengklik <em>Yes</em> pada prompt UAC yang muncul. Jika tidak muncul, mungkin aplikasi diblokir oleh Group Policy.</p>

<h3>3. Windows SmartScreen Memblokir</h3>
<p>Klik <em>More info → Run anyway</em> jika Anda yakin aplikasi aman. SmartScreen hanya memblokir aplikasi tanpa sertifikat digital terverifikasi, bukan berarti selalu berbahaya.</p>

<h3>4. Antivirus Memblokir</h3>
<p>Nonaktifkan sementara perlindungan real-time antivirus, instal aplikasi, lalu aktifkan kembali. Atau tambahkan pengecualian untuk installer tersebut.</p>

<h3>5. Ruang Disk Tidak Cukup</h3>
<p>Pastikan drive C: memiliki ruang kosong yang cukup. Beberapa installer membutuhkan 2-3x ukuran aplikasi untuk sementara.</p>

<p>Untuk instalasi software resmi kantor, selalu ajukan permintaan melalui tiket helpdesk agar IT dapat memproses dengan hak yang sesuai.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Koneksi VPN Gagal atau Terputus',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(12),
                'views'        => 334,
                'content'      => <<<'HTML'
<p>VPN yang tidak bisa terhubung atau sering putus dapat menghambat akses ke sistem dan resource internal kantor, terutama saat bekerja dari luar kantor.</p>

<h2>Langkah Diagnosis</h2>
<ol>
  <li><strong>Verifikasi koneksi internet dasar.</strong> VPN tidak akan bisa terhubung jika internet utama bermasalah. Buka website eksternal dahulu untuk memastikan.</li>
  <li><strong>Cek username dan password.</strong> Password akun mungkin sudah kadaluarsa atau berubah. Coba login ke portal internal lainnya dengan kredensial yang sama.</li>
  <li><strong>Ganti server VPN</strong> jika tersedia beberapa pilihan. Server yang dipilih mungkin sedang dalam pemeliharaan.</li>
  <li><strong>Nonaktifkan sementara firewall/antivirus</strong> untuk menguji apakah mereka yang memblokir koneksi VPN.</li>
</ol>

<h2>VPN Terhubung tapi Tidak Bisa Akses Resource Internal</h2>
<ul>
  <li>Pastikan <em>Split tunneling</em> dikonfigurasi dengan benar. Hubungi IT untuk cek konfigurasi rute.</li>
  <li>Flush DNS setelah terhubung VPN: <code>ipconfig /flushdns</code> di Command Prompt.</li>
</ul>

<h2>Reinstall Klien VPN</h2>
<p>Uninstal klien VPN, restart komputer, unduh versi terbaru dari portal IT internal, dan instal ulang. Backup konfigurasi koneksi sebelum uninstal.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Remote Desktop Tidak Bisa Terhubung',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(11),
                'views'        => 245,
                'content'      => <<<'HTML'
<p>Remote Desktop Protocol (RDP) yang gagal terhubung dengan pesan "cannot connect to the remote computer" bisa disebabkan oleh berbagai faktor di sisi klien maupun server.</p>

<h2>Di Komputer Target (yang Ingin Diremote)</h2>
<ol>
  <li><strong>Aktifkan Remote Desktop:</strong> klik kanan <em>This PC → Properties → Remote settings → Allow remote connections to this computer</em>.</li>
  <li><strong>Pastikan komputer target menyala dan tidak sleep.</strong> Ubah pengaturan power agar komputer tidak sleep saat tidak aktif.</li>
  <li><strong>Tambahkan pengguna yang diizinkan:</strong> di pengaturan Remote Desktop, klik <em>Select Users</em> dan tambahkan akun yang akan digunakan untuk koneksi.</li>
</ol>

<h2>Di Komputer Klien (yang Akan Meremote)</h2>
<ol>
  <li>Pastikan alamat IP atau hostname yang dimasukkan benar. Gunakan <code>ipconfig</code> di komputer target untuk memverifikasi IP.</li>
  <li>Coba ping ke komputer target: <code>ping [IP-target]</code>. Jika gagal, masalah ada di jaringan/firewall.</li>
</ol>

<h2>Masalah Firewall</h2>
<p>Windows Firewall mungkin memblokir port RDP (3389). Di komputer target, buka <em>Windows Defender Firewall → Allow an app → Remote Desktop</em> dan pastikan diizinkan. Untuk jaringan kantor, hubungi IT jika firewall jaringan memblokir koneksi.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Google Chrome Sering Crash atau Not Responding',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(10),
                'views'        => 467,
                'content'      => <<<'HTML'
<p>Chrome yang sering crash, tab yang menutup sendiri, atau pesan "Aw, Snap!" adalah gangguan yang menghambat pekerjaan berbasis web.</p>

<h2>Solusi Bertahap</h2>
<ol>
  <li><strong>Perbarui Chrome.</strong> Klik menu ⋮ → <em>Help → About Google Chrome</em>. Chrome akan otomatis cek dan instal update jika tersedia.</li>
  <li><strong>Nonaktifkan semua ekstensi.</strong> Buka <code>chrome://extensions</code>, nonaktifkan semua, restart Chrome, dan cek apakah masalah hilang. Aktifkan kembali satu per satu untuk menemukan ekstensi bermasalah.</li>
  <li><strong>Bersihkan cache:</strong> <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>Delete</kbd> → hapus Cached images/files dan Cookies untuk rentang waktu <em>All time</em>.</li>
  <li><strong>Nonaktifkan Hardware Acceleration.</strong> Settings → Advanced → System → matikan <em>Use hardware acceleration when available</em>. Restart Chrome.</li>
  <li><strong>Reset Chrome ke default:</strong> Settings → Advanced → Reset and clean up → <em>Restore settings to their original defaults</em>. Ini menghapus ekstensi dan reset pengaturan tapi tidak menghapus history/bookmark.</li>
</ol>

<h2>Chrome Kehabisan Memori</h2>
<p>Chrome menggunakan banyak RAM. Jika RAM komputer terbatas (4 GB), batasi jumlah tab yang terbuka, gunakan fitur <em>Memory Saver</em> (chrome://settings → Performance), atau pertimbangkan browser yang lebih ringan seperti Edge untuk penggunaan sehari-hari.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: File PDF Tidak Bisa Dibuka atau Menampilkan Error',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(9),
                'views'        => 298,
                'content'      => <<<'HTML'
<p>File PDF yang tidak bisa dibuka, menampilkan blank putih, atau error "file is damaged" bisa disebabkan oleh file yang corrupt, reader yang bermasalah, atau file yang terproteksi.</p>

<h2>Langkah Pertama: Coba Reader Lain</h2>
<ul>
  <li>Buka PDF di browser (Chrome/Edge mendukung PDF bawaan). Drag file ke jendela browser.</li>
  <li>Jika berhasil di browser tapi tidak di Acrobat Reader, masalah ada di aplikasi PDF-nya.</li>
</ul>

<h2>Perbaiki Adobe Acrobat Reader</h2>
<ol>
  <li>Buka Acrobat Reader → <em>Help → Repair Installation</em>.</li>
  <li>Jika masih gagal, uninstal dan unduh versi terbaru dari situs resmi Adobe.</li>
</ol>

<h2>File Terunduh Tidak Lengkap</h2>
<p>PDF yang diunduh dari email atau web mungkin tidak selesai diunduh. Cek ukuran file — PDF yang terlalu kecil dibanding harapan biasanya tidak lengkap. Unduh ulang dari sumber aslinya.</p>

<h2>PDF Terproteksi Password</h2>
<p>Beberapa PDF memerlukan password untuk dibuka atau dicetak. Minta password dari pengirim dokumen. Jangan menggunakan tool untuk membobol password PDF — selain bermasalah secara etika, juga dapat melanggar ketentuan keamanan data instansi.</p>

<h2>PDF dari Sistem Internal Tidak Bisa Dibuka</h2>
<p>Jika PDF yang dihasilkan dari sistem internal tidak bisa dibuka, kemungkinan ada masalah pada konfigurasi aplikasi penghasil PDF. Buat tiket dengan menyertakan nama sistem dan pesan error yang muncul.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: File Tidak Bisa Dihapus (Access Denied)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(8),
                'views'        => 267,
                'content'      => <<<'HTML'
<p>Pesan "You need permission to perform this action" atau "File is open in another program" saat mencoba menghapus file adalah masalah yang cukup umum dan umumnya bisa diselesaikan.</p>

<h2>File Sedang Digunakan Aplikasi Lain</h2>
<ol>
  <li>Tutup semua aplikasi yang mungkin membuka file tersebut.</li>
  <li>Restart Windows Explorer: Task Manager → klik kanan <em>Windows Explorer</em> → Restart.</li>
  <li>Gunakan <strong>Process Explorer</strong> (Sysinternals) atau <em>Resource Monitor → CPU → Associated Handles</em> untuk mencari proses yang mengunci file.</li>
</ol>

<h2>Masalah Izin (Permission)</h2>
<ol>
  <li>Klik kanan file → Properties → tab Security.</li>
  <li>Klik <em>Edit</em> dan tambahkan akun Anda dengan izin <em>Full Control</em>.</li>
  <li>Atau klik <em>Advanced → Change owner</em> untuk mengambil kepemilikan file.</li>
</ol>

<h2>File di Direktori Sistem</h2>
<p>File di folder Windows, Program Files, atau System32 memang sengaja diproteksi. Jangan hapus sembarangan — konsultasikan dengan IT terlebih dahulu.</p>

<h2>Gunakan Safe Mode</h2>
<p>Boot ke Safe Mode (minimal service yang berjalan) jika semua cara gagal. File yang biasanya dikunci oleh layanan sistem dapat dihapus saat Safe Mode.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Copy-Paste (Clipboard) Tidak Bekerja di Windows',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(7),
                'views'        => 234,
                'content'      => <<<'HTML'
<p>Clipboard yang tidak berfungsi — paste menghasilkan teks lama, paste tidak bekerja sama sekali, atau konten hilang setelah copy — adalah gangguan kecil yang bisa sangat mengganggu.</p>

<h2>Solusi Cepat</h2>
<ol>
  <li><strong>Restart Windows Explorer.</strong> Task Manager → klik kanan Windows Explorer → Restart. Seringkali ini langsung memperbaiki clipboard yang macet.</li>
  <li><strong>Bersihkan clipboard:</strong> Command Prompt → ketik <code>echo off | clip</code> untuk mengosongkan clipboard.</li>
  <li><strong>Nonaktifkan sementara fitur Clipboard History.</strong> Settings → System → Clipboard → matikan <em>Clipboard history</em>, tunggu sebentar, aktifkan kembali.</li>
</ol>

<h2>Aplikasi yang Mengakses Clipboard</h2>
<p>Beberapa aplikasi (password manager, clipboard manager, remote desktop client) dapat berkonflik dengan clipboard Windows. Tutup aplikasi tersebut satu per satu untuk mengidentifikasi penyebabnya.</p>

<h2>Masalah Clipboard di Remote Desktop</h2>
<p>Clipboard antara komputer lokal dan remote desktop tidak berfungsi? Pastikan opsi <em>Clipboard</em> diaktifkan di pengaturan Remote Desktop Connection sebelum terhubung: tab <em>Local Resources → Local devices and resources</em> → centang Clipboard.</p>

<h2>Restart rdpclip.exe</h2>
<p>Saat menggunakan Remote Desktop, clipboard dikelola oleh proses <code>rdpclip.exe</code>. Buka Task Manager di komputer remote, cari proses ini, End Task, lalu jalankan kembali dari Run (<kbd>Win</kbd>+<kbd>R</kbd>) → <code>rdpclip</code>.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Taskbar Windows Hilang atau Tidak Merespons',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(6),
                'views'        => 312,
                'content'      => <<<'HTML'
<p>Taskbar yang menghilang atau tidak merespons klik memotong akses ke Start Menu, notifikasi, dan tombol aplikasi yang terbuka.</p>

<h2>Solusi Langsung</h2>
<ol>
  <li><strong>Restart Windows Explorer.</strong> Tekan <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>Esc</kbd> untuk membuka Task Manager. Temukan <em>Windows Explorer</em>, klik kanan → Restart. Taskbar biasanya muncul kembali.</li>
  <li><strong>Jika Task Manager tidak bisa dibuka:</strong> tekan <kbd>Ctrl</kbd>+<kbd>Alt</kbd>+<kbd>Del</kbd> → pilih Task Manager dari menu.</li>
</ol>

<h2>Taskbar Tersembunyi Otomatis</h2>
<p>Klik kanan area kosong taskbar → <em>Taskbar settings</em> → pastikan <em>Automatically hide the taskbar</em> dalam kondisi Off. Jika On, taskbar hanya muncul saat kursor mendekati bagian bawah layar.</p>

<h2>Taskbar Berpindah ke Sisi Layar</h2>
<p>Taskbar bisa diseret ke sisi kiri, kanan, atau atas layar secara tidak sengaja. Klik kanan taskbar → <em>Taskbar settings → Taskbar behaviors → Taskbar alignment</em> → pilih <em>Left</em> untuk posisi bawah kiri (Windows 11).</p>

<h2>Perbaikan Lanjutan via PowerShell</h2>
<p>Jika restart Explorer tidak membantu, buka PowerShell sebagai Administrator dan jalankan:</p>
<pre>Get-AppXPackage -AllUsers | Foreach {Add-AppxPackage -DisableDevelopmentMode -Register "$($_.InstallLocation)\AppXManifest.xml"}</pre>
<p>Perintah ini mendaftarkan ulang semua aplikasi UWP termasuk komponen Taskbar Windows.</p>
HTML,
            ],

            /* ── JARINGAN 41-50 ───────────────────────────────────────────── */

            [
                'title'        => 'Troubleshooting: Tidak Bisa Terhubung ke WiFi Kantor',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(5),
                'views'        => 534,
                'content'      => <<<'HTML'
<p>Tidak bisa terhubung ke jaringan WiFi kantor adalah masalah yang sering terjadi dan umumnya diselesaikan dalam beberapa langkah sederhana.</p>

<h2>Pemeriksaan Bertahap</h2>
<ol>
  <li><strong>Pastikan WiFi adapter aktif.</strong> Periksa tombol fisik WiFi pada laptop atau kombinasi <kbd>Fn</kbd>+tombol WiFi. Ikon WiFi di taskbar harus menunjukkan jaringan tersedia, bukan dengan tanda X.</li>
  <li><strong>"Forget" dan sambung ulang.</strong> Klik nama jaringan WiFi → <em>Forget</em>, lalu sambungkan kembali dan masukkan password yang benar.</li>
  <li><strong>Flush DNS dan reset TCP/IP:</strong>
    <pre>netsh winsock reset
netsh int ip reset
ipconfig /release
ipconfig /flushdns
ipconfig /renew</pre>
    Restart komputer setelah menjalankan perintah di atas.</li>
  <li><strong>Update driver WiFi.</strong> Buka Device Manager → <em>Network adapters</em> → klik kanan adapter WiFi → Update driver.</li>
</ol>

<h2>Terhubung tapi Tidak Ada Internet (Limited)</h2>
<p>Kemungkinan masalah pada DHCP server atau IP address conflict. Coba atur IP manual atau hubungi IT untuk pemeriksaan infrastruktur jaringan.</p>

<h2>WiFi Tidak Muncul dalam Daftar Jaringan</h2>
<p>Pastikan router/access point dalam kondisi menyala. Jika jaringan lain muncul tapi jaringan kantor tidak, mungkin access point yang melayani area Anda sedang bermasalah — laporkan ke IT.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Koneksi Internet Terasa Lambat',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(4),
                'views'        => 478,
                'content'      => <<<'HTML'
<p>Internet yang lambat bisa disebabkan oleh banyak faktor — dari kondisi jaringan hingga perangkat pengguna itu sendiri. Diagnosis yang tepat menghindarkan salah langkah.</p>

<h2>Ukur Kecepatan Aktual</h2>
<p>Buka <em>fast.com</em> atau <em>speedtest.net</em> untuk mengukur kecepatan unduh dan unggah. Bandingkan dengan kecepatan yang seharusnya. Jika jauh di bawah normal, masalah ada di infrastruktur jaringan.</p>

<h2>Periksa di Level Perangkat</h2>
<ol>
  <li><strong>Buka Task Manager → tab Performance → Ethernet/WiFi.</strong> Jika utilisasi jaringan mendekati 100%, ada proses yang mengkonsumsi bandwidth besar (update OS, backup cloud, dll.).</li>
  <li><strong>Cek proses yang menggunakan jaringan:</strong> Task Manager → tab App history atau gunakan <em>Resource Monitor → Network</em>.</li>
  <li><strong>Scan malware.</strong> Beberapa malware mengirimkan data secara diam-diam dan menghabiskan bandwidth.</li>
</ol>

<h2>Posisi dan Sinyal WiFi</h2>
<p>Jarak jauh dari access point atau halangan fisik (dinding beton, lemari logam) melemahkan sinyal. Pindah ke posisi lebih dekat dengan access point atau minta IT untuk menambah titik WiFi di area bermasalah.</p>

<h2>Internet Lambat Hanya di Satu Komputer</h2>
<p>Jika komputer lain di ruangan sama berkecepatan normal, masalah ada di perangkat Anda. Coba restart adapter jaringan: Device Manager → klik kanan adapter → Disable → Enable kembali.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: DNS Error — Tidak Bisa Membuka Website Tertentu',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(3),
                'views'        => 356,
                'content'      => <<<'HTML'
<p>Pesan "DNS_PROBE_FINISHED_NXDOMAIN" atau "Server DNS address could not be found" menandakan masalah pada resolusi nama domain — komputer tidak bisa mengubah nama situs menjadi alamat IP.</p>

<h2>Solusi Cepat</h2>
<ol>
  <li><strong>Flush DNS cache:</strong> Command Prompt Administrator → <code>ipconfig /flushdns</code>. Berhasil mengatasi banyak kasus DNS error yang disebabkan cache kadaluarsa.</li>
  <li><strong>Restart router/modem</strong> (jika memiliki akses). Tunggu 30 detik sebelum menyalakan kembali.</li>
  <li><strong>Ganti DNS server secara manual:</strong>
    <ul>
      <li>Buka <em>Network settings → Change adapter options</em>.</li>
      <li>Klik kanan adapter → Properties → <em>Internet Protocol Version 4 (TCP/IPv4)</em>.</li>
      <li>Masukkan DNS: <code>8.8.8.8</code> (Google) atau <code>1.1.1.1</code> (Cloudflare).</li>
    </ul>
  </li>
</ol>

<h2>Hanya Situs Tertentu yang Tidak Bisa Dibuka</h2>
<p>Coba buka situs tersebut dengan browser berbeda dan dari jaringan mobile (hotspot HP). Jika bisa diakses dari hotspot tapi tidak dari jaringan kantor, kemungkinan situs tersebut diblokir oleh firewall kantor. Hubungi IT jika Anda memerlukan akses ke situs tersebut untuk keperluan kerja.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Konflik Alamat IP (IP Address Conflict)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(2),
                'views'        => 234,
                'content'      => <<<'HTML'
<p>Notifikasi "There might be an IP address conflict" atau jaringan yang tiba-tiba putus dan tidak bisa terhubung kembali bisa jadi disebabkan dua perangkat menggunakan IP yang sama.</p>

<h2>Mengapa Ini Terjadi?</h2>
<p>IP address conflict terjadi ketika:</p>
<ul>
  <li>Komputer dikonfigurasi dengan IP statis yang sama dengan perangkat lain.</li>
  <li>DHCP server mengalokasikan IP yang sudah dipakai perangkat dengan IP statis.</li>
  <li>Perangkat baru terhubung ke jaringan dengan IP hardcoded yang sama.</li>
</ul>

<h2>Solusi Cepat</h2>
<ol>
  <li><strong>Gunakan IP otomatis (DHCP).</strong> Buka Network settings → klik kanan adapter → Properties → IPv4 → pilih <em>Obtain an IP address automatically</em>. Ini adalah pengaturan yang disarankan untuk perangkat kantor.</li>
  <li><strong>Lepas dan sambung ulang kabel LAN / WiFi.</strong> DHCP server akan mengalokasikan IP baru.</li>
  <li><strong>Jalankan perintah:</strong>
    <pre>ipconfig /release
ipconfig /renew</pre>
  </li>
</ol>

<h2>Jika IP Statis Diperlukan</h2>
<p>Jika komputer Anda memang perlu IP statis (misalnya untuk kebutuhan tertentu), koordinasikan dengan IT untuk mendapatkan IP yang sudah dipesan dan tidak akan pernah di-assign ke perangkat lain.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Tidak Bisa Mengakses Shared Folder di Jaringan',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(1),
                'views'        => 289,
                'content'      => <<<'HTML'
<p>Folder bersama (shared folder) yang tiba-tiba tidak bisa diakses sementara orang lain masih bisa membukanya sering kali disebabkan masalah izin atau konfigurasi jaringan di komputer Anda.</p>

<h2>Pemeriksaan Awal</h2>
<ol>
  <li><strong>Coba akses via path UNC langsung:</strong> tekan <kbd>Win</kbd>+<kbd>R</kbd>, ketik <code>\\NAMA-SERVER\nama-folder</code> atau <code>\\192.168.x.x\nama-folder</code>.</li>
  <li><strong>Ping server:</strong> <code>ping nama-server</code> atau <code>ping [IP server]</code>. Jika tidak ada reply, masalah di koneksi jaringan, bukan izin folder.</li>
  <li><strong>Masukkan kredensial secara manual.</strong> Dialog login mungkin muncul — masukkan username dan password akun domain atau akun lokal server.</li>
</ol>

<h2>Masalah Network Discovery</h2>
<ol>
  <li>Buka <em>Network and Sharing Center → Change advanced sharing settings</em>.</li>
  <li>Pastikan <em>Network discovery</em> dan <em>File and printer sharing</em> dalam kondisi <strong>On</strong> untuk profile jaringan yang aktif.</li>
</ol>

<h2>Hapus Kredensial Tersimpan yang Kadaluarsa</h2>
<p>Buka <em>Control Panel → Credential Manager → Windows Credentials</em>. Hapus kredensial untuk server yang bermasalah, lalu coba akses ulang dan masukkan password terbaru.</p>

<h2>SMB Version Mismatch</h2>
<p>Windows 11 secara default menonaktifkan SMBv1 yang mungkin digunakan server lama. Hubungi IT untuk penanganan konfigurasi SMB — jangan mengaktifkan SMBv1 sendiri karena memiliki kerentanan keamanan serius.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Profil Pengguna Windows Corrupt (Temp Profile)',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(47),
                'views'        => 345,
                'content'      => <<<'HTML'
<p>Login ke Windows tapi muncul notifikasi "You have been logged on with a temporary profile" dan Desktop tampak kosong adalah tanda profil user corrupt.</p>

<h2>Gejala Profil Corrupt</h2>
<ul>
  <li>Desktop kosong tanpa ikon yang biasa ada.</li>
  <li>Pengaturan dan file tidak tersimpan setelah logout.</li>
  <li>Notifikasi temporary profile di system tray.</li>
</ul>

<h2>Solusi: Perbaiki via Registry</h2>
<ol>
  <li>Login dengan akun Administrator lain (atau minta bantuan IT).</li>
  <li>Buka Registry Editor (<kbd>Win</kbd>+<kbd>R</kbd> → <code>regedit</code>).</li>
  <li>Navigasi ke: <code>HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\ProfileList</code>.</li>
  <li>Cari subfolder yang berakhiran <code>.bak</code> atau memiliki nilai <code>ProfileImagePath</code> yang menunjuk ke profil Anda.</li>
  <li>Ikuti panduan IT untuk mengubah nilai yang diperlukan — proses ini sensitif dan perlu dilakukan dengan hati-hati.</li>
</ol>

<h2>Solusi Alternatif: Buat Profil Baru</h2>
<p>Jika perbaikan registry tidak berhasil, buat akun user baru dan pindahkan file dari profil lama (<code>C:\Users\[nama-lama]</code>) ke profil baru. Hubungi IT untuk bantuan proses ini agar tidak ada data penting yang tertinggal.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Antivirus Memblokir Aplikasi yang Seharusnya Aman',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(48),
                'views'        => 267,
                'content'      => <<<'HTML'
<p>Antivirus terkadang mendeteksi aplikasi sah sebagai ancaman (false positive), terutama aplikasi buatan lokal atau tools IT yang jarang dikenal oleh database antivirus.</p>

<h2>Langkah Verifikasi Sebelum Mengizinkan</h2>
<ol>
  <li><strong>Periksa sumber aplikasi.</strong> Apakah diunduh dari sumber resmi? Jika dari email atau link tidak jelas, jangan langsung percaya.</li>
  <li><strong>Scan file di VirusTotal.</strong> Buka <em>virustotal.com</em>, upload file tersebut. Jika hanya 1-2 antivirus yang mendeteksinya sebagai ancaman dari 70+ engine, kemungkinan besar false positive.</li>
</ol>

<h2>Cara Menambahkan Pengecualian (Exclusion)</h2>
<p>Untuk Windows Defender:</p>
<ol>
  <li>Buka <em>Windows Security → Virus & threat protection → Manage settings</em>.</li>
  <li>Gulir ke <em>Exclusions → Add or remove exclusions → Add an exclusion</em>.</li>
  <li>Pilih file atau folder yang ingin dikecualikan dari pemindaian.</li>
</ol>

<p>Untuk antivirus pihak ketiga (Bitdefender, Kaspersky, dll.), prosedur serupa ada di menu Settings atau Exceptions.</p>

<h2>Laporkan False Positive</h2>
<p>Laporkan false positive ke vendor antivirus melalui portal resmi mereka. Database antivirus akan diperbarui sehingga aplikasi tersebut tidak lagi terdeteksi di komputer lain.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: File Hilang Setelah Restart Komputer',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(49),
                'views'        => 423,
                'content'      => <<<'HTML'
<p>File yang tersimpan dan tiba-tiba menghilang setelah restart bisa sangat membingungkan dan mengkhawatirkan. Namun file tersebut sering kali tidak benar-benar hilang.</p>

<h2>Cari File yang "Hilang"</h2>
<ol>
  <li><strong>Periksa Recycle Bin.</strong> File mungkin terhapus secara tidak sengaja.</li>
  <li><strong>Gunakan Search Windows.</strong> Tekan <kbd>Win</kbd>+<kbd>S</kbd>, ketik nama file (jika masih ingat). Atur filter pencarian ke lokasi <em>This PC</em>.</li>
  <li><strong>Periksa folder Downloads, Documents, dan Desktop</strong> karena browser dan aplikasi sering menyimpan file di lokasi default yang berbeda.</li>
  <li><strong>Cek di OneDrive atau Google Drive</strong> jika sync aktif. File mungkin dipindah oleh proses sync.</li>
</ol>

<h2>Previous Versions</h2>
<p>Klik kanan folder tempat file terakhir disimpan → <em>Properties → Previous Versions</em>. Jika System Restore aktif, mungkin ada snapshot folder sebelum file hilang.</p>

<h2>Profil Sementara (Temp Profile)</h2>
<p>Jika file hilang bersamaan dengan munculnya notifikasi "logged in with temporary profile", lihat artikel tentang Profil Pengguna Corrupt untuk panduan pemulihan.</p>

<h2>Simpan ke Lokasi Aman</h2>
<p>Hindari menyimpan file penting hanya di Desktop atau folder Temp. Gunakan folder Documents yang tersinkron ke OneDrive sehingga file selalu ada cadangannya di cloud.</p>
HTML,
            ],

            [
                'title'        => 'Troubleshooting: Koneksi LAN (Kabel) Tidak Terdeteksi',
                'author_name'  => 'Tim IT Support',
                'category'     => 'troubleshooting',
                'category_id'  => $ts,
                'status'       => 'published',
                'published_at' => now()->subDays(50),
                'views'        => 312,
                'content'      => <<<'HTML'
<p>Koneksi LAN melalui kabel ethernet lebih stabil dari WiFi, namun ketika tidak terdeteksi, diagnosisnya memerlukan pemeriksaan dari beberapa titik.</p>

<h2>Pemeriksaan Fisik</h2>
<ol>
  <li><strong>Periksa lampu indikator di port LAN laptop/komputer.</strong> Seharusnya ada lampu hijau (terhubung) dan oranye berkedip (ada aktivitas). Tidak ada lampu = tidak ada koneksi fisik.</li>
  <li><strong>Periksa konektor RJ-45.</strong> Dengarkan bunyi "klik" saat memasukkan konektor. Klip pengunci yang patah menyebabkan kabel mudah longgar.</li>
  <li><strong>Coba kabel LAN berbeda.</strong> Kabel yang rusak secara internal tidak selalu terlihat dari luar.</li>
  <li><strong>Coba port switch yang berbeda.</strong> Port switch yang mati dapat menyebabkan tidak ada koneksi.</li>
</ol>

<h2>Pemeriksaan Software</h2>
<ol>
  <li>Buka Device Manager → <em>Network adapters</em>. Pastikan adapter LAN tidak di-disable dan tidak ada tanda error.</li>
  <li>Klik kanan adapter LAN → <em>Enable device</em> jika dalam kondisi disabled.</li>
  <li>Update driver ethernet adapter dari situs produsen (Realtek, Intel, dll.).</li>
</ol>

<h2>Tidak Ada Port LAN di Laptop Modern</h2>
<p>Laptop ultrabook sering tidak memiliki port LAN. Gunakan adaptor USB-to-Ethernet atau docking station. Hubungi IT untuk peminjaman jika diperlukan untuk pekerjaan yang membutuhkan koneksi stabil.</p>
HTML,
            ],

        ];
    }
}
