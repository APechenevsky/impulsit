<?
use \Bitrix\Main,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Type,
	\Impulsit\Currency\CurrencyTable;

class currencyGetListExpression extends CBitrixComponent
{

	// Проверяем подключение необходимых модулей
	protected function checkModules()
	{
		if (!Main\Loader::IncludeModule("impulsit.currency"))
			throw new Main\LoaderException(Loc::getMessage("IMPULSIT_CURRENCY_MODULE_NOT_INSTALLED"));
	}

	//добавление валют за сегодня
	public function executeComponent()
	{
		//$this->includeComponentLang("class.php");
		$arParams = $this->arParams;
		$this->checkModules();

		$nav = new \Bitrix\Main\UI\PageNavigation("nav");
		$nav->allowAllRecords(true)
		   ->setPageSize($arParams["COUNT"])
		   ->initFromUri();

		$result = CurrencyTable::getList(array(
			"select" => array("ID", "CODE", "DATE", "COURSE"), //Имена полей, которые нужно получить
			"filter" => array(), //Фильтр для выборки
			"order" => array($arParams["SORT_BY"] => $arParams["SORT_ORDER"]), //Параметры сортировки
			"count_total" => true,
			"offset" => $nav->getOffset(), //смещение для limit
			"limit" => $nav->getLimit(), //колличество записей
		));

		$this->arResult["NAV"] = $nav->setRecordCount($result->getCount());
		$this->arResult[] = $result->fetchAll();

		$this->includeComponentTemplate();
	}
}