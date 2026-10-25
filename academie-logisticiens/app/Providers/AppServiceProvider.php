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
        \Illuminate\Validation\Rules\Password::defaults(function () {
            $rule = \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers();

            return app()->environment('production') ? $rule->uncompromised() : $rule;
        });

        $this->app->singleton('identite', fn () => [
            'siteTitre' => \App\Models\Parametre::siteTitre(),
            'siteSlogan' => \App\Models\Parametre::siteSlogan(),
            'identifiantsLegaux' => \App\Models\Parametre::identifiantsLegaux(),
            'emailContact' => (string) (\App\Models\Parametre::valeur('email_contact', 'contact@cabinet-aclog.com') ?: 'contact@cabinet-aclog.com'),
        ]);

        \Illuminate\Support\Facades\View::composer('*', fn ($view) => $view->with($this->app->make('identite')));
    }
}
