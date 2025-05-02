<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Predefiniowane typy wiadomości i ich podstawowe dane.
     */
    protected static $messages = [
        'default'          => ['msg' => 'Jest to testowa wiadomość. Prosimy na nią nieodpowiadać'],
        'emailverify'      => ['msg' => 'Potwierdź swój adres e-mail, klikając w link poniżej.'],
        'warnings_meteo'   => ['msg' => 'Otrzymujesz najnowsze ostrzeżenia meteorologiczne dla Twojej lokalizacji.'],
        'warnings_hydro'   => ['msg' => 'Otrzymujesz najnowsze ostrzeżenia hydrologiczne dla Twojej lokalizacji.'],
        'temp_warning'     => ['msg' => 'Temperatura spadła poniżej Twojego progu.'],
    ];

    protected $chosenMsg;
    protected $messageType;
    protected $payload;

    /**
     * @param string $messageType Typ wiadomości
     * @param mixed  $payload     Dodatkowe dane (np. ostrzeżenia lub wartość temperatury)
     */
    public function __construct(string $messageType = 'default', $payload = null)
    {
        $this->messageType = $messageType;
        $this->payload     = $payload;

        $this->chosenMsg = self::$messages[$messageType] ?? ['msg' => 'Domyślna wiadomość (typ nieznany).'];
    }

    /**
     * Buduje wiadomość e-mail.
     */
    public function build()
    {
        $data = $this->chosenMsg;
        $data['type'] = $this->messageType;

        switch ($this->messageType) {
            case 'emailverify':
                $data['verify_link'] = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes(60),
                    ['id' => $this->payload['userId'] ?? null, 'hash' => sha1($this->payload['toEmail'] ?? '')]
                );
                break;
            case 'warnings_meteo':
            case 'warnings_hydro':
                $data['items'] = $this->payload; // tablica ostrzeżeń
                break;
            case 'temp_warning':
                $data['city']    = $this->payload['city'] ?? '';
                $data['current'] = $this->payload['current'] ?? '';
                $data['value']   = $this->payload['value'] ?? '';
                break;
        }

        $view = match ($this->messageType) {
            'warnings_meteo', 'warnings_hydro' => 'emails.warnings',
            'temp_warning'                     => 'emails.temp_warning',
            default                            => 'emails.custom',
        };

        return $this->from('zutweatherproject@gmail.com', 'IPZ')
            ->subject($this->getSubject())
            ->view($view)
            ->with(['data' => $data]);
    }

    /**
     * Generuje temat wiadomości w zależności od typu.
     */
    protected function getSubject(): string
    {
        return match ($this->messageType) {
            'emailverify'      => 'Potwierdzenie adresu e-mail',
            'warnings_meteo'   => 'Ostrzeżenia meteorologiczne',
            'warnings_hydro'   => 'Ostrzeżenia hydrologiczne',
            'temp_warning'     => 'Ostrzeżenie temperaturowe: poniżej progu',
            default            => 'Powiadomienie z IPZ',
        };
    }

    /**
     * Wysyła maila.
     *
     * @param string $to          Adres odbiorcy
     * @param int    $userId      ID użytkownika (tylko dla emailverify)
     * @param string $messageType Typ wiadomości
     * @param mixed  $payload     Dodatkowe dane do przekazania
     */
    public static function sendMail(string $to, int $userId, string $messageType, $payload = null)
    {
        $instance = new self($messageType, array_merge([
            'userId'  => $userId,
            'toEmail' => $to
        ], is_array($payload) ? $payload : []));

        Mail::to($to)->send($instance);
    }
}
