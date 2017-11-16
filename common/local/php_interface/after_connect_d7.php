<?php

use Bitrix\Main\Application;

$connection = Application::getConnection();
$connection->queryExecute("SET NAMES 'utf8'");
$connection->queryExecute('SET collation_connection = "utf8_unicode_ci"');

/**
 * Внимание! У нас MySQL 5.6 и в нём sql_mode не может принимать пустое значение, как бы Битрикс не просил об этом.
 */
$connection->queryExecute('SET sql_mode = "NO_ENGINE_SUBSTITUTION"');

/*
 * Внимание! Тут мы ставим уровень изоляции на сессию, а монитор производительности БД Битрикса читает глобальную
 * настройку и он будет продолжать на неё ругаться
 */
$connection->queryExecute('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
