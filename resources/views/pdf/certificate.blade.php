<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { width: 297mm; height: 210mm; font-family: "DejaVu Serif", serif; color: #1f2937; }

        .sheet {
            position: relative;
            width: 297mm;
            height: 210mm;
            background: #fdfbf3;
            overflow: hidden;
        }

        /* Dekorativ ikki qatorli ramka */
        .border-outer {
            position: absolute;
            top: 7mm; left: 7mm; right: 7mm; bottom: 7mm;
            border: 2.2px solid #0f3460;
        }
        .border-inner {
            position: absolute;
            top: 9.5mm; left: 9.5mm; right: 9.5mm; bottom: 9.5mm;
            border: 0.9px solid #b8860b;
        }

        /* Burchak bezaklari */
        .corner {
            position: absolute;
            width: 16mm; height: 16mm;
            border: 2.2px solid #b8860b;
        }
        .corner.tl { top: 5mm; left: 5mm; border-right: none; border-bottom: none; }
        .corner.tr { top: 5mm; right: 5mm; border-left: none; border-bottom: none; }
        .corner.bl { bottom: 5mm; left: 5mm; border-right: none; border-top: none; }
        .corner.br { bottom: 5mm; right: 5mm; border-left: none; border-top: none; }

        /* Orqa fondagi juda xira katta doira */
        .watermark {
            position: absolute;
            top: 76mm; left: 50%;
            width: 120mm; height: 120mm; margin-left: -60mm;
            border-radius: 50%;
            border: 10px solid #f2ecd8;
        }

        .content {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            text-align: center;
        }

        .brand {
            position: absolute;
            top: 19mm; left: 0; right: 0;
            font-size: 11.5px;
            letter-spacing: 5px;
            color: #533483;
            text-transform: uppercase;
            font-weight: bold;
        }
        .brand-rule {
            position: absolute;
            top: 24.5mm; left: 50%;
            width: 10mm; margin-left: -5mm;
            border-top: 0.7px solid #b8860b;
        }

        .title {
            position: absolute;
            top: 29mm; left: 0; right: 0;
            font-size: 46px;
            font-weight: bold;
            color: #0f3460;
            letter-spacing: 11px;
        }

        .title-underline {
            position: absolute;
            top: 50mm; left: 50%;
            width: 36mm; margin-left: -18mm;
            border-top: 1.4px solid #b8860b;
        }

        .subtitle {
            position: absolute;
            top: 53.5mm; left: 0; right: 0;
            font-size: 12px;
            color: #6b7280;
            letter-spacing: 1.2px;
            font-style: italic;
        }

        .given-to {
            position: absolute;
            top: 66mm; left: 0; right: 0;
            font-size: 11px;
            color: #6b7280;
        }

        .student-name {
            position: absolute;
            top: 73mm; left: 20mm; right: 20mm;
            font-size: 30px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 1px;
        }

        .name-underline {
            position: absolute;
            top: 90mm; left: 50%;
            width: 95mm; margin-left: -47.5mm;
            border-top: 0.8px solid #d1c9a3;
        }

        .desc {
            position: absolute;
            top: 95mm; left: 42mm; right: 42mm;
            font-size: 12.5px;
            color: #374151;
            line-height: 1.75;
        }
        .course-name { font-weight: bold; color: #0f3460; }

        .seal-wrap {
            position: absolute;
            top: 114mm; left: 50%;
            width: 32mm; margin-left: -16mm;
            text-align: center;
        }
        .seal-circle {
            position: relative;
            width: 32mm; height: 32mm;
            margin: 0 auto;
            border-radius: 50%;
            border: 1.8px solid #b8860b;
            background: #fdfbf3;
        }
        .seal-circle-inner {
            position: absolute;
            top: 2.1mm; left: 2.1mm; right: 2.1mm; bottom: 2.1mm;
            border-radius: 50%;
            border: 0.7px solid #b8860b;
        }
        .seal-monogram {
            position: absolute;
            top: 8mm; left: 0; right: 0;
            font-size: 15px;
            font-weight: bold;
            color: #0f3460;
            letter-spacing: 2px;
        }
        .seal-caption {
            position: absolute;
            top: 18.5mm; left: 0; right: 0;
            font-size: 6px;
            letter-spacing: 1.5px;
            color: #533483;
            font-weight: bold;
        }
        .ribbon {
            width: 13mm; height: 11mm;
            background: #0f3460;
            margin: 0 auto;
            position: relative;
        }
        .ribbon-tip {
            width: 0; height: 0;
            margin: 0 auto;
            border-left: 6.5mm solid transparent;
            border-right: 6.5mm solid transparent;
            border-top: 5mm solid #0f3460;
        }

        .bottom-row {
            position: absolute;
            bottom: 20mm; left: 20mm; right: 20mm;
        }
        .bottom-row table { width: 100%; border-collapse: collapse; }
        .bottom-row td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .bottom-label { font-size: 8.5px; color: #9ca3af; letter-spacing: 0.6px; text-transform: uppercase; }
        .bottom-value { font-size: 14px; font-weight: bold; color: #111827; margin-top: 1mm; }
        .bottom-line { border-top: 0.8px solid #9ca3af; width: 32mm; margin: 0 auto 1.8mm; }

        .qr-cell img { display: block; margin: 0 auto; }
        .cert-number {
            font-size: 7.5px;
            color: #9ca3af;
            margin-top: 1.2mm;
            letter-spacing: 0.3px;
        }

        .footer-note {
            position: absolute;
            bottom: 11mm; left: 0; right: 0;
            font-size: 7.5px;
            color: #c2bca6;
            letter-spacing: 0.4px;
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="watermark"></div>
    <div class="border-outer"></div>
    <div class="border-inner"></div>
    <div class="corner tl"></div>
    <div class="corner tr"></div>
    <div class="corner bl"></div>
    <div class="corner br"></div>

    <div class="content">
        <p class="brand">Yangi Asr Universiteti</p>
        <div class="brand-rule"></div>
        <p class="title">SERTIFIKAT</p>
        <div class="title-underline"></div>
        <p class="subtitle">Kursni muvaffaqiyatli tugatganlik to'g'risida</p>

        <p class="given-to">Ushbu sertifikat quyidagi shaxsga taqdim etiladi</p>
        <p class="student-name">{{ $user->full_name }}</p>
        <div class="name-underline"></div>

        <p class="desc">
            yuqorida ismi keltirilgan shaxs <span class="course-name">&laquo;{{ $course->title_uz }}&raquo;</span>
            kursini to'liq (100%) o'zlashtirib, muvaffaqiyatli yakunlaganligi ushbu sertifikat bilan tasdiqlanadi.
        </p>

        <div class="seal-wrap">
            <div class="seal-circle">
                <div class="seal-circle-inner"></div>
                <p class="seal-monogram">YAU</p>
                <p class="seal-caption">TASDIQLANGAN</p>
            </div>
            <div class="ribbon"></div>
            <div class="ribbon-tip"></div>
        </div>

        <div class="bottom-row">
            <table>
                <tr>
                    <td>
                        <div class="bottom-line"></div>
                        <p class="bottom-label">Yakuniy ball</p>
                        <p class="bottom-value">{{ number_format((float) $certificate->final_score, 2) }}</p>
                    </td>
                    <td class="qr-cell">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="62" height="62">
                        <p class="cert-number">&#8470; {{ $certificate->certificate_number }}</p>
                    </td>
                    <td>
                        <div class="bottom-line"></div>
                        <p class="bottom-label">Berilgan sana</p>
                        <p class="bottom-value">{{ \Carbon\Carbon::parse($certificate->issued_at)->format('Y-m-d') }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="footer-note">yangiasr.uz &nbsp;&bull;&nbsp; Sertifikat haqiqiyligini yuqoridagi QR kod orqali tekshiring</p>
    </div>
</div>
</body>
</html>
