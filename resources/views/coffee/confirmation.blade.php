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
            padding: 24px 16px;
            box-sizing: border-box;
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
        .card--form {
            text-align: left;
            max-width: 560px;
            width: 100%;
        }
        .card .emoji { font-size: 48px; }
        .card--form .emoji { text-align: center; }
        .card h1 { font-size: 22px; margin: 16px 0 8px; }
        .card p { margin: 0; color: #666; }
        .question {
            border: 0;
            border-top: 1px solid #eee;
            margin: 0;
            padding: 20px 0 4px;
        }
        .question legend {
            font-weight: 600;
            font-size: 16px;
            padding: 0;
            color: #333;
        }
        .question--error legend { color: #c62828; }
        .option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            font-size: 16px;
            cursor: pointer;
        }
        .option input { width: 20px; height: 20px; margin: 0; }
        textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 12px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font: inherit;
            font-size: 16px;
            resize: vertical;
        }
        textarea:focus { outline: none; border-color: #9c27b0; }
        .error { color: #c62828; font-size: 14px; margin-top: 8px; }
        button {
            width: 100%;
            margin-top: 24px;
            padding: 14px 20px;
            border: 0;
            border-radius: 8px;
            background: #9c27b0;
            color: #fff;
            font: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #7b1fa2; }
        @media (max-width: 480px) {
            .card { padding: 28px 20px; }
        }
    </style>
</head>
<body>
<div class="card {{ $questions !== [] ? 'card--form' : '' }}">
    @if ($submitted)
        <div class="emoji">☕</div>
        <h1>Дякуємо за ваш фідбек!</h1>
        <p>Ваші відповіді збережено — вони допоможуть зробити наступні Random Coffee кращими.</p>
    @elseif ($questions !== [])
        <div class="emoji">☕</div>
        <h1>{{ config('coffee.survey.title') }}</h1>
        <p>{{ config('coffee.survey.intro') }}</p>

        <form method="post" action="{{ $formAction }}">
            @foreach ($questions as $index => $question)
                @php $field = 'answers.' . $question['key']; @endphp
                <fieldset class="question {{ $errors->has($field) ? 'question--error' : '' }}">
                    <legend>{{ $index + 1 }}. {{ $question['label'] }}</legend>

                    @if ($question['type'] === \App\Services\RandomCoffee\FeedbackSurvey::TYPE_TEXT)
                        <textarea name="answers[{{ $question['key'] }}]" rows="4"
                                  maxlength="{{ $question['max_length'] }}">{{ $answers[$question['key']] ?? '' }}</textarea>
                    @else
                        @foreach ($question['options'] as $value => $label)
                            <label class="option">
                                <input type="radio" name="answers[{{ $question['key'] }}]" value="{{ $value }}"
                                       {{ ($answers[$question['key']] ?? null) === (string) $value ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    @endif

                    @if ($errors->has($field))
                        <p class="error">{{ $errors->first($field) }}</p>
                    @endif
                </fieldset>
            @endforeach

            <button type="submit">Надіслати</button>
        </form>
    @else
        <div class="emoji">{{ $attended ? '☕' : '🙁' }}</div>
        <h1>Дякуємо за відповідь!</h1>
        <p>
            @if ($attended)
                Раді, що зустріч відбулася. До наступної кави!
            @else
                Шкода, що цього разу не вийшло. Наступного циклу пощастить більше!
            @endif
        </p>
    @endif
</div>
</body>
</html>
