<?php

/**
 * Vaqtinchalik diagnostika — rasm bilan saqlaganda "majburiy maydon"
 * xatoliklari chiqishi PHP'ning so'rov hajmi cheklovi (post_max_size)
 * bilan bog'liqmi, shuni tekshiradi. Agar rasm + boshqa maydonlarning
 * umumiy hajmi 'post_max_size'dan katta bo'lsa, PHP butun $_POST
 * massivini jimgina bo'shatib yuboradi — shuning uchun barcha
 * maydonlar serverga "bo'sh" bo'lib yetib boradi.
 *
 * Loyihaning ILDIZIDA ishga tushiring: `php check_php_upload_limits.php`
 * Natijani menga yuboring, keyin faylni o'chirib tashlashingiz mumkin.
 */

echo "=== PHP-CLI sozlamalari (php artisan serve shu qiymatlarni ishlatadi) ===\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "php.ini fayli: " . (php_ini_loaded_file() ?: '(topilmadi)') . "\n";

echo "\nDIQQAT: Agar Apache/Nginx + PHP-FPM orqali ishlatayotgan bo'lsangiz,\n";
echo "yuqoridagi qiymatlar boshqacha (odatda kichikroq) bo'lishi mumkin —\n";
echo "u holda shu buyruqni brauzer/veb-server ishlatadigan PHP bilan ham\n";
echo "solishtirish kerak (masalan phpinfo() orqali).\n";
