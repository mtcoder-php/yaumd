export const menuItems = [

    {
        path: 'university',
        name: 'Universitet',
        submenu: [
            {
                title: "Universitet haqida",
                path: "about-university",
                innersubmenu: [
                    { title: "Umumiy ma'lumot", path: "/universitet/haqida/umumiy" },
                    { title: "Universitet tuzilmasi", path: "/universitet/haqida/tuzilma" },
                    { title: "Professor & o'qituvchilar", path: "/universitet/haqida/professorlar" },
                    { title: "Universitet nizomi", path: "/universitet/haqida/nizom" },
                    { title: "Sertifikatlar", path: "/universitet/haqida/sertifikatlar" },
                    { title: "O'quv binolari", path: "/universitet/haqida/binolar" },
                    { title: "Talabalar turar joylari", path: "/universitet/haqida/yotoqxona" },
                ]
            },
            {
                title: "Yangiliklar",
                path: "university-news",
                innersubmenu: [
                    { title: "Yangiliklar", path: "/yangiliklar" },
                    { title: "E'lonlar", path: "/elonlar" },
                    { title: "Fotogalereya", path: "/galereya/foto" },
                    { title: "Videogalereya", path: "/galereya/video" },
                ]
            },
            {
                title: "Ilmiy kengash",
                path: "university-council",
                innersubmenu: [
                    { title: "Ilmiy kengash tarkibi", path: "/kengash/tarkib" },
                    { title: "Ilmiy kengash qarorlari", path: "/kengash/qarorlar" },
                ]
            },
            {
                title: "Ilmiy maqolalar",
                path: "scientific-articles",
                innersubmenu: [
                    { title: "Xorijiy jurnallar", path: "/maqolalar/xorijiy" },
                    { title: "Mahalliy jurnallar", path: "/maqolalar/mahalliy" },
                ]
            },
            {
                title: "Normativ hujjatlar",
                path: "normative-documents",
                innersubmenu: [
                    { title: "Ta'lim to'g'risida", path: "/hujjatlar/talim" },
                    { title: "Prezident farmonlari", path: "/hujjatlar/farmonlar" },
                    { title: "Hukumat qarorlari", path: "/hujjatlar/qarorlar" },
                    { title: "Davlat dasturlari", path: "/hujjatlar/dasturlar" },
                    { title: "Davlat ta'lim standarti", path: "/hujjatlar/standart" },
                ]
            },
        ]
    },
    {
        path: 'structure',
        name: 'Tuzilma',
        submenu: [
            {
                title: "Rahbariyat",
                path: "leadership",
                innersubmenu: [
                    { title: "Rektor", path: "/tuzilma/rektor" },
                    { title: "O'quv ishlari prorektori", path: "/tuzilma/prorektor-oquv" },
                    { title: "Yoshlar masalalari prorektori", path: "/tuzilma/prorektor-yoshlar" },
                    { title: "Ilmiy ishlar prorektori", path: "/tuzilma/prorektor-ilmiy" },
                    { title: "Moliya prorektori", path: "/tuzilma/prorektor-moliya" },
                ]
            },
            {
                title: "Kafedralar",
                path: "departments",
                innersubmenu: [
                    { title: "Mumtoz sharq filologiyasi", path: "/kafedralar/sharq" },
                    { title: "Tillar", path: "/kafedralar/tillar" },
                    { title: "Maxsus pedagogika", path: "/kafedralar/pedagogika" },
                    { title: "Maktab va maktabgacha ta'lim", path: "/kafedralar/maktab" },
                    { title: "Umumta'lim fanlari", path: "/kafedralar/umumtalim" },
                ]
            },
            {
                title: "Ilmiy va o'quv bo'limlar",
                path: "academic-departments",
                innersubmenu: [
                    { title: "Ta'lim sifatini boshqarish", path: "/bolimlar/sifat" },
                    { title: "O'quv-uslubiy boshqarma", path: "/bolimlar/uslubiy" },
                    { title: "Magistratura bo'limi", path: "/bolimlar/magistratura" },
                    { title: "Xalqaro aloqalar", path: "/bolimlar/xalqaro" },
                    { title: "Talabalar ilmiy jamiyati", path: "/bolimlar/tij" },
                ]
            },
            {
                title: "Moliyaviy bo'limlar",
                path: "financial-departments",
                innersubmenu: [
                    { title: "Buxgalteriya", path: "/bolimlar/buxgalteriya" },
                    { title: "Marketing", path: "/bolimlar/marketing" },
                    { title: "Rejalashtirish va moliya", path: "/bolimlar/moliya" },
                ]
            },
            {
                title: "Boshqa bo'limlar",
                path: "other-departments",
                innersubmenu: [
                    { title: "Kadrlar bo'limi", path: "/bolimlar/kadrlar" },
                    { title: "Devonxona", path: "/bolimlar/devonxona" },
                    { title: "Murojaatlar bo'limi", path: "/bolimlar/murojaatlar" },
                    { title: "Huquqshunos", path: "/bolimlar/huquqshunos" },
                    { title: "Psixolog", path: "/bolimlar/psixolog" },
                    { title: "Kasaba uyushmasi", path: "/bolimlar/kasaba" },
                ]
            },
            {
                title: "Ta'lim markazlari",
                path: "education-centers",
                innersubmenu: [
                    { title: "Grand maktabi", path: "/markazlar/grand-maktab" },
                    { title: "Grand bog'chasi", path: "/markazlar/grand-bogcha" },
                    { title: "Grand ta'lim markazi", path: "/markazlar/grand-talim" },
                ]
            },
            {
                title: "Kutubxona",
                path: "library",
                innersubmenu: [
                    { title: "Badiiy adabiyotlar", path: "/markazlar/grand-maktab" },
                    { title: "Darsliklar va o‘quv qo‘llanmalar", path: "/markazlar/grand-bogcha" },
                    { title: "Elektron bazadagi resurslar", path: "/markazlar/grand-talim" },
                ]
            },

        ]
    },
    {
        path: 'activities',
        name: 'Faoliyat',
        submenu: [
            {
                title: "O'quv faoliyati",
                path: "academic-activities",
                innersubmenu: [
                    { title: "Malaka talablari", path: "/faoliyat/oquv/malaka" },
                    { title: "O'quv dasturi", path: "/faoliyat/oquv/dastur" },
                    { title: "O'quv rejalari", path: "/faoliyat/oquv/reja" },
                    { title: "Baholash nizomi", path: "/faoliyat/oquv/baholash" },
                    { title: "Mustaqil ta'lim nizomi", path: "/faoliyat/oquv/mustaqil" },
                    { title: "Akademik halollik", path: "/faoliyat/oquv/halollik" },
                ]
            },
            {
                title: "Ilmiy faoliyat",
                path: "scientific-activities",
                innersubmenu: [
                    { title: "Normativ-huquqiy hujjatlar", path: "/faoliyat/ilmiy/hujjatlar" },
                    { title: "Ilmiy-tadqiqot ishlari", path: "/faoliyat/ilmiy/tadqiqot" },
                    { title: "Ilmiy kengashlar", path: "/faoliyat/ilmiy/kengashlar" },
                    { title: "Ilmiy jamiyatlar", path: "/faoliyat/ilmiy/jamiyatlar" },
                    { title: "Aloqa", path: "/faoliyat/ilmiy/aloqa" },
                ]
            },
            {
                title: "Xalqaro faoliyat",
                path: "international-activities",
                innersubmenu: [
                    { title: "Ilmiy hamkorlik", path: "/faoliyat/xalqaro/hamkorlik" },
                ]
            },
            {
                title: "Madaniy faoliyat",
                path: "cultural-activities",
                innersubmenu: [
                    { title: "Madaniy tadbirlar", path: "/faoliyat/madaniy/tadbirlar" },
                    { title: "Talabalar hayoti", path: "/faoliyat/madaniy/hayot" },
                ]
            },
        ]
    },
    {
        path: 'students',
        name: 'Talabalar',
        submenu: [
            {
                title: "Bakalavriat",
                path: "bachelor",
                innersubmenu: [
                    { title: "Yo'riqnoma", path: "/talabalar/bakalavr/yoriqnoma" },
                    { title: "Dars jadvali", path: "/talabalar/bakalavr/jadval" },
                    { title: "Fanlar katalogi", path: "/talabalar/bakalavr/katalog" },
                    { title: "Yotoqxona", path: "/talabalar/bakalavr/yotoqxona" },
                ]
            },
            {
                title: "Magistratura",
                path: "master",
                innersubmenu: [
                    { title: "Dissertatsiya himoyasi", path: "/talabalar/magistr/himoya" },
                    { title: "Dissertatsiya mavzulari", path: "/talabalar/magistr/mavzular" },
                ]
            },

        ]
    },
    {
        path: 'admission',
        name: 'Abituriyent',
        submenu: [
            { title: "Qabul komissiyasi yangiliklari", path: "/qabul/yangiliklar" },
            { title: "Abituriyentlarga eslatma", path: "/qabul/eslatma" },
            { title: "Test sinovi", path: "/qabul/test" },
            { title: "Qabul nizomi", path: "/qabul/nizom" },
            { title: "Qabul kvotasi", path: "/qabul/kvota" },
            { title: "Hujjatlar to'plami", path: "/qabul/hujjatlar" },
            { title: "Qabul monitoringi", path: "/qabul/monitoring" },
            { title: "Ariza topshirish", path: "/qabul/ariza" },
        ]
    },
]
