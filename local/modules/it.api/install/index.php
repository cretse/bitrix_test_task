<?php
use Bitrix\Main\Localization\Loc;

class it_api extends CModule
{
    public $MODULE_ID = 'it.api';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function __construct()
    {
        $this->MODULE_ID = 'it.api';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_VERSION_DATE = '2025-01-01';
        $this->MODULE_NAME = 'REST API Модуль';
        $this->MODULE_DESCRIPTION = 'Тестовое задание: API для каталога';
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);
    }
}