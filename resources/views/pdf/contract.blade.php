<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Serif, serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
        }
        .page { padding: 20px 35px; }
        h2 { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        h3 { text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 8px; }
        .section-title { font-weight: bold; text-align: center; margin: 10px 0 5px 0; font-size: 10.5px; }
        p { margin-bottom: 4px; text-align: justify; }
        .bold { font-weight: bold; }
        .two-col { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .two-col td { vertical-align: top; width: 50%; padding: 0 8px; }
        .sign-line { border-bottom: 1px solid #000; width: 160px; margin-top: 25px; margin-bottom: 2px; }
        .qr-table { width: 100%; margin-top: 15px; text-align: center; border-collapse: collapse; }
        .qr-table td { width: 50%; text-align: center; padding: 5px; }
        .payment-box { border: 1px solid #000; padding: 8px; margin-top: 10px; font-size: 10px; }
        .footer-title { text-align: center; font-weight: bold; font-size: 12px; margin-top: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 6px 0; }
        .info-table td { padding: 2px 4px; }
        .indent { padding-left: 15px; }
        .sub-indent { padding-left: 30px; }
    </style>
</head>
<body>
<div class="page">

    <h2>To'lov-kontrakt (Ikki tomonlama) asosida mutaxassis tayyorlashga</h2>
    <h3>KONTRAKT № {{ $contract->contract_number }}</h3>

    <table style="width:100%; margin-bottom:8px;">
        <tr>
            <td>Toshkent shahri</td>
            <td style="text-align:right;">{{ \Carbon\Carbon::parse($contract->created_at)->format('Y-m-d') }}</td>
        </tr>
    </table>

    <p>Yangi Asr Universiteti (keyingi o'rinlarda "Ta'lim muassasasi") nomidan rektor (direktor)
        <span class="bold">Ishandjanov Baxtiyer Ilxamovich</span> bir tomondan,
        <span class="bold">{{ $applicant->jshshir ? $applicant->jshshir.' – ' : '' }}{{ $applicant->last_name }} {{ $applicant->first_name }} {{ $applicant->middle_name }}</span>
        (keyingi o'rinlarda "Buyurtmachi") ikkinchi tomondan, birgalikda "Tomonlar" deb
        ataladigan shaxslar mazkur kontraktni quyidagicha tuzdilar:</p>

    <p class="section-title">I. KONTRAKT PREDMETI</p>

    <p>1.1. Universitet talabaga Malaka talablari doirasida o'quv dasturlariga muvofiq oliy ta'lim
        xizmatini haq evaziga ko'rsatish majburiyatini oladi. Talaba ko'rsatilgan ta'lim xizmatlari uchun tegishli
        to'lovni to'lash majburiyatini oladi.</p>

    <table class="info-table">
        <tr>
            <td style="width:40%">Ta'lim bosqichi:</td>
            <td class="bold">{{ $degreeLabel }}</td>
        </tr>
        <tr>
            <td>Ta'lim shakli:</td>
            <td class="bold">{{ $applicant->study_form === 'full_time' ? 'Kunduzgi' : ($applicant->study_form === 'evening' ? 'Kechki' : 'Sirtqi') }}</td>
        </tr>
        <tr>
            <td>O'qish muddati:</td>
            <td class="bold">{{ $direction->duration_years }} yil</td>
        </tr>
        <tr>
            <td>Ta'lim yo'nalishi:</td>
            <td class="bold">{{ $direction->hemis_code ? $direction->hemis_code.' - ' : '' }}{{ $direction->name_uz }}</td>
        </tr>
    </table>

    <p>1.2. Ta'lim dasturlarini o'zlashtirib, yakuniy davlat attestatsiyasidan muvaffaqiyatli o'tgandan so'ng
        talabaga ta'lim va malaka to'g'risidagi hujjat beriladi. Yakuniy attestatsiyadan o'tmagan yoki yakuniy
        attestatsiyadan qoniqarsiz natijalar olgan talabaga, shuningdek ta'lim dasturlarining bir qismini o'zlashtirgan
        yoki Universitetning talabalar safidan chetlashtirilganlarga o'qish davri to'g'risidagi ma'lumotnoma
        beriladi.</p>

    <p class="section-title">II. TOMONLARNING HUQUQ VA MAJBURIYATLARI</p>

    <p>2.1. Universitet quyidagi huquqlarga ega:</p>
    <p class="indent">2.1.1. Mustaqil ravishda o'quv jarayonini amalga oshirish, baholash tizimlari va shakllarini tashkil etish;</p>
    <p class="indent">2.1.2. O'zbekiston Respublikasi qonunchiligiga, Universitetning ta'sis va ichki hujjatlariga hamda ushbu
        shartnomaga muvofiq talabaga nisbatan rag'batlantirish va intizomiy choralarni qo'llash;</p>
    <p class="indent">2.1.3. Mazkur shartnoma majburiyatlari va Universitet ichki tartib qoidalarini buzgan talaba bilan
        shartnomani bir tomonlama bekor qilish va uni talabalar safidan chetlashtirish choralarini ko'rish;</p>
    <p class="indent">2.1.4. Yakuniy davlat attestatsiyasidan muvaffaqiyatli o'tgan talabalarga belgilangan namunadagi xujjat
        taqdim etish;</p>

    <p>2.2. Talaba quyidagi huquqlarga ega:</p>
    <p class="indent">2.2.1. Universitet to'g'risida ma'lumot olish, sifatli ta'lim ko'rsatilishi va ta'lim olish uchun zaruriy shart-sharoitlarini yaratib berishni talab qilish;</p>
    <p class="indent">2.2.2. Ta'lim dasturlarini o'zlashtirish uchun zarur bo'lgan Universitetning mol-mulkidan belgilangan tartibda foydalanish;</p>
    <p class="indent">2.2.3. Universitet tomonidan tashkil etiladigan ijtimoiy, madaniy, ko'ngilochar va boshqa tadbirlarda ishtirok etish;</p>
    <p class="indent">2.2.4. O'z bilimi, ko'nikmasi, qobiliyati va malakasi to'g'risida to'liq va ishonchli ma'lumotlarni olish.</p>

    <p>2.3. Universitetning majburiyatlari:</p>
    <p class="indent">2.3.1. Ta'lim uchun O'zbekiston Respublikasining "Ta'lim to'g'risida"gi Qonuniga muvofiq Universitet Nizomida nazarda tutilgan shart-sharoitlarni yaratish;</p>
    <p class="indent">2.3.2. Talabalarning huquqlarini ta'minlash;</p>
    <p class="indent">2.3.3. Talabaning tanlagan ta'lim yo'nalishi bo'yicha tasdiqlangan ta'lim dasturlariga muvofiq Malaka talablari asosida tayyorlash;</p>
    <p class="indent">2.3.4. Boshlanadigan o'quv semestrida o'qitish uchun to'lovning belgilangan miqdori to'g'risida o'z vaqtida (o'quv jarayoni boshlanishidan bir oy oldin) xabar berish;</p>
    <p class="indent">2.3.5. Abituriyentning o'qishga qabul qilinganligi to'g'risidagi Universitet buyrug'ini bir yillik shartnoma pulining kamida 50 foizi to'langandan so'ng joriy o'quv semestri boshlangan kundan boshlab bir oylik muddatdan kechiktirilmasdan imzolash;</p>
    <p class="indent">2.3.6. Talabalarga inson qadr-qimmatini hurmat qilish, jismoniy va ruhiy zo'ravonlikning barcha turlaridan himoya qilish, shaxsni haqorat qilish, hayot va salomatlikni himoya qilish;</p>
    <p class="indent">2.3.7. Ta'lim jarayonida shaffoflikni ta'minlash, korrupsiyaga qarshi kurash choralarini ko'rish;</p>

    <p>2.4. Talaba majburiyatlari:</p>
    <p class="indent">2.4.1. Ta'lim xizmatlari uchun to'lovni Universitet tomonidan shartnomada belgilangan muddatlarida o'z vaqtida to'lash;</p>
    <p class="indent">2.4.2. Tariflar o'zgarganda, Universitet bilan kelishgan holda mazkur shartnomaga tegishli o'zgartirishlar kiritish hamda o'qishning qolgan muddati uchun to'lovni bir oy muddat ichida amalga oshirish;</p>
    <p class="indent">2.4.3. Tashkilotdan yoki bankdan pul o'tkazganlik haqida bank tasdiqnomasi va shartnoma nusxasini Universitetga o'z vaqtida taqdim qilish;</p>
    <p class="indent">2.4.4. Ta'lim uchun to'lovni amalga oshirganda to'lov topshiriqnomasida shartnomaning raqami va tuzilgan sanasi, ta'lim oluvchining ismi-sharifi, ID kodi hamda o'qiyotgan kursini to'liq ko'rsatish;</p>
    <p class="indent">2.4.5. Universitetning ichki tartib, texnik va yong'in xavfsizligi qoidalariga rioya qilish;</p>
    <p class="indent">2.4.6. Universitet mulkini asrash, unga yetkazilgan zararni qonunchilikka muvofiq qoplash;</p>
    <p class="indent">2.4.7. Talabalar, professor-o'qituvchilar va xodimlarga hurmat bilan munosabatda bo'lish.</p>

    <p class="section-title">III. O'ZARO HISOB-KITOB</p>

    <p>3.1. Joriy o'quv yili uchun universitetda talabaning to'lov-kontrakt asosida bir yillik o'qitish qiymati
        {{ $degreeLabel }} bosqichi bo'yicha ta'limning
        {{ $applicant->study_form === 'full_time' ? 'Kunduzgi' : ($applicant->study_form === 'evening' ? 'Kechki' : 'Sirtqi') }} shakli bo'yicha
        <span class="bold">{{ number_format($contract->amount, 0, '.', ' ') }} ({{ $amountInWords }}) so'm</span>, etib belgilangan.</p>

    <p>3.2. Shartnoma summasi O'zbekiston Respublikasining milliy valyutasi (so'm)da to'lanadi;</p>

    <p>3.3. Kunduzgi va kechki ta'lim oluvchi talabalar uchun 100 % kontrakt to'lovi 8 ga bo'lib to'lanadi:
        joriy o'quv yilining 1-oktabridan 1-mayigacha har oyning 1-sanasigacha 12,5% dan kontrakt to'lovlari
        amalga oshirilishi lozim. Sirtqi ta'lim oluvchi talabalar uchun esa quyidagicha to'lov amalga oshiriladi:
        Joriy o'quv yilining 1-semestri boshlangunga qadar 50%i, qolgan 50% kontrakt to'lovi 2-semestr
        boshlangunga qadar amalga oshirilishi lozim.</p>

    <p>3.4. Talaba joriy o'quv yili uchun shartnoma bo'yicha to'lov amalga oshirmasa va to'lovni tasdiqlovchi
        hujjatlar bo'lmasa, talaba darslardan chetlashtiriladi;</p>

    <p>3.5. Tomonlar Universitetning xizmat ko'rsatuvchi banki hisob raqamiga pul mablag'lari kelib tushgan
        kunni, to'lov majburiyatlari bajarilgan kun deb tan oladilar;</p>

    <p>3.6. Ta'lim xizmatlari uchun talaba tomonidan to'lov amalga oshirilmagan bo'lsa, talaba keyingi
        semestrga, shuningdek oraliq, joriy, yakuniy, davlat attestatsiyasiga hamda o'tkazilayotgan o'quv
        darslariga kiritilmaydi. Bunday holda, Universitet ushbu shartnoma shartlariga muvofiq ushbu shartnomani
        bir tomonlama bekor qilishga haqli. Talabalar uchun darslarni uzrli sababsiz o'tkazib yuborish, ta'lim
        xizmatlari uchun to'lov to'lamaslik uchun asos bo'la olmaydi.</p>

    <p>3.7. Mazkur shartnomaning 5.3. bandiga muvofiq shartnoma bekor qiliniganda ta'lim uchun
        yo'naltiligan to'lov qiymati ta'lim xizmatlari ko'rsatilgan davrga mutanosib tarzda soliq va majburiy
        to'lovlar chegirib tashlangan holda talabaga qaytarib beriladi.</p>

    <p>3.8. Ta'lim oluvchi talaba imtiyozli kredit asosida bank idoralaridan ta'lim krediti olgan bo'lsa,
        shartnomaning 5.3. bandiga muvofiq shartnoma bekor qilinganda, ta'lim uchun yo'naltiligan to'lov
        qiymati, ta'lim xizmatlari ko'rsatilgan davrga mutanosib tarzda soliq va majburiy to'lovlar chegirib
        tashlangan holda kredit ajratuvchi bankka qaytarib beriladi.</p>

    <p>3.9. Ta'lim xizmatlari ko'rsatilayotgan talaba bilan har bir semestr uchun Universitet o'z
        majburiyatlarini bajarayotganligi to'g'risida hisob-faktura tayyorlaydi. Hisob-fakturani imzolash
        talabaning majburiyati bo'lmay, balki uning ixtiyoriy huquqi hisoblanadi.</p>

    <p>3.10. Talaba Universitet bilan aloqani uzishi natijasida o'quv jarayonida qatnashmasa, kontrakt
        to'lovlaridan qayta xisob-kitob qilinib to'langan to'lov qaytarilmaydi.</p>

    <p>3.11. Talaba bilan tuzilgan kontrakt talabaning tashabbusiga ko'ra ariza asosida bekor qilinsa,
        talaba ariza berilgan kundan boshlab ta'lim xizmatlari ko'rsatilmagan davr uchun qayta xisob-kitob
        qilinadi.</p>

    <p class="section-title">IV. UNIVERSITET LITSENZIYASI BEKOR QILINISHI HOLATIDA TO'LOVLARNI QAYTARISH TARTIBI</p>

    <p>4.1. Universitet litsenziyasi tegishli vakolatli sud (davlat organlari)ning qarori bilan bekor qilingan
        taqdirda, Universitet tomonidan ko'rsatilgan va ko'rsatilmagan ta'lim xizmatlarini baholash va moliyaviy
        majburiyatlarni belgilash quyidagi tartibda amalga oshiriladi:</p>

    <p>4.2. Litsenziya bekor qilingan sanaga qadar:</p>
    <p class="indent">4.2.1. Universitet tomonidan to'liq o'qitilgan va yakunlangan fanlar (modullar) bo'yicha to'lovlar qaytarilmaydi, chunki ushbu xizmatlar to'liq hajmda ko'rsatilgan hisoblanadi;</p>
    <p class="indent">4.2.2. Universitet tomonidan qisman o'qitilgan fanlar (modullar) bo'yicha to'lovlar qaytarilmaydi, chunki Universitet bu fanlarga tayyorgarlik ko'rish, o'quv dasturlarini ishlab chiqish, professor-o'qituvchilar mehnatini ta'minlash va boshqa tashkiliy-ma'muriy xarajatlarni amalga oshirgan;</p>
    <p class="indent">4.2.3. Talaba tomonidan to'langan semestr yoki o'quv yili to'lovining 70 foizi Universitetning ta'lim jarayonini tashkil etishga bog'liq doimiy xarajatlari hisobiga o'tkaziladi va qaytarilmaydi;</p>

    <p>4.3. To'lov-kontrakt shartnоmasining muddati davomida to'langan to'lovlar quyidagi tartibda qayta hisob-kitob qilinadi:</p>
    <p class="indent">4.3.1. Universitet tomonidan ko'rsatilgan xizmatlar qiymati quyidagilarni o'z ichiga oladi:</p>
    <p class="sub-indent">a) Talaba o'qishga qabul qilingan kundan boshlab litsenziya bekor qilingan kunigacha bo'lgan davrda Universitet tomonidan ko'rsatilgan barcha amaliy xizmatlar;</p>
    <p class="sub-indent">b) Talaba ishtirok etgan yoki etmagan bo'lishidan qat'i nazar, belgilangan o'quv rejasiga muvofiq tashkil etilgan barcha mashg'ulotlar;</p>
    <p class="sub-indent">d) O'quv yili boshida ta'lim dasturini tashkil etish uchun Universitet tomonidan amalga oshirilgan barcha tayyorgarlik ishlari;</p>
    <p class="sub-indent">e) Universitet tomonidan o'quv jarayonini tashkil etish va ta'minlash bilan bog'liq barcha doimiy xarajatlar;</p>
    <p class="sub-indent">f) boshqa xarajatlar.</p>

    <p>4.4. Talaba tomonidan oldindan to'langan, lekin Universitet tomonidan litsenziya bekor qilinishi sababli
        ko'rsatilmagan ta'lim xizmatlari uchun to'lovlar qayta hisob-kitob qilingandan so'ng, 45 bank ish kunidan
        kechiktirmay Talabaning yozma arizasiga asosan qaytarib beriladi.</p>

    <p>4.5. Ushbu bo'limda ko'rsatilgan qayta hisob-kitob va to'lovlarni qaytarish tartibi faqat Universitetning
        litsenziyasi bekor qilingan holatlarga taalluqli bo'lib, boshqa hollarga nisbatan amal qilmaydi.</p>

    <p class="section-title">V. SHARTNOMANI O'ZGARTIRISH, BEKOR QILISH VA NIZOLARNI XAL ETISH TARTIBI</p>

    <p>5.1. Mazkur shartnomani tomonlarning kelishuviga binoan yoki qonunchilikka muvofiq o'zgartirilishi mumkin;</p>
    <p>5.2. Shartnomaga kiritilgan o'zgartirish, shartnomaga qo'shimcha kelishuv tuzish bilan rasmiylashtiriladi.</p>
    <p>5.3. Shartnomani quyidagi hollarda bekor qilish mumkin:</p>
    <p class="indent">5.3.1. Tomonlarning o'zaro roziligi bilan;</p>
    <p class="indent">5.3.2. Universitet nizomiga muvofiq, ma'muriyat qaroriga ko'ra;</p>
    <p class="indent">5.3.3. O'qish uchun to'lov belgilangan muddat ichida to'lanmasa;</p>
    <p class="indent">5.3.4. Talabaning tashabbusiga ko'ra;</p>
    <p class="indent">5.3.5. Talaba sababsiz bir oy davomida ta'lim jarayonida qatnashmasligi talabani Universitet bilan aloqani uzish deb tan olinadi. Universitet bilan aloqani uzgan talaba bilan tuzilgan shartnoma Universitet tashabbusiga ko'ra bekor qilinadi.</p>
    <p class="indent">5.3.6. Qonunchilikda ko'zda tutilgan boshqa hollarda.</p>
    <p>5.4. Mazkur shartnoma yuzasidan kelib chiqadigan nizolar va kelishmovchiliklar taraflarning o'zaro kelishuvga erishishlari yo'li bilan hal etiladi, o'zaro bitimga erishilmagan taqdirda fuqarolik sudlariga murojaat etish yo'li bilan ko'rib chiqiladi.</p>

    <p class="section-title">VI. SHARTNOMANING AMAL QILISH MUDDATI</p>

    <p>6.1. Ushbu shartnoma tomonlar tomonidan tuzilgan kundan boshlab kuchga kiradi va tomonlar o'z majburiyatlarini to'liq bajarmaguncha amal qiladi.</p>
    <p>6.2. Agar talabaga akademik ta'til berilsa, shartnoma ta'til muddati davomida uzaytiriladi, akademik ta'til paytida o'qish uchun to'lov olinmaydi.</p>

    <p class="section-title">VII. YAKUNIY QOIDALAR</p>

    <p>7.1. Ta'lim xizmatini ko'rsatish davri, Universitet Kengashi tomonidan tasdiqlangan o'quv jarayoni jadvali asosida belgilanadi.</p>
    <p>7.2. Ushbu shartnoma Ikki nusxada tuzildi, bir nusxa – Universitetda, ikkinchi nusxa talabada saqlanadi. Barcha nusxalar bir xil yuridik kuchga ega.</p>
    <p>7.3. Ushbu shartnomaga o'zgartish va qo'shimchalar faqat yozma shaklda kiritilganda va tomonlarning vakolatli vakillari tomonidan imzolanganda yuridik kuchga ega bo'ladi.</p>

    <p class="section-title">VIII. TOMONLARNING REKVIZITLARI VA IMZOLARI</p>

    <table class="two-col">
        <tr>
            <td>
                <p class="bold">Ta'lim muassasasi:</p>
                <p>Yangi Asr Universiteti</p>
                <p><span class="bold">Pochta manzili:</span></p>
                <p>Address: Toshkent shahri</p>
                <p>E-mail: ir_yangiasr_edu@outlook.com</p>
                <p>Tel.: +998 (71) 200-29-92</p>
                <p class="bold">Bank rekvizitlari:</p>
                <p>Bank nomi:</p>
                <p>H/R: 20208000505405372001</p>
                <p>STIR: 308593412</p>
                <p>MFO: 00997</p>
                <p class="bold">Ta'lim muassasasi rahbari:</p>
                <div class="sign-line"></div>
                <p>Ishandjanov Baxtiyer Ilxamovich</p>
            </td>
            <td>
                <p class="bold">Buyurtmachi:</p>
                <p>F.I.Sh.: {{ $applicant->jshshir ? $applicant->jshshir.'-' : '' }}{{ $applicant->last_name }} {{ $applicant->first_name }} {{ $applicant->middle_name }}</p>
                <p>Yashash manzili: {{ $applicant->region?->name_uz ?? '' }}{{ $applicant->district ? ', '.$applicant->district->name_uz : '' }}</p>
                <p>Pasport ma'lumotlari: {{ $applicant->passport_series }}</p>
                <p>Telefon raqami: {{ $applicant->phone }}</p>
                <p class="bold">Buyurtmachi imzosi:</p>
                <div class="sign-line"></div>
            </td>
        </tr>
    </table>

    <!-- QR kodlar -->
    <table class="qr-table">
        <tr>
            <td>
                <img src="data:image/svg+xml;base64,{{ $qrCode1 }}" width="100" height="100">
            </td>
            <td>
                <img src="data:image/svg+xml;base64,{{ $qrCode2 }}" width="100" height="100">
            </td>
        </tr>
    </table>

    <!-- To'lov maqsadi -->
    <div class="payment-box">
        <p class="bold">Diqqat: Tijorat banklari orqali to'lov amalga oshirilganda To'lov maqsadi quyidagicha bo'lishi talab etiladi</p>
        <br>
        <p>Talaba {{ $applicant->last_name }} {{ $applicant->first_name }} {{ $applicant->middle_name }}ning
            {{ $applicant->jshshir ? 'JSHSHIR:'.$applicant->jshshir.' ' : '' }}{{ \Carbon\Carbon::parse($contract->created_at)->format('Y-m-d') }}
            yildagi {{ $contract->contract_number }} sonli shartnomasiga asosan kontrakt to'lovi uchun</p>
    </div>

    <p class="footer-title">Kontrakt to'lovini xazna ilovasi orqali to'lang</p>

</div>
</body>
</html>
