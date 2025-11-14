<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class TestController extends Controller
{
    public function showUsers()
    {
        $users = User::with(['office', 'outlet'])->get();
        
        return response()->json($users->map(function($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'office' => $user->office ? [
                    'name' => $user->office->name,
                    'type' => $user->office->type
                ] : null,
                'outlet' => $user->outlet ? [
                    'name' => $user->outlet->name
                ] : null
            ];
        }));
    }
}
