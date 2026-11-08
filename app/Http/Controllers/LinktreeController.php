<?php

namespace App\Http\Controllers;

use App\Models\Linktree;

class LinktreeController extends Controller
{
    public function index()
    {
        return view('pages.linktree', ['links' => Linktree::terurut()->get()]);
    }
}
