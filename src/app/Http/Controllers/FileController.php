<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
        
    public function show($shortName)
    {
        $pdf = DocumentFile::where('short_name', $shortName)->firstOrFail();
                
        $hasAccess = UserPermission::where('user_id', auth()->id())->where('folder_id', $pdf->folder_id)->exists();
        
        if ($hasAccess) {
            return Storage::download('pdf/' . $shortName);
        }

        return back();
    }

    // ...
}