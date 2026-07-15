<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Coffee</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Nunito', -apple-system, sans-serif;
            background: #f7f7f9;
            color: #333;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
            padding: 40px 48px;
            text-align: center;
            max-width: 420px;
        }
        .card .emoji { font-size: 48px; }
        .card h1 { font-size: 22px; margin: 16px 0 8px; }
        .card p { margin: 0; color: #666; }
    </style>
</head>
<body>
<div class="card">
    <div class="emoji">{{ $attended ? '☕' : '🙁' }}</div>
    <h1>Дякуємо за відповідь!</h1>
    <p>
        @if ($attended)
            Раді, що зустріч відбулася. До наступної кави!
        @else
            Шкода, що цього разу не вийшло. Наступного циклу пощастить більше!
        @endif
    </p>
</div>
</body>
</html>