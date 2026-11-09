<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopController extends Controller
{
    /**
     * ログインしていない人向けのトップページ。ログイン中の人はホームへ移す。
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('home');
        }

        return view('top');
    }
}
