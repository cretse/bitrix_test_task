<?php
namespace It\Api\Export;

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\IO\Directory;

class Excel
{
    public static function run($email)
    {
        try {
            Loader::includeModule('iblock');
            Loader::includeModule('catalog');

            $iblockId = 2; // ID Одежды

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/';
            if (!Directory::isDirectoryExists($uploadDir)) {
                Directory::createDirectory($uploadDir);
            }

            $res = ElementTable::getList([
                'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID'],
                'filter' => [
                    'IBLOCK_ID' => $iblockId,
                    'ACTIVE' => 'Y'
                ],
                'limit' => 100
            ]);

            $products = $res->fetchAll();

            if (empty($products)) {
                throw new \Exception("В инфоблоке ID $iblockId нет активных товаров.");
            }

            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            $html .= '<table border="1">
                <tr>
                    <th style="font-weight:bold;">ID</th>
                    <th style="font-weight:bold;">Название</th>
                    <th style="font-weight:bold;">Категория</th>
                </tr>';

            foreach ($products as $p) {
                $sectionName = '';
                if ($p['IBLOCK_SECTION_ID']) {
                    $section = \Bitrix\Iblock\SectionTable::getById($p['IBLOCK_SECTION_ID'])->fetch();
                    $sectionName = $section['NAME'];
                }

                $html .= "<tr>
                    <td>{$p['ID']}</td>
                    <td>" . htmlspecialcharsbx($p['NAME']) . "</td>
                    <td>" . htmlspecialcharsbx($sectionName) . "</td>
                </tr>";
            }
            $html .= '</table>';

            $filePath = $uploadDir . 'export_products.xls';

            if (file_put_contents($filePath, $html) === false) {
                throw new \Exception("Ошибка записи файла. Проверь права на папку /upload/");
            }

            $siteId = "s1";

            $mailId = \CEvent::Send(
                "PRODUCT_EXPORT_READY",
                $siteId,
                ["EMAIL_TO" => $email],
                "Y",
                "",
                [$filePath]
            );

            if (!$mailId) {
                throw new \Exception("Битрикс не смог создать почтовое событие. Проверь Почтовый шаблон.");
            }

            return true;

        } catch (\Exception $e) {
            echo "<div style='color:red; background:#fff; padding:10px; border:2px solid red;'>";
            echo "<b>Ошибка:</b> " . $e->getMessage();
            echo "</div>";
            return false;
        }
    }
}