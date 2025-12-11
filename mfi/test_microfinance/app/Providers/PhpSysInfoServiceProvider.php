<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Plugins\CustomPingTest;

class PhpSysInfoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('phpsysinfo.pingtest', function ($app) {
            return new CustomPingTest('UTF-8');
        });
    }

    public function boot()
    {
        // Define constants if not already defined
        if (!defined('PSI_PLUGIN_PINGTEST_TIMEOUT')) {
            define('PSI_PLUGIN_PINGTEST_TIMEOUT', 2);
        }
        if (!defined('PSI_PLUGIN_PINGTEST_ACCESS')) {
            define('PSI_PLUGIN_PINGTEST_ACCESS', 'command');
        }
        if (!defined('PSI_PLUGIN_PINGTEST_ADDRESSES')) {
            define('PSI_PLUGIN_PINGTEST_ADDRESSES', '127.0.0.1');
        }
    }
} 