<?php

namespace App\Exports;

use App\Models\Cita;
use App\Models\Pagos;
use Maatwebsite\Excel\Concerns\FromCollection;

class CitasExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $products = Pagos::all();
        return view('excel.cumplimientos', ['pagosRatificacion' => $products]);
    }
}
