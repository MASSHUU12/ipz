<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImgwApiClient;
use App\Models\User;
use App\Mail\SendMail;

class SendWarningsEmails extends Command
{
    protected $signature = 'send:warnings-emails';
    protected $description = 'Wyślij ostrzeżenia meteorologiczne i hydrologiczne na maile użytkowników';

    public function handle(ImgwApiClient $client)
    {
        $meteo = $client->getWarningsMeteo();
        $hydro = $client->getWarningsHydro();

        User::with('preference')
            ->where('email_verified_at', '!=', null)
            ->get()
            ->each(function (User $user) use ($meteo, $hydro) {
                $pref = $user->preference;
                if ($pref->notice_method !== 'email') {
                    return;
                }

                // ostrzeżenia meteo
                if ($pref->meteorological_warnings) {
                    $userCity = $pref->city;
                    $userMeteo = array_filter($meteo, fn($w) => in_array($userCity, $w['area']));
                    if (count($userMeteo)) {
                        SendMail::sendMail(
                            $user->email,
                            $user->id,
                            'warnings_meteo',
                            $userMeteo
                        );
                    }
                }

                // ostrzeżenia hydro
                if ($pref->hydrological_warnings) {
                    $userCity = $pref->city;
                    $userHydro = array_filter($hydro, fn($w) => in_array($userCity, $w['area']));
                    if (count($userHydro)) {
                        SendMail::sendMail(
                            $user->email,
                            $user->id,
                            'warnings_hydro',
                            $userHydro
                        );
                    }
                }
            });

        $this->info('Wysłano wszystkie e-maile z ostrzeżeniami.');
    }
}
