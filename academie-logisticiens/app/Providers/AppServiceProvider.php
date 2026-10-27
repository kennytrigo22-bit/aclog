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
            'telContact' => (string) (\App\Models\Parametre::valeur('telephone_contact_1', '+242 06 828 79 15') ?: '+242 06 828 79 15'),
            'whatsapp' => preg_replace('/\D+/', '', (string) \App\Models\Parametre::valeur('whatsapp_numero', '242068287915')) ?: '242068287915',
            'lienYoutube' => (string) (\App\Models\Parametre::valeur('lien_youtube', 'https://www.youtube.com/@academiedeslogisticiensdid2613') ?: 'https://www.youtube.com/@academiedeslogisticiensdid2613'),
            'lienFacebook' => (string) (\App\Models\Parametre::valeur('lien_facebook', 'https://www.facebook.com/profile.php?id=100088676956387') ?: 'https://www.facebook.com/profile.php?id=100088676956387'),
        ]);

        \Illuminate\Support\Facades\View::composer('*', fn ($view) => $view->with($this->app->make('identite')));
    }
}
