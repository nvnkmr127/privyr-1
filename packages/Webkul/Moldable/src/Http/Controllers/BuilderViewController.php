<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Contracts\View\View;

class BuilderViewController
{
    public function index(): View
    {
        return view('moldable::builder.index');
    }
}
