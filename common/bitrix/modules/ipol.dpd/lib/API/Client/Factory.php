<?php
namespace Ipolh\DPD\API\Client;

use \Ipolh\DPD\API\User;

class Factory
{
	public static function create($wdsl, User $user)
	{
		if (class_exists('\\SoapClient')) {
			return new Soap($wdsl, $user);
		}

		throw new Exception("Soap client is not found", 1);
	}
}