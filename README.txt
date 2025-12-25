Тестовое задание Bitrix REST API + Excel Exp.



Установка
1. Скопировать содержимое папки local в корень вашего проекта
2. Скопировать export_task.php и openapi.yaml в корень проекта
3. Установить модуль it.api в админке Marketplace - установленные решения



В  bitrix/.settings.php добавить секцию routing:
'routing' => ['value' => ['config' => ['api.php']]],


В .htaccess(главный, тот что в корне сайта) добавить правило перед обработкой urlrewrite:
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ /bitrix/routing_index.php [L]


Эндпоинты:
GET /api/v1/categories — список категорий
GET /api/v1/products/{categoryId} — товары в категории
GET /api/v1/product/{productId} — детальная информация.


Запуск скрипта выгрузки:
php export_task.php pochta@gmail.com

Результат в /upload/export_products.xls
Почтовое событие - PRODUCT_EXPORT_READY.


