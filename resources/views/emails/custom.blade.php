@if(($data['type'] ?? 'default') == 'emailverify')
        <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Weryfikacja Adresu Email</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .email-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            padding: 40px;
        }

        h1 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 28px;
            margin-top: 0;
            text-align: center;
            background: linear-gradient(90deg, #3498db, #9b59b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 16px;
            margin-bottom: 25px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            display: inline-block;
            background: linear-gradient(135deg, #8e44ad, #3498db);
            color: #fff !important;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
            transform: translateY(0);
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #9b59b6, #3498db);
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .highlight {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="email-card">

        <h1>Weryfikacja Adresu Email</h1>

        <p>Cześć!</p>

        <p>Dziękujemy za rejestrację. Aby dokończyć proces i uzyskać pełny dostęp do konta, zweryfikuj swój adres e-mail klikając w poniższy przycisk:</p>

        <div class="button-container">
            <a href="{{ $data['verify_link'] ?? '#' }}" class="button">Zweryfikuj E-mail</a>
        </div>

        <div class="highlight">
            <p><strong>Ważne:</strong> Link weryfikacyjny wygaśnie w ciągu 24 godzin. Jeśli nie zdążysz, możesz zawsze wygenerować nowy link w ustawieniach konta.</p>
        </div>

        <p>Jeśli to nie Ty utworzyłeś konto, zignoruj tę wiadomość - prawdopodobnie ktoś pomylił się wpisując adres e-mail.</p>

        <div class="footer">
            <p>Z poważaniem,<br>Zespół ZUT Weather</p>
            <p>© 2025 Twoja Firma. Wszelkie prawa zastrzeżone.</p>
        </div>
    </div>
</div>
</body>
</html>

@else
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Ogólne Powiadomienie</title>
    </head>
    <body>
    <p>{!! $data['msg'] !!}</p>
    </body>
    </html>
@endif
