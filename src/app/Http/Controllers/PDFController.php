<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


use App\Models\SeerPerGeneral;
use App\Models\SeerCitados;
use App\Models\SeerMotivo;
use App\Models\SolicitudMotivo;
use App\Models\SolicitudRama;
use App\Models\SolicitudEconomica;
use App\Models\SeerMotivoSolicitud;
use App\Models\SeerSolicitante;


class PDFController extends Controller
{
    public function pdfEstadistica(){
        //Generar un PDF desde HTML
        
        //$pdf = app('dompdf.wrapper');
        //$pdf->loadHTML('<h1>Styde.net</h1>');
        //return $pdf->download('mi-archivo.pdf');
        
        //Genera PDF desde la vista
        $data = [
            'titulo' => 'Styde.net'
        ];
    
        $pdf = \PDF::loadView('PDF/vista-prueba', $data);
    
        return $pdf->loadView('archivo.pdf');


        //$pdf = \PDF::loadView('vista-pdf', $data);
        //return $pdf->download('mi-archivo.pdf');
        //return PDF::loadView('vista-pdf', $data)->stream('archivo.pdf');
    }
    public function pdfCitatorio($id){
        $solicitud = SeerPerGeneral::findOrFail($id);
        $solicitantes = SeerSolicitante::where('id_solicitud', $id)->get();
        $citados = SeerCitados::where('id_solicitud', $id)->get();
        $motivoIds = SeerMotivo::where('id_solicitud', $id)->pluck('id_motivo');
        $motivos = SolicitudMotivo::whereIn('id', $motivoIds)->get();

        $SolicitudPDFs = [];

        foreach ($solicitantes as $solicitante) {
            foreach ($citados as $citado) {
                $pdf = \PDF::loadView('PDF/citatorio', compact(
                    'solicitud',
                    'solicitante',
                    'citado',
                    'motivos',
                ))->setPaper('letter');
                $nombreArchivo = 'citatorio_' . $citado->nombre . '_' . $citado->primer_apellido . '_' . $citado->segundo_apellido . '.pdf';
                Storage::put("documentosCitatorios/{$nombreArchivo}", $pdf->output());
                $SolicitudPDFs[] = $nombreArchivo; 
            }
        } 
        return view('solicitudes.descargaCitatorios', compact('SolicitudPDFs'));    
    }  
    public function pdfRatificacion(){
        return view('PDF.ratificacion');    
    }  

}
