<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>取引完了</title>
</head>
<body style="margin:0; padding:0; background:#f6f6f6;">
    <div style="max-width:640px; margin:0 auto; padding:24px;">
        <div style="background:#ffffff; border:1px solid #e5e5e5; border-radius:8px; padding:20px;">
            <h2 style="margin:0 0 12px; font-size:18px; color:#333;">
                取引が完了しました。
            </h2>

            <p style="margin:0 0 10px; color:#333; line-height:1.7;">
                商品名：<strong>{{ $purchase->item->name }}</strong><br>
                購入者：{{ $purchase->user->name }}
            </p>

            <p style="margin:16px 0 0; color:#333; line-height:1.7;">
                取引チャットはこちら：<br>
                <a href="{{ route('trades.show', $purchase) }}" style="color:#1a73e8; text-decoration:underline;">
                    {{ route('trades.show', $purchase) }}
                </a>
            </p>
        </div>

        <p style="margin:12px 0 0; font-size:12px; color:#777; line-height:1.6;">
            ※このメールは送信専用です。返信してもお返事できません。
        </p>
    </div>
</body>
</html>