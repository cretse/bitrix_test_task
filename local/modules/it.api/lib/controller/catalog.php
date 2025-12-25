<?php
namespace It\Api\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\Elements\ElementClothesTable;
use Bitrix\Catalog\PriceTable;

class Catalog extends Controller
{

    public function configureActions()
    {
        return [
            'getCategories' => [
                'prefilters' => [
                    new ActionFilter\Csrf(false), // Отключаем проверку токена
                ],
            ],
            'getProducts' => [
                'prefilters' => [
                    new ActionFilter\Csrf(false),
                ],
            ],
            'getProductDetail' => [
                'prefilters' => [
                    new ActionFilter\Csrf(false),
                ],
            ],
        ];
    }


    public function __construct()
    {
        parent::__construct();
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');
    }

    // Список категорий
    public function getCategoriesAction()
    {
        $iblockId = 2;

        $sections = SectionTable::getList([
            'select' => ['ID', 'NAME', 'PICTURE', 'IBLOCK_SECTION_ID'],
            'filter' => ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            'order' => ['SORT' => 'ASC']
        ])->fetchAll();

        return $this->buildTree($sections);
    }

    private function buildTree(array $elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['IBLOCK_SECTION_ID'] == $parentId) {
                $children = $this->buildTree($elements, $element['ID']);
                $item = [
                    'id' => (int)$element['ID'],
                    'name' => $element['NAME'],
                    'image' => $element['PICTURE'] ? \CFile::GetPath($element['PICTURE']) : null,
                    'url' => '/catalog/section/' . $element['ID'] . '/',
                ];
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }
        return $branch;
    }

    public function getProductsAction($categoryId)
    {
        $products = ElementClothesTable::getList([
            'select' => ['ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL'],
            'filter' => ['IBLOCK_SECTION_ID' => $categoryId, 'ACTIVE' => 'Y'],
            'order' => ['SORT' => 'ASC']
        ])->fetchAll();

        foreach ($products as &$product) {
            $minPrice = PriceTable::getList([
                'select' => ['PRICE'],
                'filter' => ['PRODUCT.PARENT_ID' => $product['ID'], '>PRICE' => 0],
                'order' => ['PRICE' => 'ASC'],
                'limit' => 1
            ])->fetch();

            $product['PRICE_FROM'] = $minPrice ? (float)$minPrice['PRICE'] : 0;
            $product['IMAGE'] = $product['PREVIEW_PICTURE'] ? \CFile::GetPath($product['PREVIEW_PICTURE']) : null;
            $product['URL'] = str_replace('#ELEMENT_ID#', $product['ID'], $product['DETAIL_PAGE_URL']);
            unset($product['PREVIEW_PICTURE'], $product['DETAIL_PAGE_URL']);
        }

        return $products;
    }

    // Деталка товара
    public function getProductDetailAction($productId)
    {
        $product = ElementClothesTable::getList([
            'select' => [
                'ID', 'NAME', 'DETAIL_TEXT', 'DETAIL_PICTURE',
                'BRAND_NAME' => 'BRAND.ELEMENT.NAME',
                'MATERIAL_VALUE' => 'MATERIAL.ITEM.VALUE'
            ],
            'filter' => ['ID' => $productId, 'ACTIVE' => 'Y']
        ])->fetch();

        if (!$product) return ['error' => 'Product not found'];

        $offers = [];
        $skuInfo = \CCatalogSku::GetInfoByProductIBlock(2);
        if ($skuInfo) {
            $offersEntity = \Bitrix\Iblock\Iblock::wakeUp($skuInfo['IBLOCK_ID'])->getEntityDataClass();
            $offersQuery = $offersEntity::getList([
                'select' => ['ID', 'NAME', 'ARTNUMBER_VALUE' => 'ARTNUMBER.VALUE', 'COLOR_NAME' => 'COLOR.ITEM.VALUE', 'SIZE_NAME' => 'SIZE.ITEM.VALUE'],
                'filter' => ['CML2_LINK.VALUE' => $productId, 'ACTIVE' => 'Y']
            ]);

            while ($offer = $offersQuery->fetch()) {
                $offers[] = [
                    'id' => (int)$offer['ID'],
                    'name' => $offer['NAME'],
                    'article' => $offer['ARTNUMBER_VALUE'],
                    'color' => $offer['COLOR_NAME'],
                    'size' => $offer['SIZE_NAME']
                ];
            }
        }

        return [
            'id' => (int)$product['ID'],
            'name' => $product['NAME'],
            'description' => $product['DETAIL_TEXT'],
            'image' => $product['DETAIL_PICTURE'] ? \CFile::GetPath($product['DETAIL_PICTURE']) : null,
            'properties' => [
                'brand' => $product['BRAND_NAME'],
                'material' => $product['MATERIAL_VALUE']
            ],
            'offers' => $offers
        ];
    }
}