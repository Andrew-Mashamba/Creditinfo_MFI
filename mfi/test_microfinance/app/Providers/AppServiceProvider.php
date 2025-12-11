<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Commands\GenerateBillCommand;
use App\Handlers\GenerateBillCommandHandler;
use App\Services\NbcPayments\GepgGatewayService;
use App\Services\NbcPayments\GepgLoggerService;
use App\Services\Payments\InternalFundsTransferService;
use App\Services\Payments\ExternalFundsTransferService;
use App\Services\Payments\MobileWalletTransferService;
use App\Services\Payments\BillPaymentService;
use App\Services\ResellerApiService;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindMethod(GenerateBillCommand::class.'@handle', function ($command, $app) {
            return $app->make(GenerateBillCommandHandler::class)->handle($command);
        });

        // Register GEPG services with proper dependency injection
        $this->app->singleton(GepgLoggerService::class, function ($app) {
            return new GepgLoggerService();
        });

        $this->app->singleton(GepgGatewayService::class, function ($app) {
            return new GepgGatewayService($app->make(GepgLoggerService::class));
        });

        // Register new payment services
        $this->app->singleton(InternalFundsTransferService::class);
        $this->app->singleton(ExternalFundsTransferService::class);
        $this->app->singleton(MobileWalletTransferService::class);
        $this->app->singleton(BillPaymentService::class);

        // Register Reseller API Service
        $this->app->singleton(ResellerApiService::class, function ($app) {
            return new ResellerApiService();
        });

        // Register Permission Service
        $this->app->singleton('permission', function ($app) {
            return new PermissionService();
        });
        
        $this->app->singleton(PermissionService::class, function ($app) {
            return $app->make('permission');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Force URL for subdirectory installation
        if (config('app.url')) {
            \URL::forceRootUrl(config('app.url'));
        }

        // Register custom Blade directives for permission checking
        Blade::directive('canModule', function ($expression) {
            // Parse the expression to get module and action
            $parts = explode(',', str_replace(['(', ')', ' ', "'", '"'], '', $expression));
            $module = trim($parts[0] ?? '');
            $action = trim($parts[1] ?? '');
            
            return "<?php if(app('permission')->can('{$module}', '{$action}')): ?>";
        });
        
        Blade::directive('endcanModule', function () {
            return '<?php endif; ?>';
        });
        
        Blade::directive('canAnyModule', function ($expression) {
            // Parse module and array of actions
            return "<?php if(app('permission')->canAny{$expression}): ?>";
        });
        
        Blade::directive('endcanAnyModule', function () {
            return '<?php endif; ?>';
        });
        
        // Directive to check if user has any permission in a module
        Blade::directive('hasModuleAccess', function ($expression) {
            $module = str_replace(['(', ')', ' ', "'", '"'], '', $expression);
            return "<?php if(count(app('permission')->getModulePermissions('{$module}')) > 0): ?>";
        });
        
        Blade::directive('endhasModuleAccess', function () {
            return '<?php endif; ?>';
        });
    }
}
