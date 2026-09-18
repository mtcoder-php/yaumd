// Umumiy ApexCharts sozlamalari — brend rangi (indigo #6366f1 -> #4f46e5)
// barcha bitta-seriyali ustun/tasma/maydon grafiklarda bir xil ko'rinishni
// ta'minlash uchun ishlatiladi (dataviz: "sequential is the safe default"
// — kategoriyalar bo'yicha miqdorni solishtirishda bitta rang, ko'p rangli
// kategoriyal palitra emas). CRM hisobotlari (Reports.vue) va Dashboard
// sahifalari shu modulni ishlatadi — bir xil naqshni ikki joyda
// takrorlamaslik uchun.
//
// MUHIM: bu ikkalasi avval eski to'q-navy palitraga (#0f3460 -> #533483)
// mos edi — admin panel butunlay indigo (--color-brand-*, app.css) rangiga
// o'tkazilganda bu fayl unutilib qolgan, shu sabab grafiklar hamon eski
// navy/siyoh rangda chiqib turardi. Endi shu yerda ham indigo'ga TUZATILDI.
export const BRAND_FROM = '#6366f1'
export const BRAND_TO = '#4f46e5'

// Katta sonlarni ixchamlashtirish (1 284 / 12,9 ming / 4,2 mln) — stat
// kartalarda to'liq raqam emas, o'qilishi oson qisqa shakl ko'rsatiladi;
// to'liq qiymat "title" atributida (hover'da) qoldiriladi.
export const compactNumber = (v) =>
    new Intl.NumberFormat('uz-UZ', { notation: 'compact', maximumFractionDigits: 1 }).format(v || 0)

export const formatAmount = (v) =>
    new Intl.NumberFormat('uz-UZ').format(v || 0) + " so'm"

// Katta summalarni "102.8 mlrd" / "154.6 mln" / "820 ming" ko'rinishida
// ixchamlashtirish — referensdagi (billing.e-edu.uz) stat kartalaridagi
// summa ko'rsatkichlari bilan bir xil format (compactNumber'dan farqli
// o'laroq, bu yerda o'zbekcha to'liq birlik nomlari ishlatiladi).
export const compactAmount = (v) => {
    const n = Number(v) || 0
    if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + ' mlrd'
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + ' mln'
    if (n >= 1_000) return (n / 1_000).toFixed(1) + ' ming'
    return new Intl.NumberFormat('uz-UZ').format(n)
}

// Bitta seriyali grafiklar uchun umumiy ma'lumot shakli — grafik sarlavhasi
// allaqachon nima ko'rsatilganini aytadi, shuning uchun legend keraksiz
// (dataviz: "a single series needs no legend box").
export const barSeries = (rows, name = 'Talabalar') => [{ name, data: rows.map((r) => r.count) }]

export const baseChartOptions = {
    chart: { toolbar: { show: false }, fontFamily: 'inherit', animations: { speed: 400 } },
    colors: [BRAND_FROM],
    fill: {
        type: 'gradient',
        gradient: { shade: 'dark', type: 'horizontal', gradientToColors: [BRAND_TO], stops: [0, 100] },
    },
    grid: { borderColor: '#eef0f2', strokeDashArray: 3, yaxis: { lines: { show: true } } },
    dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600, colors: ['#374151'] } },
    tooltip: { theme: 'light' },
    legend: { show: false },
}

export const horizontalBarOptions = (rows) => ({
    ...baseChartOptions,
    chart: { ...baseChartOptions.chart, type: 'bar' },
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%', dataLabels: { position: 'top' } } },
    dataLabels: { ...baseChartOptions.dataLabels, offsetX: 18 },
    xaxis: { categories: rows.map((r) => r.label), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#4b5563', fontSize: '12px' } } },
})

export const columnChartOptions = (rows, opts = {}) => ({
    ...baseChartOptions,
    chart: { ...baseChartOptions.chart, type: 'bar' },
    plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '45%', dataLabels: { position: 'top' } } },
    dataLabels: { ...baseChartOptions.dataLabels, offsetY: -18 },
    // Ko'p kategoriyali (masalan barcha viloyatlar) ustun grafiklarda
    // yorliqlar bir-biriga tiqilib qolmasligi uchun burish kerak bo'ladi —
    // shuning uchun opsional rotate/trim sozlamasi qo'shildi.
    xaxis: {
        categories: rows.map((r) => r.label),
        labels: {
            style: { colors: '#9ca3af', fontSize: '11px' },
            rotate: opts.rotateLabels ? -45 : 0,
            trim: !!opts.rotateLabels,
        },
    },
    yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
})

