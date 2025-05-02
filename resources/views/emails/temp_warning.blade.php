@component('mail::message')
    # Ostrzeżenie temperaturowe

    Temperatura w **{{ $data['city'] }}** spadła do **{{ $data['current'] }}°C**,
    co jest poniżej Twojego ustawionego progu **{{ $data['value'] }}°C**.

    Jeśli chcesz zmienić próg ostrzeżeń, odwiedź ustawienia konta.

    ---
    Bezpiecznego dnia,
    Zespół IPZ
@endcomponent
