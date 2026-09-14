<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Backup Database Otomatis Setiap 24 Jam (Pukul 00:00 WIB)
Schedule::command('backup:database')->dailyAt('00:00');

