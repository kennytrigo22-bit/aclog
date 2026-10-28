<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purge des statistiques anciennes (visites > 13 mois, presences > 1 jour). Necessite la tache cron "schedule:run".
\Illuminate\Support\Facades\Schedule::command('model:prune', ['--model' => [\App\Models\Visite::class, \App\Models\Presence::class]])->daily();
