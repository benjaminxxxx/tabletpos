<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', ['title' => __('Productos')]);
    }

    public function register()
    {
        return view('products.register', ['title' => __('Registrar productos')]);
    }
}