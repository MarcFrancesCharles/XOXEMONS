<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class XuxemonController extends Controller {
    public function index() {
        // Retorna tots els xuxemons de l'usuari
        $xuxemons = Auth::user()->xuxemons;
        return response()->json($xuxemons);
    }
}