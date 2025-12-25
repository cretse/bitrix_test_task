<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_CRONTAB", true);

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    // Проверка загрузки модуля
    if (!\Bitrix\Main\Loader::includeModule('it.api')) {
        die("Ошибка: модуль it.api не установлен или не найден!\n");
    }

    $email = $argv[1] ?: 'cretsezxc@gmail.com';
    if (\It\Api\Export\Excel::run($email)) {
        echo "Успешно! Файл отправлен на $email\n";
    } else {
        echo "Ошибка при отправке письма через Bitrix Mail Event.\n";
    }

} catch (\Throwable $e) {
    echo "\n--- КРИТИЧЕСКАЯ ОШИБКА ---\n";
    echo "Сообщение: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . " (строка " . $e->getLine() . ")\n";
    echo "--------------------------\n";
}