<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller {
    public function index() {
        // Retorna els objectes de l'usuari actual amb la quantitat
        $items = Auth::user()->items;
        return response()->json($items);
    }
}