<?php

namespace Impulsit\Currency;

use \Bitrix\Main,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Type,
	\Impulsit\Currency\CurrencyTable;

class loadCurrency extends \CBitrixComponent
{

	// Проверяем подключение необходимых модулей
	protected function checkModules()
	{
		if (!Main\Loader::IncludeModule("impulsit.currency"))
			throw new Main\LoaderException(Loc::getMessage("IMPULSIT_CURRENCY_MODULE_NOT_INSTALLED"));
	}

	//добавление валют за сегодня
	function var1()
	{
		$xml = simplexml_load_file("http://www.cbr.ru/scripts/XML_daily.asp?date_req=".date('d/m/Y'));
		$date = new Type\DateTime(strval($xml["Date"]));

		foreach($xml as $val)
		{
			$result = CurrencyTable::add(array(
				"CODE" => strval($val->CharCode),
				"DATE" => $date,
				"COURSE" => $val->Value/$val->Nominal,
			));
		}

		return $result;
	}

	public function executeComponent()
	{
		$load = new loadCurrency();
		$load->includeComponentLang("loadCurrency.php");
		$load->checkModules();

		$result = $load->var1();

		if ($result->isSuccess())
		{
			$id = $result->getId();
			$load->arResult="Запись добавлена с id: ".$id;
		}
		else
		{
			$error = $result->getErrorMessages();
			$load->arResult = "Произошла ошибка при добавлении: <pre>".var_export($error, true)."</pre>";
		}

		$load->includeComponentTemplate();
	}

}