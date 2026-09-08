// Umumiy ApexCharts sozlamalari — brend rangi (#0f3460 -> #533483) barcha
// bitta-seriyali ustun/tasma/maydon grafiklarda bir xil ko'rinishni
// ta'minlash uchun ishlatiladi (dataviz: "sequential is the safe default"
// — kategoriyalar bo'yicha miqdorni solishtirishda bitta rang, ko'p rangli
// kategoriyal palitra emas). CRM hisobotlari (Reports.vue) va Dashboard
// sahifalari shu modulni ishlatadi — bir xil naqshni ikki joyda
// takrorlamaslik uchun.

export const BRAND_FROM = '#0f3460'
export const BRAND_TO = '#533483'

// Katta sonlarni ixchamlashtirish (1 284 / 12,9 ming / 4,2 mln) — stat
// kartalarda to'liq raqam emas, o'qilishi oson qisqa shakl ko'rsatiladi;
// to'liq qiymat "title" atributida (hover'da) qoldiriladi.
export const compactNumber = (v) =>
    new Intl.NumberFormat('uz-UZ', { notation: 'compact', maximumFractionDigits: 1 }).format(v || 0)

export const formatAmount = (v) =>
    new Intl.NumberFormat('uz-UZ').format(v || 0) + " so'm"

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

export const columnChartOptions = (rows) => ({
    ...baseChartOptions,
    chart: { ...baseChartOptions.chart, type: 'bar' },
    plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '45%', dataLabels: { position: 'top' } } },
    dataLabels: { ...baseChartOptions.dataLabels, offsetY: -18 },
    xaxis: { categories: rows.map((r) => r.label), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
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
