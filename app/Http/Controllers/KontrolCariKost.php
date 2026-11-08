<?php
/**
 * KontrolCariKost.php  (CD-03 / PC-03 / UC-03 Mencari Kost)
 * Satu file berisi: koneksi DB, entity Kost, dan control KontrolCariKost.
 * Buka halaman lewat file ini: http://localhost:8000/controllers/KontrolCariKost.php
 */
session_start();

/* ====================== KONFIGURASI ====================== */
const DB_HOST = '127.0.0.1';
const DB_NAME = 'kost_io';
const DB_USER = 'root';
const DB_PASS = '';
const DEV_MODE = true;   // true = pakai akun dummy kalau belum login

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $pdo;
}

function e($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function rupiah($n): string { return 'Rp' . number_format((float) $n, 0, ',', '.'); }
function inisial(string $nama): string
{
    $h = '';
    foreach (array_slice(preg_split('/\s+/', trim($nama)), 0, 2) as $k) {
        $h .= mb_strtoupper(mb_substr($k, 0, 1));
    }
    return $h;
}

/* ====================== <<entity>> Kost ====================== */
class Kost
{
    const STATUS_PUBLIK = 'Dipublikasikan';
    private const KOLOM = "idKost, namaKost, alamat, lokasi, tipeKost,
                           fasilitasUmum AS fasilitas, fotoUtama, hargaMulai, status";

    private array $d;
    public function __construct(array $row) { $this->d = $row; }

    public function getIdKost(): int       { return (int) $this->d['idKost']; }
    public function getNamaKost(): string  { return $this->d['namaKost'] ?? ''; }
    public function getAlamat(): string    { return $this->d['alamat'] ?? ''; }
    public function getLokasi(): string    { return $this->d['lokasi'] ?? ''; }
    public function getTipeKost(): string  { return $this->d['tipeKost'] ?? ''; }
    public function getFotoUtama(): ?string { return $this->d['fotoUtama'] ?? null; }
    public function getHargaMulai(): float { return (float) ($this->d['hargaMulai'] ?? 0); }
    public function getStatus(): string    { return $this->d['status'] ?? ''; }
    public function getDaftarFasilitas(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->d['fasilitas'] ?? ''))));
    }

    /** getSemuaKost(): List<Kost> */
    public static function getSemuaKost(): array
    {
        $stmt = getDB()->prepare("SELECT " . self::KOLOM . " FROM Kost WHERE status = :s ORDER BY namaKost");
        $stmt->execute([':s' => self::STATUS_PUBLIK]);
        return array_map(fn($r) => new Kost($r), $stmt->fetchAll());
    }

    /** getKost(kriteria): List<Kost> */
    public static function getKost(array $kr): array
    {
        $where  = ['status = :status'];
        $params = [':status' => self::STATUS_PUBLIK];

        if (($kr['kataKunci'] ?? '') !== '') {
            $where[] = '(namaKost LIKE :k1 OR lokasi LIKE :k2 OR alamat LIKE :k3)';
            $params[':k1'] = $params[':k2'] = $params[':k3'] = '%' . $kr['kataKunci'] . '%';
        }
        if (($kr['rentangHarga'] ?? '') !== '') {
            [$min, $max] = array_pad(explode('-', $kr['rentangHarga'], 2), 2, '');
            if ($min !== '' && ctype_digit($min)) { $where[] = 'hargaMulai >= :hmin'; $params[':hmin'] = (int) $min; }
            if ($max !== '' && ctype_digit($max)) { $where[] = 'hargaMulai <= :hmax'; $params[':hmax'] = (int) $max; }
        }
        if (($kr['tipeKost'] ?? '') !== '') {
            $where[] = 'tipeKost = :tipe';
            $params[':tipe'] = $kr['tipeKost'];
        }
        if (($kr['fasilitas'] ?? '') !== '') {
            $where[] = 'fasilitasUmum LIKE :fas';
            $params[':fas'] = '%' . $kr['fasilitas'] . '%';
        }

        $stmt = getDB()->prepare("SELECT " . self::KOLOM . " FROM Kost WHERE " . implode(' AND ', $where) . " ORDER BY hargaMulai");
        $stmt->execute($params);
        return array_map(fn($r) => new Kost($r), $stmt->fetchAll());
    }
}

/* ====================== <<control>> KontrolCariKost ====================== */
class KontrolCariKost
{
    public const OPSI_HARGA = [
        ''                => 'Semua harga',
        '0-500000'        => 'Di bawah Rp500.000',
        '500000-1500000'  => 'Rp500.000 – Rp1.500.000',
        '1500000-'        => 'Di atas Rp1.500.000',
    ];
    public const OPSI_TIPE      = ['Putra', 'Putri', 'Campur'];
    public const OPSI_FASILITAS = ['Wi-Fi', 'AC', 'Kamar mandi dalam', 'Lemari', 'Meja belajar', 'Parkir', 'Dapur bersama'];

    private array $kriteria = ['kataKunci' => '', 'rentangHarga' => '', 'tipeKost' => '', 'fasilitas' => ''];
    private array $daftarKost = [];

    /** membukaMenuCari(): halaman dibuka / tombol Cari diklik */
    public function membukaMenuCari(): void
    {
        $this->isiKriteria($_GET);
        $this->cariKost($this->kriteria);
        $this->tampilFormCari();
    }

    public function isiKriteria(array $in): void
    {
        $this->kriteria['kataKunci'] = trim((string) ($in['q'] ?? ''));

        $harga = $in['rentangHarga'] ?? '';
        $this->kriteria['rentangHarga'] = array_key_exists($harga, self::OPSI_HARGA) ? $harga : '';

        $tipe = $in['tipeKost'] ?? '';
        $this->kriteria['tipeKost'] = in_array($tipe, self::OPSI_TIPE, true) ? $tipe : '';

        $fasilitas = $in['fasilitas'] ?? '';
        $this->kriteria['fasilitas'] = in_array($fasilitas, self::OPSI_FASILITAS, true) ? $fasilitas : '';
    }

    public function cariKost(array $kriteria): void
    {
        $semuaKosong = count(array_filter($kriteria, fn($v) => $v !== '')) === 0;
        try {
            $hasil = $semuaKosong ? Kost::getSemuaKost() : Kost::getKost($kriteria);
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $hasil = [];
        }
        $hasil ? $this->kirimHasil($hasil) : $this->kirimHasilKosong();
    }

    public function kirimHasil(array $daftarKost): void { $this->daftarKost = $daftarKost; }
    public function kirimHasilKosong(): void            { $this->daftarKost = []; }
    public function pesanTidakDitemukan(): string       { return 'Kost tidak ditemukan'; }
    public function ubahKriteria(): string              { return $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(['q' => $this->kriteria['kataKunci']] + $this->kriteria) . '#q'; }

    public function getKriteria(): array   { return $this->kriteria; }
    public function getDaftarKost(): array { return $this->daftarKost; }

    public function tampilFormCari(): void
    {
        $kontrol = $this;
        require __DIR__ . '/../view/FormCariKost.php';
    }
}

/* ====================== ALUR HALAMAN ====================== */
if (isset($_GET['keluar'])) {
    session_destroy();
    header('Location: ' . $_SERVER['SCRIPT_NAME']);
    exit;
}
if (empty($_SESSION['akun'])) {
    if (!DEV_MODE) { header('Location: login.php'); exit; }
    $_SESSION['akun'] = ['idAkun' => 1, 'namaLengkap' => 'Rifa Aqilla', 'jenisAkun' => 'Calon Penghuni'];
}

(new KontrolCariKost())->membukaMenuCari();
