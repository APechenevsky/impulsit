<?php

namespace Impulsit\Currency;

use \Bitrix\Main\Entity;
use \Bitrix\Main\type;

class CurrencyTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return "impulsit_currency";
	}

	public static function getUfId()
	{
		return "IMPULSIT_CURRENCY";
	}

	public static function getMap()
	{
		return array(
			//ID
			new Entity\IntegerField("ID", array(
				"primary" => true,
				"autocomplete" => true
			)),
			//Код валюты
			new Entity\StringField("CODE", array(
				"required" => true,
			)),
			//Дата
			new Entity\DatetimeField("DATE", array(
				"required" => true,
			)),
			//Курс
			new Entity\FloatField("COURSE", array(
				"required" => true,
			)),
		);
	}

}