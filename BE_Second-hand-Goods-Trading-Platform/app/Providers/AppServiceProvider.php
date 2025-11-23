<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cấu hình JSON response không escape dấu / cho toàn bộ ứng dụng
        // Sử dụng Response macro để thêm options
        \Illuminate\Support\Facades\Response::macro('jsonUnescaped', function ($data = null, $status = 200, $headers = []) {
            $response = response()->json($data, $status, $headers);
            $response->setEncodingOptions(
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            return $response;
        });
    }
}
