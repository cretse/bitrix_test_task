
<?php
use Bitrix\Main\Routing\RoutingConfigurator;
use It\Api\Controller\Catalog;

return function (RoutingConfigurator $routes) {
    $routes->prefix('api/v1')->group(function (RoutingConfigurator $routes) {
        $routes->get('categories', [Catalog::class, 'getCategories']);
        $routes->get('products/{categoryId}', [Catalog::class, 'getProducts']);
        $routes->get('product/{productId}', [Catalog::class, 'getProductDetail']);
    });
};