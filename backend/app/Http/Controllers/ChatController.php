<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Funció privada per comprovar si són amics
    private function areFriends($userId, $friendId) {
        return Friendship::where('status', 'accepted')
            ->where(function($q) use ($userId, $friendId) {
                $q->where('user_id', $userId)->where('friend_id', $friendId);
            })->orWhere(function($q) use ($userId, $friendId) {
                $q->where('user_id', $friendId)->where('friend_id', $userId);
            })->exists();
    }

    // 1. Obtenir l'historial de missatges amb un amic
    public function getMessages($friendId) {
        $myId = Auth::id();

        // Validació de seguretat: Només pots veure missatges si sou amics
        if (!$this->areFriends($myId, $friendId)) {
            return response()->json(['message' => 'No tens permís. No sou amics.'], 403);
        }

        // Busquem els missatges on jo soc l'emissor i ell el receptor, o viceversa, ordenats pel més antic primer
        $messages = Message::where(function($q) use ($myId, $friendId) {
                $q->where('sender_id', $myId)->where('receiver_id', $friendId);
            })->orWhere(function($q) use ($myId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    // 2. Enviar un missatge nou
    public function sendMessage(Request $request, $friendId) {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $myId = Auth::id();

        // Validació de seguretat: Només pots enviar si sou amics
        if (!$this->areFriends($myId, $friendId)) {
            return response()->json(['message' => 'No pots enviar missatges a usuaris que no són amics teus.'], 403);
        }

        $message = Message::create([
            'sender_id' => $myId,
            'receiver_id' => $friendId,
            'content' => $request->content
        ]);

        return response()->json([
            'message' => 'Missatge enviat',
            'data' => $message
        ]);
    }
}