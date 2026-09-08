<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To'lov holati — Yangi Asr Universiteti</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #0f3460, #533483);
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        .icon.paid    { background: #dcfce7; color: #16a34a; }
        .icon.pending { background: #fef3c7; color: #d97706; }
        .icon.failed  { background: #fee2e2; color: #dc2626; }
        h1 { font-size: 1.05rem; color: #111827; margin: 0 0 8px; }
        p.desc { font-size: 0.875rem; color: #6b7280; margin: 0 0 20px; line-height: 1.5; }
        .details { background: #f9fafb; border-radius: 12px; padding: 14px 16px; text-align: left; font-size: 0.8rem; color: #374151; }
        .details div { display: flex; justify-content: space-between; padding: 3px 0; }
        .details span:first-child { color: #9ca3af; }
        .refresh {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 22px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0f3460, #533483);
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="card">
    @if($status === 'paid')
        <div class="icon paid">&#10003;</div>
        <h1>To'lov muvaffaqiyatli qabul qilindi!</h1>
        <p class="desc">Rahmat. To'lovingiz shartnomangizga hisobga olindi.</p>
    @elseif($status === 'pending')
        <div class="icon pending">&#8987;</div>
        <h1>To'lov holati tekshirilmoqda</h1>
        <p class="desc">Agar to'lovni endigina yakunlagan bo'lsangiz, bir necha soniyadan so'ng sahifani yangilang.</p>
        <a class="refresh" href="{{ url()->current() }}">Sahifani yangilash</a>
    @else
        <div class="icon failed">&times;</div>
        <h1>To'lov amalga oshmadi</h1>
        <p class="desc">To'lov bekor qilindi yoki xatolik yuz berdi. Qaytadan urinib ko'ring yoki kassaga murojaat qiling.</p>
    @endif

    <div class="details">
        <div><span>Shartnoma</span><span>{{ $contractNumber ?? '—' }}</span></div>
        <div><span>Summa</span><span>{{ number_format($amount, 0, '', ' ') }} so'm</span></div>
    </div>
</div>
</body>
</html>
