<?php
declare(strict_types=1);

class KeluhanController
{
    public function __construct(private PDO $db)
    {
    }

    public function mintaFormPengajuan(string $deskripsi, string $pesan, string $token): void
    {
        require __DIR__ . '/../view/pengajuan-keluhan.php';
    }

    public function validasiLaporan(string $deskripsi, int $idPenghuni, int $idKost): void
    {
        if ($idPenghuni < 1 || $idKost < 1) {
            throw new InvalidArgumentException('Data penghuni atau kost belum tersedia.');
        }
        if (trim($deskripsi) === '' || mb_strlen($deskripsi, 'UTF-8') > 2000) {
            throw new InvalidArgumentException('Isi deskripsi keluhan, maksimal 2.000 karakter.');
        }
    }

    public function ajukanKeluhan(string $deskripsi, int $idPenghuni, int $idKost, ?array $foto): bool
    {
        $this->validasiLaporan($deskripsi, $idPenghuni, $idKost);
        $pathFoto = $this->simpanFoto($foto);

        try {
            $query = $this->db->prepare(
                'INSERT INTO Keluhan (idKost, idPenghuni, deskripsi, foto, status, tanggalPengajuan)
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            return $query->execute([$idKost, $idPenghuni, trim($deskripsi), $pathFoto, 'Diajukan']);
        } catch (PDOException $error) {
            if ($pathFoto !== null) {
                unlink(__DIR__ . '/../' . $pathFoto);
            }
            throw $error;
        }
    }

    private function simpanFoto(?array $foto): ?string
    {
        if ($foto === null || $foto['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($foto['error'] !== UPLOAD_ERR_OK || $foto['size'] > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Foto gagal diunggah. Batas ukuran 2 MB.');
        }

        $tipe = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);
        $format = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        if (!isset($format[$tipe])) {
            throw new InvalidArgumentException('Foto harus berformat JPG atau PNG.');
        }

        $folder = __DIR__ . '/../uploads/keluhan';
        if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
            throw new RuntimeException('Folder unggahan tidak tersedia.');
        }

        $path = 'uploads/keluhan/' . bin2hex(random_bytes(16)) . '.' . $format[$tipe];
        if (!move_uploaded_file($foto['tmp_name'], __DIR__ . '/../' . $path)) {
            throw new RuntimeException('Foto tidak dapat disimpan.');
        }

        return $path;
    }
}

session_start();
$idPenghuni = (int) ($_SESSION['idPenghuni'] ?? 0);
$idKost = (int) ($_SESSION['idKost'] ?? 0);
if ($idPenghuni < 1 || $idKost < 1) {
    http_response_code(403);
    exit('Silakan masuk sebagai penghuni dengan data kost aktif.');
}

$token = $_SESSION['tokenKeluhan'] ??= bin2hex(random_bytes(32));
$deskripsi = '';
$pesan = $_SESSION['pesanKeluhan'] ?? '';
unset($_SESSION['pesanKeluhan']);

try {
    $db = new PDO(
        'mysql:host=127.0.0.1;dbname=kost_io;charset=utf8mb4',
        getenv('KOST_DB_USER') ?: 'root',
        getenv('KOST_DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $controller = new KeluhanController($db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tokenForm = $_POST['token'] ?? '';
        if (!is_string($tokenForm) || !hash_equals($token, $tokenForm)) {
            http_response_code(403);
            exit('Formulir tidak valid. Buka kembali halaman pengajuan.');
        }

        $deskripsi = is_string($_POST['deskripsi'] ?? null) ? $_POST['deskripsi'] : '';
        if ($controller->ajukanKeluhan($deskripsi, $idPenghuni, $idKost, $_FILES['foto'] ?? null)) {
            $_SESSION['pesanKeluhan'] = 'Laporan berhasil dikirim.';
            header('Location: KeluhanController.php', true, 303);
            exit;
        }
    }

    $controller->mintaFormPengajuan($deskripsi, $pesan, $token);
} catch (InvalidArgumentException $error) {
    http_response_code(422);
    $controller->mintaFormPengajuan($deskripsi, $error->getMessage(), $token);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    $pesan = 'Laporan belum dapat diproses. Silakan coba lagi.';
    require __DIR__ . '/../view/pengajuan-keluhan.php';
}
