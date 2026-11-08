<?php
session_start();

class KontrolPengajuanSewa
{
    private $conn;

    public function __construct()
    {
        $host = '127.0.0.1';
        $dbname = 'kost_io';
        $username = 'root';
        $password = '';

        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    // Mengambil informasi kamar beserta informasi kost.
    private function ambilDataKamar($idKamar)
    {
        $sql = "SELECT
                    kamar.*,
                    kost.namaKost,
                    kost.alamat,
                    kost.lokasi,
                    kost.tipeKost,
                    kost.fotoUtama,
                    kost.fotoKost
                FROM kamar
                INNER JOIN kost
                    ON kamar.idKost = kost.idKost
                WHERE kamar.idKamar = :idKamar
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':idKamar' => $idKamar]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Menampilkan halaman formulir pengajuan sewa.
    public function tampilFormAjukanSewa()
    {
        $idKamar = $_GET['idKamar'] ?? '';

        if (!ctype_digit((string) $idKamar)) {
            die('ID kamar tidak valid.');
        }

        $kamar = $this->ambilDataKamar($idKamar);

        if (!$kamar) {
            die('Data kamar tidak ditemukan.');
        }

        if (
            strtolower(trim($kamar['statusKamar'] ?? '')) !== 'tersedia'
        ) {
            die('Maaf, kamar ini sedang tidak tersedia.');
        }

        $pesanError = $_GET['error'] ?? '';

        require_once __DIR__ . '/../view/FormAjukanSewa.php';
    }

    // Memproses formulir pengajuan sewa.
    public function simpanPengajuan()
    {
        $idKamar = $_POST['idKamar'] ?? '';

        if (!ctype_digit((string) $idKamar)) {
            die('ID kamar tidak valid.');
        }

        // Mengambil ID akun dari session login.
        $idCalonPenghuni = $_SESSION['idAkun'] ?? null;

        if (!$idCalonPenghuni) {
            die('Silakan login terlebih dahulu sebelum mengajukan sewa.');
        }

        $kamar = $this->ambilDataKamar($idKamar);

        if (!$kamar) {
            die('Data kamar tidak ditemukan.');
        }

        if (
            strtolower(trim($kamar['statusKamar'] ?? '')) !== 'tersedia'
        ) {
            die('Maaf, kamar ini sudah tidak tersedia.');
        }

        $tanggalMulai = $_POST['tanggalMulaiSewa'] ?? '';
        $durasiSewa = $_POST['durasiSewa'] ?? '';
        $jumlahPenghuni = $_POST['jumlahPenghuni'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');

        // Validasi input.
        if (
            empty($tanggalMulai) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalMulai) ||
            $tanggalMulai < date('Y-m-d')
        ) {
            header(
                'Location: KontrolPengajuanSewa.php?aksi=form&idKamar=' .
                urlencode($idKamar) . '&error=tanggal'
            );
            exit;
        }

        if (
            !ctype_digit((string) $durasiSewa) ||
            (int) $durasiSewa < 1
        ) {
            die('Durasi sewa tidak valid.');
        }

        if (
            !ctype_digit((string) $jumlahPenghuni) ||
            (int) $jumlahPenghuni < 1
        ) {
            die('Jumlah penghuni tidak valid.');
        }

        // Validasi file KTP.
        if (
            !isset($_FILES['berkasKTP']) ||
            $_FILES['berkasKTP']['error'] !== UPLOAD_ERR_OK
        ) {
            die('Silakan unggah dokumen KTP terlebih dahulu.');
        }

        $file = $_FILES['berkasKTP'];

        if ($file['size'] > 2 * 1024 * 1024) {
            die('Ukuran file KTP maksimal 2 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $formatDiizinkan = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf'
        ];

        if (!isset($formatDiizinkan[$mime])) {
            die('Format dokumen harus JPG, PNG, atau PDF.');
        }

        // Menyiapkan folder penyimpanan.
        $folderUpload = __DIR__ . '/../uploads/ktp/';

        if (!is_dir($folderUpload)) {
            mkdir($folderUpload, 0775, true);
        }

        // Nama file dibuat unik agar tidak saling menimpa.
        $namaFile = 'ktp_' . uniqid() . '.' . $formatDiizinkan[$mime];

        $tujuanFile = $folderUpload . $namaFile;
        $pathDatabase = 'uploads/ktp/' . $namaFile;

        if (!move_uploaded_file($file['tmp_name'], $tujuanFile)) {
            die('Dokumen KTP gagal diunggah.');
        }

        try {
            $sql = "INSERT INTO pengajuansewa (
                        idCalonPenghuni,
                        idKamar,
                        idKost,
                        tanggalMulaiSewa,
                        durasiSewa,
                        jumlahPenghuni,
                        catatan,
                        berkasKTP,
                        status,
                        tanggalPengajuan
                    ) VALUES (
                        :idCalonPenghuni,
                        :idKamar,
                        :idKost,
                        :tanggalMulaiSewa,
                        :durasiSewa,
                        :jumlahPenghuni,
                        :catatan,
                        :berkasKTP,
                        :status,
                        CURDATE()
                    )";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([
                ':idCalonPenghuni' => $idCalonPenghuni,
                ':idKamar' => $kamar['idKamar'],
                ':idKost' => $kamar['idKost'],
                ':tanggalMulaiSewa' => $tanggalMulai,
                ':durasiSewa' => $durasiSewa,
                ':jumlahPenghuni' => $jumlahPenghuni,
                ':catatan' => $catatan,
                ':berkasKTP' => $pathDatabase,
                ':status' => 'Menunggu'
            ]);

            header(
                'Location: KontrolPengajuanSewa.php?aksi=berhasil'
            );
            exit;

        } catch (PDOException $e) {
            // Hapus file apabila penyimpanan data gagal.
            if (file_exists($tujuanFile)) {
                unlink($tujuanFile);
            }

            die('Pengajuan gagal disimpan: ' . $e->getMessage());
        }
    }

    // Halaman konfirmasi pengajuan berhasil.
    public function tampilBerhasil()
    {
        require_once __DIR__ . '/../view/FormPengajuanBerhasil.php';
    }
}

// Menjalankan aksi berdasarkan parameter URL.
$controller = new KontrolPengajuanSewa();

$aksi = $_GET['aksi'] ?? 'form';

switch ($aksi) {
    case 'form':
        $controller->tampilFormAjukanSewa();
        break;

    case 'simpan':
        $controller->simpanPengajuan();
        break;

    case 'berhasil':
        $controller->tampilBerhasil();
        break;

    default:
        die('Aksi tidak ditemukan.');
}