// Ulush (donut) diagrammasi — bir nechta kategoriya bo'yicha nisbiy
// ulushni ko'rsatish uchun (masalan "Ta'lim shakli", "Bakalavr/Magistr").
// Kategoriyal palitra shu yerda cheklangan (2-3 element) bo'lgani uchun
// ishlatilishi xavfsiz (dataviz: "categorical only when the categories
// themselves are the subject").
export const donutChartOptions = (labels, colors) => ({
    chart: { type: 'donut', toolbar: { show: false }, fontFamily: 'inherit', animations: { speed: 400 } },
    labels,
    colors,
    stroke: { width: 2, colors: ['#ffffff'] },
    dataLabels: { enabled: false },
    legend: {
        show: true,
        position: 'bottom',
        fontSize: '12px',
        labels: { colors: '#4b5563' },
        markers: { size: 8, offsetX: -2 },
        itemMargin: { horizontal: 8, vertical: 4 },
    },
    plotOptions: { pie: { donut: { size: '68%' } } },
    tooltip: { theme: 'light' },
})

// Vaqt bo'yicha tendensiya (bitta seriya) — maydon (area) grafik. To'ldirish
// ~10% xiralik (dataviz: "area fill... a wash, never a saturated block"),
// chiziq 2px, nuqta belgilar >=8px diametrda — barchasi marks-and-anatomy
// spetsifikatsiyasiga mos.
export const areaChartOptions = (categories) => ({
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'inherit', animations: { speed: 400 } },
    colors: [BRAND_FROM],
    stroke: { width: 2, curve: 'smooth' },
    fill: { type: 'solid', opacity: 0.12 },
    markers: { size: 5, colors: [BRAND_FROM], strokeColors: '#ffffff', strokeWidth: 2, hover: { size: 6 } },
    grid: { borderColor: '#eef0f2', strokeDashArray: 3 },
    dataLabels: { enabled: false },
    tooltip: { theme: 'light' },
    legend: { show: false },
    xaxis: { categories, labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } }, min: 0, forceNiceScale: true },
})

// Ikki seriyali tendensiya grafigi (masalan "tasdiqlangan" vs "bekor
// qilingan") — referensdagi "Kunlik shartnomalar" grafigiga mos: indigo
// (tasdiqlangan) to'ldirilgan maydon + qizil (bekor qilingan) yupqa chiziq.
// Ikki seriya bo'lgani uchun (dataviz: "a legend is required once a second
// series appears") — lekin standart Apex legendasi o'rniga sahifada
// qo'lda chizilgan mini-legenda ishlatiladi (referensdagi kabi), shuning
// uchun bu yerda ham legend o'chirilgan.
export const dualAreaChartOptions = (categories) => ({
    // MUHIM: bu yerda avval chart.type 'line' edi — ApexCharts'da "line"
    // turi array shaklidagi fill (fill.type: [...]) bilan birga ishlatilsa,
    // chiziq/maydon (stroke+fill) umuman chizilmay qoladi, faqat alohida
    // qatlamda chiziladigan markerlar (nuqtalar) to'g'ri joyida ko'rinib
    // qoladi — grafik "nuqtalar bor, chiziq yo'q" bo'lib chiqishining sababi
    // shu edi. "area" turi esa gradient/solid fill'ni ham, chiziqni ham
    // to'g'ri qo'llab-quvvatlaydi, shuning uchun TUZATILDI.
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'inherit', animations: { speed: 400 } },
    colors: [BRAND_FROM, '#ef4444'],
    stroke: { width: [2, 1.5], curve: 'smooth' },
    fill: {
        type: ['gradient', 'solid'],
        gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02, stops: [0, 100] },
        // Ikkinchi seriya ("Bekor qilingan") pastida to'ldirilgan maydon
        // emas, faqat yupqa chiziq ko'rinishi kerak (referensdagi kabi) —
        // shuning uchun uning fon to'ldirilishi opacity: 0 bilan yashiriladi.
        opacity: [1, 0],
    },
    markers: { size: [4, 0], strokeColors: '#ffffff', strokeWidth: 2, hover: { size: 5 } },
    grid: { borderColor: '#eef0f2', strokeDashArray: 3 },
    dataLabels: { enabled: false },
    tooltip: { theme: 'light' },
    legend: { show: false },
    xaxis: { categories, labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } }, min: 0, forceNiceScale: true },
})
