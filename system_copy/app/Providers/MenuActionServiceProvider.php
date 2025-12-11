<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\MenuAction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class MenuActionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //Log::info('Registering MenuActionServiceProvider');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //Log::info('Booting MenuActionServiceProvider');

        // Register menu actions
        $this->app->singleton('menu-actions', function ($app) {
            Log::info('Creating menu actions singleton');
            return new MenuActionService();
        });

        // Register menu action cache
        $this->app->singleton('menu-action-cache', function ($app) {
            Log::info('Creating menu action cache singleton');
            return new MenuActionCacheService();
        });

        // Register menu action events
        $this->app['events']->listen('menu-action.created', function ($menuAction) {
            Log::info('Menu action created', [
                'menu_action_id' => $menuAction->id,
                'menu_action_name' => $menuAction->name
            ]);
        });

        $this->app['events']->listen('menu-action.updated', function ($menuAction) {
            Log::info('Menu action updated', [
                'menu_action_id' => $menuAction->id,
                'menu_action_name' => $menuAction->name
            ]);
        });

        $this->app['events']->listen('menu-action.deleted', function ($menuAction) {
            Log::info('Menu action deleted', [
                'menu_action_id' => $menuAction->id,
                'menu_action_name' => $menuAction->name
            ]);
        });

        // Share menu actions with all views
        View::composer('*', function ($view) {
            try {
                $menuActions = MenuAction::with('menu')->get()
                    ->groupBy('menu.menu_name')
                    ->map(function ($actions) {
                        return $actions->pluck('name');
                    });
            } catch (\Exception $e) {
                // If database is not available, provide empty menu actions
                $menuActions = collect([]);
            }

            $view->with('menuActions', $menuActions);
        });
    }
} 