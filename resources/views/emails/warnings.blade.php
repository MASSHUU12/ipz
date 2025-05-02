@component('mail::message')
    # Ostrzeżenia dla Twojej lokalizacji

    {{ $data['msg'] }}

    @foreach($data['items'] as $warning)
        - **{{ ucfirst($warning['phenomenon']) }}** (poziom {{ $warning['level'] }})

        _Od:_ {{ \Illuminate\Support\Carbon::parse($warning['start'])->toDayDateTimeString() }}
        _Do:_ {{ \Illuminate\Support\Carbon::parse($warning['end'])->toDayDateTimeString() }}
        _Obszar:_ {{ implode(', ', $warning['area']) }}

    @endforeach

    ---
    Jeśli chcesz zobaczyć więcej szczegółów, odwiedź naszą stronę.

    Pozdrawiamy,
    Zespół IPZ
@endcomponent
