<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ユーザー登録完了</title>
        <style>
            body { max-width: 480px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .button { display: inline-block; padding: 8px 16px; background: #2563eb; color: #fff; text-decoration: none; }
        </style>
    </head>
    <body>
        <h1>ユーザー登録が完了しました。</h1>
        <p>アカウントの作成が完了し、現在はログイン済みです。日報を振り返り、次の行動につなげていきましょう。</p>

        <a class="button" href="{{ route('home') }}">自分の日報一覧へ</a>
    </body>
</html>
