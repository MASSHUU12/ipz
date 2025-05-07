@if(isset($data['reset_link']) && isset($data['username']))
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Resetowanie Hasła</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            padding: 20px;
            color: #333;
        }
        .card {
            max-width: 600px;
            background: #fff;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #2c7be5;
            font-size: 22px;
            text-align: center;
        }
        .icon {
            text-align: center;
            font-size: 40px;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(to right, #00b4db, #0083b0);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            margin: 20px 0;
            font-weight: bold;
        }
        .highlight {
            background: #fdf3d8;
            border-left: 4px solid #f9ca24;
            padding: 10px;
            margin-top: 20px;
        }
        .footer {
            font-size: 12px;
            color: #888;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🔐</div>
    <h1>Resetowanie Hasła</h1>

    <p>Cześć {{ $data['username'] }},</p>
    <p>Wygląda na to, że ktoś (może Ty?) poprosił o zresetowanie hasła.</p>
    <p>Aby ustawić nowe hasło, kliknij poniższy link:</p>

    <p style="text-align:center;">
        <a href="{{ $data['reset_link'] }}" class="btn">Resetuj Hasło</a>
    </p>

    <div class="highlight">
        <strong>Uwaga:</strong> Link do resetowania hasła wygaśnie po 60 minutach. Jeśli link wygaśnie, możesz ponownie poprosić o resetowanie hasła.
    </div>

    <p style="margin-top: 20px;">
        Jeśli nie prosiłeś(-aś) o zmianę hasła, zignoruj tę wiadomość lub skontaktuj się z naszym wsparciem technicznym.
    </p>

    <div class="footer">
        Z poważaniem,<br>
        Zespół ZUT Weather<br>
        © 2025 ZUT Weather. Wszelkie prawa zastrzeżone.
    </div>
</div>
</body>
</html>

@else
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Ogólne Powiadomienie</title>
</head>
<body>
    <p>{{ $data['msg'] ?? 'Brak treści wiadomości.' }}</p>
</body>
</html>
@endif
