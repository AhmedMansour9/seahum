<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppDownloadController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $userAgent = (string)$request->userAgent();

        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return redirect()->away(config('app_download.ios_url'));
        }

        if (preg_match('/android/i', $userAgent)) {
            return redirect()->away(config('app_download.android_url'));
        }

        return view('app-download', [
            'androidUrl' => config('app_download.android_url'),
            'iosUrl' => config('app_download.ios_url'),
        ]);
    }
}
