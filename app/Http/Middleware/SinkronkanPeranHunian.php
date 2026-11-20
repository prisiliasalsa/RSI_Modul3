<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SinkronkanPeranHunian
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $idAkun = (int) $session->get('idAkun');

        if ($idAkun > 0 && in_array($session->get('jenisAkun'), ['Calon Penghuni', 'Penghuni'], true)) {
            $idPenghuni = self::idPenghuniAktif($idAkun);
            $session->put('jenisAkun', $idPenghuni === null ? 'Calon Penghuni' : 'Penghuni');

            if ($idPenghuni === null) {
                $session->forget('idPenghuni');
            } else {
                $session->put('idPenghuni', $idPenghuni);
            }
        }

        return $next($request);
    }

    public static function idPenghuniAktif(int $idAkun): ?int
    {
        $idPenghuni = DB::table('penghuni')
            ->where('idAkun', $idAkun)
            ->where('status', 'Aktif')
            ->orderByDesc('idPenghuni')
            ->value('idPenghuni');

        return $idPenghuni === null ? null : (int) $idPenghuni;
    }
}
