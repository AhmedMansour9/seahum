<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' || app()->getLocale() === 'eg' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ translate('Download_the_SEA_HUM_app') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f8fb; font-family: system-ui, sans-serif; color: #0b2a43; }
        .card { background: #fff; border-radius: 20px; padding: 40px 32px; max-width: 380px; width: calc(100% - 32px); text-align: center; box-shadow: 0 10px 40px rgba(11, 42, 67, .1); }
        h1 { font-size: 22px; margin: 0 0 24px; }
        a { display: block; padding: 14px 20px; margin-top: 12px; border-radius: 12px; background: #0b2a43; color: #fff; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ translate('Download_the_SEA_HUM_app') }}</h1>
    <a href="{{ $androidUrl }}">{{ translate('Android') }}</a>
    <a href="{{ $iosUrl }}">{{ translate('iPhone') }}</a>
</div>
</body>
</html>
