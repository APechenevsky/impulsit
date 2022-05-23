<?php

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\Application,
	Bitrix\Main\Entity\Base,
	Bitrix\Main\Config\Option,
	Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class impulsit_currency extends CModule
{
	var $MODULE_ID = "impulsit.currency";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $PARTNER_NAME;
	var $PARTNER_URI;

	var $errors;

	function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("IMPULSIT_CURRENCY_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("IMPULSIT_CURRENCY_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("IMPULSIT_CURRENCY_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("IMPULSIT_CURRENCY_PARTNER_URI");
	}

	function InstallDB()
	{
		Loader::includeModule($this->MODULE_ID);

		if(!Application::getConnection()->isTableExists(
			Base::getInstance("\Impulsit\Currency\CurrencyTable")->getDBTableName()
			)
		)
		{
			Base::getInstance("\Impulsit\Currency\CurrencyTable")->createDbTable();
		}
		\Impulsit\Currency\loadCurrency::executeComponent();
	}

	function UnInstallDB()
	{
		Loader::includeModule($this->MODULE_ID);

		Application::getConnection(\Impulsit\Currency\CurrencyTable::getConnectionName())->
			queryExecute("drop table if exists ".Base::getInstance("\Impulsit\Currency\CurrencyTable")->getDBTableName());

		Option::delete($this->MODULE_ID);
	}

	function InstallFiles()
	{
		CopyDirFiles($this->GetPath()."/install/components", $_SERVER["DOCUMENT_ROOT"]."/local/components/", true, true);
		return true;
	}

	function UnIntstallFiles()
	{
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/local/components/impulsit/currency");
		return true;
	}

	function InstallAgent()
	{
		\CAgent::AddAgent( "\\Impulsit\Currency\loadCurrency::executeComponent();", "impulsit.currency", "N", 86400, "", "Y");
	}

	function UnInstallAgent()
	{
		\CAgent::RemoveModuleAgents("impulsit.currency");
	}

	function isVersionD7()
	{
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion("main"), "14.00.00");
	}

	public function GetPath($notDocumentRoot=false)
	{
		if ($notDocumentRoot)
			return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
		else
			return dirname(__DIR__);
	}

	function DoInstall()
	{
		global $APPLICATION;

		if($this->isVersionD7())
		{
			ModuleManager::registerModule($this->MODULE_ID);
			$this->InstallDB();
			$this->InstallFiles();
			$this->InstallAgent();
		}
		else
		{
			$APPLICATION->ThrowException(Loc::getMessage("IMPULSIT_CURRENCY_INSTALL_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("IMPULSIT_CURRENCY_INSTALL_TITLE"), $this->GetPath()."/install/step.php");
	}

	function DoUninstall()
	{

		global $APPLICATION;

		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		if($request["step"] < 2)
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IMPULSIT_CURRENCY_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep1.php");
		elseif ($request["step"] == 2)
		{

			$this->UnIntstallFiles();
			$this->UnInstallAgent();

			if ($request["savedata"] != "Y")
				$this->UnInstallDB();

			ModuleManager::unRegisterModule($this->MODULE_ID);

			$APPLICATION->IncludeAdminFile(Loc::getMessage("IMPULSIT_CURRENCY_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep2.php");

		}
	}


}