<?php

class KontrolKost
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
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    // Mengambil detail kost dan daftar kamar
    public function ambilDetailKost($idKost)
    {
        $query = "SELECT *
                  FROM kost
                  WHERE idKost = :idKost
                  AND status = 'Dipublikasikan'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(['idKost' => $idKost]);

        $dataKost = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dataKost) {
            return false;
        }

        $queryKamar = "SELECT *
                       FROM kamar
                       WHERE idKost = :idKost
                       ORDER BY nomorKamar ASC";

        $stmtKamar = $this->conn->prepare($queryKamar);
        $stmtKamar->execute(['idKost' => $idKost]);

        $daftarKamar = $stmtKamar->fetchAll(PDO::FETCH_ASSOC);

        return [
            'kost' => $dataKost,
            'kamar' => $daftarKamar
        ];
    }

    // Mengambil detail kamar beserta informasi kost
    public function ambilDetailKamar($idKamar)
    {
        $query = "SELECT
                    kamar.*,
                    kost.namaKost,
                    kost.alamat,
                    kost.lokasi,
                    kost.tipeKost,
                    kost.kontakPemilik,
                    kost.fotoUtama,
                    kost.status AS statusKost
                  FROM kamar
                  INNER JOIN kost
                    ON kamar.idKost = kost.idKost
                  WHERE kamar.idKamar = :idKamar
                  AND kost.status = 'Dipublikasikan'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(['idKamar' => $idKamar]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Menampilkan halaman detail kost
    public function tampilDetailKost()
    {
        $idKost = $_GET['idKost'] ?? null;

        if (!$idKost || !ctype_digit((string) $idKost)) {
            die("ID kost tidak valid.");
        }

        $data = $this->ambilDetailKost($idKost);

        if (!$data) {
            die("Data kost tidak tersedia.");
        }

        $kost = $data['kost'];
        $daftarKamar = $data['kamar'];

        require __DIR__ . '/../view/FormDetailKost.php';
    }

    // Menampilkan halaman detail kamar
    public function tampilDetailKamar()
    {
        $idKamar = $_GET['idKamar'] ?? null;

        if (!$idKamar || !ctype_digit((string) $idKamar)) {
            die("ID kamar tidak valid.");
        }

        $kamar = $this->ambilDetailKamar($idKamar);

        if (!$kamar) {
            die("Data kamar tidak tersedia.");
        }

        require __DIR__ . '/../view/FormDetailKamar.php';
    }
}


// Menjalankan controller berdasarkan aksi
$kontrol = new KontrolKost();

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'detailKost') {
    $kontrol->tampilDetailKost();
} elseif ($aksi === 'detailKamar') {
    $kontrol->tampilDetailKamar();
} else {
    echo "Halaman tidak ditemukan.";
}