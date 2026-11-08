<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request): View
    {
        $idAkun = $request->session()->get('idAkun');

        abort_unless(
            $idAkun,
            403,
            'Silakan login sebagai pemilik kost.'
        );

        $pemilik = DB::table('akun')
            ->where('idAkun', $idAkun)
            ->where('jenisAkun', 'Pemilik Kost')
            ->first();

        abort_unless(
            $pemilik,
            403,
            'Akses khusus pemilik kost.'
        );

        // Hanya mengambil kost milik akun yang login.
        $daftarKost = DB::table('kost')
            ->where('idPemilik', $pemilik->idAkun)
            ->orderBy('namaKost')
            ->get(['idKost', 'namaKost']);

        $request->validate([
            'kost' => ['nullable', 'integer'],
            'cari' => ['nullable', 'string', 'max:120'],
        ]);

        $idKost = $request->filled('kost')
            ? (int) $request->query('kost')
            : (int) ($daftarKost->first()->idKost ?? 0);

        $kostAktif = $daftarKost->firstWhere(
            'idKost',
            $idKost
        );

        abort_if(
            $idKost !== 0 && !$kostAktif,
            404,
            'Kost tidak ditemukan.'
        );

        $cari = trim((string) $request->query('cari', ''));

        $namaKostHeader = $kostAktif->namaKost ?? 'Kost.io';

        $totalPendapatan = 0;
        $jumlahTransaksi = 0;
        $transaksi = null;
        $grafik = collect();

        if ($kostAktif) {

            // Berdasarkan tabel datakeuangan dan pembayaran.
            // Pembayaran harus diterima serta tagihan lunas.
            $base = DB::table('datakeuangan as dk')
                ->join(
                    'pembayaran as pb',
                    'dk.idPembayaran',
                    '=',
                    'pb.idPembayaran'
                )
                ->join(
                    'tagihansewa as t',
                    'pb.idTagihan',
                    '=',
                    't.idTagihan'
                )
                ->join(
                    'penghuni as p',
                    'pb.idPenghuni',
                    '=',
                    'p.idPenghuni'
                )
                ->join(
                    'kamar as km',
                    't.idKamar',
                    '=',
                    'km.idKamar'
                )
                ->where('dk.idKost', $kostAktif->idKost)
                ->whereIn(
                    'pb.statusPembayaran',
                    ['Diterima', 'Lunas']
                )
                ->where('t.status', 'Lunas');

            // Ringkasan pendapatan.
            $totalPendapatan = (float) (clone $base)
                ->sum('dk.nominal');

            $jumlahTransaksi = (clone $base)
                ->count('dk.idTransaksi');

            // Pendapatan per tanggal untuk grafik.
            $grafik = (clone $base)
                ->select('dk.tanggalTransaksi')
                ->selectRaw(
                    'SUM(dk.nominal) as total'
                )
                ->groupBy('dk.tanggalTransaksi')
                ->orderBy('dk.tanggalTransaksi')
                ->get();

            // Data tabel transaksi.
            $query = (clone $base)
                ->select(
                    'dk.idTransaksi',
                    'dk.tanggalTransaksi',
                    'dk.nominal',
                    'p.namaLengkap',
                    'km.nomorKamar',
                    't.periode'
                );

            // Pencarian transaksi.
            if ($cari !== '') {
                $query->where(function ($q) use ($cari) {
                    $q->where(
                        'p.namaLengkap',
                        'like',
                        "%{$cari}%"
                    )
                    ->orWhere(
                        'km.nomorKamar',
                        'like',
                        "%{$cari}%"
                    )
                    ->orWhere(
                        't.periode',
                        'like',
                        "%{$cari}%"
                    )
                    ->orWhere(
                        'dk.tanggalTransaksi',
                        'like',
                        "%{$cari}%"
                    );
                });
            }

            $transaksi = $query
                ->orderByDesc('dk.tanggalTransaksi')
                ->orderByDesc('dk.idTransaksi')
                ->paginate(10)
                ->withQueryString();
        }

        $nilaiMaksimum = max(
            1,
            (float) $grafik->max('total')
        );

        return view('keuangan.index', compact(
            'pemilik',
            'daftarKost',
            'kostAktif',
            'namaKostHeader',
            'idKost',
            'cari',
            'totalPendapatan',
            'jumlahTransaksi',
            'transaksi',
            'grafik',
            'nilaiMaksimum'
        ));
    }
}
