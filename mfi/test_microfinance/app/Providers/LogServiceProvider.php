<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        try {
            config([
                'logging.default' => 'daily',
                'logging.channels.daily' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/laravel.log'),
                    'level' => env('LOG_LEVEL', 'debug'),
                    'days' => 30,
                    'formatter' => \Monolog\Formatter\LineFormatter::class,
                    'formatter_with' => [
                        'format' => "[%datetime%] %channel%.%level_name%: %message% [%extra.file%:%extra.line% in %extra.function%] %context%\n",
                        'date_format' => 'Y-m-d H:i:s',
                    ],
                    'processors' => [
                        \App\Logging\DebugTraceProcessor::class,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            Log::emergency('Failed to configure logger: ' . $e->getMessage());
        }
    }
}
