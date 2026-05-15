<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', [
            'title' => __('Usuarios'),
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'title' => __('Agregar usuario'),
        ]);
    }
    public function edit(int $userId)
    {
        return view('users.edit', [
            'title' => __('Editar usuario'),
            'userId' => $userId,
        ]);
    }
}