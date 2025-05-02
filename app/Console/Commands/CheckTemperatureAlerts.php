<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Mail\SendMail;
use App\Services\ImgwApiClient;

class CheckTemperatureAlerts extends Command
{
    protected $signature = 'check:temp-warnings';
    protected $description = 'Wyślij powiadomienie email, gdy temperatura spadnie poniżej progu użytkownika';

    public function handle(ImgwApiClient $client)
    {
        $meteoData = $client->getMeteoData();

        User::with('preference')
            ->whereHas('preference', fn($q) => $q->where('temperature_warning', true))
            ->get()
            ->each(function($user) use ($meteoData) {
                $pref  = $user->preference;
                $city  = mb_strtolower($pref->city);
                $limit = (float) $pref->temperature_check_value;

                $entry = collect($meteoData)
                    ->first(fn($d) => mb_strtolower($d['city']) === $city);

                if (! $entry) {
                    return;
                }

                $current = $entry['temp'];

                if ($current < $limit) {
                    SendMail::sendMail(
                        $user->email,
                        $user->id,
                        'temp_warning',
                        [
                            'city'    => $pref->city,
                            'current' => $current,
                            'value'   => $limit,
                        ]
                    );
                }
            });

        $this->info('Sprawdzono ostrzeżenia temperaturowe.');
    }
}
