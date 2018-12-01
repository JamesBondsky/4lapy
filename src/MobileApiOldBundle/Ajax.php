<?

namespace FourPaws\MobileApiOldBundle;

class Ajax
{
	// возвращает информацию о корзине текущего пользователя
	static function GetBasket($iFUserId = 0, $iUserID = 0)
	{
		$iSummBonus = 0;
		\CModule::IncludeModule("sale");

		$arResult = array(
			"basket" => array(
				"items" => array(),
				"summ" => 0,
				"discountValue" => 0,
				"count" => 0
			),
			"delivery" => array(),
		);

		$arFilter = array(
			"FUSER_ID" => ($iFUserId > 0) ? $iFUserId : \CSaleBasket::GetBasketUserID(),
			"LID" => SITE_ID,
			"ORDER_ID" => "NULL",
			"CAN_BUY" => "Y"
		);
		// if($USER_ID)
		// {
		// 	unset($arFilter["FUSER_ID"]);
		// 	$arFilter['USER_ID'] = $USER_ID;
		// }
		// получаем корзину
		$res = \CSaleBasket::GetList(
			array("ID" => "ASC"),
			$arFilter,
			false,
			false,
			array(
				"ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY",
				"PRICE", "WEIGHT", "DETAIL_PAGE_URL", "NOTES", "CURRENCY", "VAT_RATE", "CATALOG_XML_ID",
				"PRODUCT_XML_ID", "SUBSCRIBE", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "TYPE", "SET_PARENT_ID","RENEWAL"
			)
		);

		while($arRes = $res->Fetch())
		{
			$arResult_["ITEMS"]["AnDelCanBuy"][] = $arRes;
			// заполняем ответ
			$arResult["basket"]["items"][] = array(
				"discount" => array(
					"percent" => round($arRes["DISCOUNT_VALUE"], 1),
					"value" => round($arRes["DISCOUNT_PRICE"], 2),
				),
				"price" => round($arRes["PRICE"], 2),
				"product_id" => $arRes["PRODUCT_ID"],
				"quantity" => intval($arRes["QUANTITY"])
			);

			$arResult["basket"]["summ"] += round($arRes["PRICE"] * $arRes["QUANTITY"], 2);
			$arResult["basket"]["discountValue"] += round($arRes["DISCOUNT_VALUE"] * $arRes["QUANTITY"], 2);
			$arResult["basket"]["count"] += intval($arRes["QUANTITY"]);

			$arBonus = array(
				"TYPE_OF_BONUS"=>$arRes["CATALOG"]["PROPERTIES"]['TYPE_OF_BONUS'],
				"PRICE_BASE"=>$arRes["PRICE"] + $arRes["DISCOUNT_PRICE"],
				"ACTIONS_SHILDS"=>$arRes["CATALOG"]["PROPERTIES"]["ACTIONS_SHILDS"],
				"DISCOUNT_PRICE"=>$arRes["PRICE"],
				"PRODUCT_ID"=>$arRes["PRODUCT_ID"]
			);

			$iSummBonus += MyCSaleBasket::GetUserBonuses($arBonus) * $arRes["QUANTITY"];

		}
		$arResult["basket"]["bonuses"] = (int)$iSummBonus;
		//Просчитываем цены и скидки исходя из скидок в админке
		$allSum = $arResult["basket"]["summ"];

		$arOrder = array(
			'SITE_ID' => SITE_ID,
			'USER_ID' => ($iUserID)?:$GLOBALS["USER"]->GetID(),
			'ORDER_PRICE' => $allSum,
			// 'ORDER_WEIGHT' => $allWeight,
			'BASKET_ITEMS' => $arResult_["ITEMS"]["AnDelCanBuy"]
		);
		// echo "<pre>";print_r($arOrder);echo "</pre>";
		$arOptions = array(
			'COUNT_DISCOUNT_4_ALL_QUANTITY' => 'N',
		);

		$arErrors = array();

		\CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

		$arResult["basket"]["items"] = array();
		$arResult["basket"]["summ"] = 0;
		$arResult["basket"]["discountValue"] = 0;

		foreach ($arOrder['BASKET_ITEMS'] as $keyOrder => $valueOrder)
		{
			// echo "<pre>";print_r($valueOrder);echo "</pre>";
			// $arResult["basket"]["items"][] = array(
			// 	"discount" => array(
			// 		"percent" => round($valueOrder["DISCOUNT_PRICE_PERCENT"], 1),
			// 		"value" => round($valueOrder["DISCOUNT_PRICE"], 2),
			// 	),
			// 	"price" => round($valueOrder["PRICE"], 2),
			// 	"product_id" => $valueOrder["PRODUCT_ID"],
			// 	"quantity" => intval($valueOrder["QUANTITY"]),
			// 	"summ" => round($valueOrder["PRICE"] * $valueOrder["QUANTITY"], 2),
			// 	"name"=>$valueOrder['NAME']
			// );
			// $arResult["basket"]["summ"] += round($valueOrder["PRICE"] * $valueOrder["QUANTITY"], 2);
			// $arResult["basket"]["discountValue"] += round($valueOrder["DISCOUNT_VALUE"] * $valueOrder["QUANTITY"], 2);

			$arResult["basket"]["items"][] = array(
				"discount" => array(
					"percent" => round($valueOrder["DISCOUNT_PRICE_PERCENT"], 1),
					"value" => round($valueOrder["DISCOUNT_PRICE"], 2),
				),
				"price" => round($valueOrder["PRICE"], 2, PHP_ROUND_HALF_DOWN),
				"product_id" => $valueOrder["PRODUCT_ID"],
				"quantity" => intval($valueOrder["QUANTITY"]),
				"summ" => round($valueOrder["PRICE"], 2, PHP_ROUND_HALF_DOWN) * $valueOrder["QUANTITY"],
				"name"=>$valueOrder['NAME']
			);
			$arResult["basket"]["summ"] += round($valueOrder["PRICE"], 2, PHP_ROUND_HALF_DOWN) * $valueOrder["QUANTITY"];
			$arResult["basket"]["discountValue"] += round($valueOrder["DISCOUNT_VALUE"] * $valueOrder["QUANTITY"], 2);
		}
		//////////////////////////////////////////////////////

		// //костыль для Ярославля
		// $description = "courier";
		// if(GeoCatalog::GetCity() == 'Ярославль')
		// 	$description = "courier_yar";
		// //!костыль для Ярославля

		$arTownInfo = GeoCatalog::GetCityFull();

		// получаем доставку курьером
		$arDelivery = \CSaleDelivery::GetList(
			array('SORT'=>'ASC'),
			array(
				"LID" => SITE_ID,
				"ACTIVE" => "Y",
				// "DESCRIPTION" => "courier",
				// "DESCRIPTION" => $description,
				"LOCATION" => $arTownInfo['town_id'],
				"<ORDER_PRICE_FROM" => $arResult["basket"]["summ"],
				">ORDER_PRICE_TO" => $arResult["basket"]["summ"],
				// ">PRICE" => '0',
			),
			false,
			false,
			array(
				"NAME",
				"ORDER_PRICE_FROM",
				"ORDER_PRICE_TO",
				"PRICE"
			)
		)->Fetch();

		if($arDelivery)
		{
			$arResult["delivery"]["name"] = $arDelivery["NAME"];
			$arResult["delivery"]["orderPriceMin"] = round($arDelivery["ORDER_PRICE_FROM"], 2);
			$arResult["delivery"]["orderPriceMax"] = round($arDelivery["ORDER_PRICE_TO"], 2);
			$arResult["delivery"]["value"] = round($arDelivery["PRICE"], 2);
		}

		$arResult["basket"]["deliveryValue"] = (
			(
				$arDelivery and
				(
					($arResult["basket"]["summ"] >= $arDelivery["ORDER_PRICE_FROM"]) and
					($arResult["basket"]["summ"] < $arDelivery["ORDER_PRICE_TO"])
				)
			)
			? round($arDelivery["PRICE"], 2)
			: 0
		);

		return $arResult;
	}

	// добавляет список продуктов в корзину текущего пользователя
	static function AddProductsToBasket($arFields = array(), $requestId = 0, $USER_ID = 0)
	{
		$arProducts = array();

		//а вот тут мы сделаем проверку на город и количество товара,
		//если количество меньше нуля и город Ярославль
		//запишем в базу
		//правда еще не ясен формат

		// преобразуем массив продуктов в нужный нам вид
		foreach($arFields as $value)
		{
			$id = filter_var($value["productId"], FILTER_SANITIZE_NUMBER_INT);
			$quantity = filter_var($value["quantity"], FILTER_SANITIZE_NUMBER_INT);

			if(($quantity < 0) AND ($city = GeoCatalog::GetCity()))
			{
				\CModule::IncludeModule("iblock");
				$objElement = new \CIBlockElement;

				$arFields = array(
					"IBLOCK_ID" => (CIBlockTools::GetIBlockId("not_available_goods") ?: 0),
					"NAME" => $id,
					"ACTIVE" => 'N',
					"CODE" => $city,
					"SORT" => $quantity,
				);

				$result = $objElement->Add($arFields);

			}

			if($id > 0)
			{
				$arProducts[$id] = $quantity;
			}
		}

		// добавляем в корзину
		$arResult = fAddProductsToBasket($arProducts, $USER_ID);

		$arResult += static::GetBasket($USER_ID);

		$arResult["requestId"] = intval(filter_var($requestId, FILTER_SANITIZE_NUMBER_INT));

		return $arResult;
	}

	// активируем купон на скидку
	static function AddCoupon($coupon, $requestId = 0)
	{
		\CModule::IncludeModule("catalog");
		\CModule::IncludeModule("sale");

		$arResult["result"] = false;

		if ($coupon)
		{
			if (CCatalogDiscountCoupon::IsExistCoupon($coupon))
			{
				CCatalogDiscountCoupon::ClearCoupon();

				$arResult["result"] = CCatalogDiscountCoupon::SetCoupon($coupon);
			}
			else
			{
				$arResult["result"] = false;
			}
		}
		else
		{
			if (CCatalogDiscountCoupon::GetCoupons())
			{
				CCatalogDiscountCoupon::ClearCoupon();
				$arResult["result"] = true;
			}
			else
			{
				$arResult["result"] = false;
			}
		}

		// CCatalogDiscountCoupon::ClearCoupon();
		// CCatalogDiscountCoupon::IsExistCoupon($coupon);
		// CCatalogDiscountCoupon::SetCoupon($coupon);

		if ($arResult["result"])
		{
			$res = CSaleBasket::GetList(
				array(
					"ID" => "ASC"
				),
				array(
					"FUSER_ID" => CSaleBasket::GetBasketUserID(),
					"ORDER_ID" => "NULL",
				),
				false,
				false,
				array(
					"ID",
					"CALLBACK_FUNC",
					"MODULE",
					"PRODUCT_ID",
					"QUANTITY",
					"RENEWAL",
					"PRODUCT_PROVIDER_CLASS"
				)
			);

			while($arRes = $res->Fetch())
			{
				CSaleBasket::UpdatePrice(
					$arRes["ID"],
					$arRes["CALLBACK_FUNC"],
					$arRes["MODULE"],
					$arRes["PRODUCT_ID"],
					$arRes["QUANTITY"],
					$arRes["RENEWAL"],
					$arRes["PRODUCT_PROVIDER_CLASS"]
				);
			}

			$arResult += static::GetBasket();
		}

		$arResult["requestId"] = intval(filter_var($requestId, FILTER_SANITIZE_NUMBER_INT));

		return $arResult;
	}

	// активируем купон на скидку для МП
	static function AddCouponAPI($coupon, $iFUserId = 0, $iUserID = 0)
	{
		// echo "<pre>";print_r(array($coupon, $iFUserId, $iUserID));echo "</pre>"."\r\n";
		\CModule::IncludeModule("catalog");
		\CModule::IncludeModule("sale");

		$arResult["result"] = false;

		$_SESSION['SALE_USER_ID'] = $iFUserId;

		if ($coupon)
		{
			if (CCatalogDiscountCoupon::IsExistCoupon($coupon))
			{
				CCatalogDiscountCoupon::ClearCoupon();
				// unset($_SESSION['CATALOG_USER_COUPONS']);

				$arResult["result"] = CCatalogDiscountCoupon::SetCoupon($coupon);
				// $_SESSION['CATALOG_USER_COUPONS'][] = $coupon;
			}
			else
			{
				// unset($_SESSION['CATALOG_USER_COUPONS']);
				$arResult["result"] = false;
			}
		}
		else
		{
			unset($_SESSION['CATALOG_USER_COUPONS']);
			if (CCatalogDiscountCoupon::GetCoupons())
			{
				CCatalogDiscountCoupon::ClearCoupon();
				// unset($_SESSION['CATALOG_USER_COUPONS']);
				$arResult["result"] = true;
			}
			else
			{
				$arResult["result"] = false;
			}
		}

		if ($arResult["result"])
		{
			$res = CSaleBasket::GetList(
				array(
					"ID" => "ASC"
				),
				array(
					"FUSER_ID" => ($iFUserId > 0) ? $iFUserId : CSaleBasket::GetBasketUserID(),
					"ORDER_ID" => "NULL",
				),
				false,
				false,
				array(
					"ID",
					"CALLBACK_FUNC",
					"MODULE",
					"PRODUCT_ID",
					"QUANTITY",
					"RENEWAL",
					"PRODUCT_PROVIDER_CLASS"
				)
			);

			while($arRes = $res->Fetch())
			{
				CSaleBasket::UpdatePrice(
					$arRes["ID"],
					$arRes["CALLBACK_FUNC"],
					$arRes["MODULE"],
					$arRes["PRODUCT_ID"],
					$arRes["QUANTITY"],
					$arRes["RENEWAL"],
					$arRes["PRODUCT_PROVIDER_CLASS"]
				);
			}

		}

		return $arResult;
	}

	// проверка промокода
	static function CheckPromoCode($arFields)
	{
		$arResult["result"] = false;

		$arRequest = filter_var_array(
			$arFields,
			array(
				"object" => FILTER_SANITIZE_STRING,
				"promoCode" => FILTER_SANITIZE_STRING
			)
		);

		if($arRequest["object"] == "stronghold")
		{
			if($arRequest["promoCode"])
			{
				\CModule::IncludeModule("iblock");

				$arRes = CIBlockElement::GetList(
					array(),
					array(
						"IBLOCK_ID" => CIBlockTools::GetIBlockId("promotionalCodes"),
						"=NAME" => $arRequest["promoCode"]
					),
					false,
					false,
					array(
						"ID",
						"IBLOCK_ID",
						"PROPERTY_user"
					)
				)->Fetch();

				if($arRes)
				{
					if($arRes["PROPERTY_USER_VALUE"])
					{
						$arResult["error"] = "is used";
					}
					else
					{
						$arResult["result"] = true;
					}
				}
				else
				{
					$arResult["error"] = "not found";
				}
			}
			else
			{
				$arResult["error"] = "not found";
			}
		}

		return $arResult;
	}

	// проверка емайла на занятость (наличие юзера с данным емайлом)
	static function CheckEmail($email)
	{
		$arResult["result"] = false;

		if(filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			if(CUser::GetByLogin($email)->Fetch())
			{
				$arResult["error"] = "is used";
			}
			else
			{
				$arResult["result"] = true;
			}
		}
		else
		{
			$arResult["error"] = "not valid";
		}

		return $arResult;
	}

	// проверка емайла на занятость (наличие юзера с данным емайлом)
	static function CheckPhone($phone)
	{
		$arResult["result"] = false;

		$first = substr($phone, "0",1);
		if(!preg_match("/^[0-9]{10,10}+$/", $phone) or $first != 9)
		{
			$arResult["error"] = "not valid";
		}
		else
		{
			//проверяем учетные данные на уникальность мыла и телефона, проверяем всех, кроме быстрых заказов
			\Bitrix\Main\Loader::includeModule('bxmod.auth');
			$oBxmodAuth = new \BxmodAuth();
			if ($oBxmodAuth->GetUserByPhone($phone)) {
				$arResult["error"] = "is used";
			}
		}

		if(empty($arResult["error"]))
		{
			$arResult["result"] = true;
		}

		return $arResult;
	}

	// регистрация пользователя
	static function UserRegister($arRequest)
	{
		$arResult["result"] = false;
		$arFields = array();

		$arRequest = filter_var_array(
			$arRequest,
			array(
				"login" => FILTER_SANITIZE_EMAIL,
				"name" => FILTER_SANITIZE_STRING,
				"lastName" => FILTER_SANITIZE_STRING,
				"secondName" => FILTER_SANITIZE_STRING,
				"email" => FILTER_SANITIZE_EMAIL,
				"password" => FILTER_SANITIZE_STRING,
				"confirm_password" => FILTER_SANITIZE_STRING,
				"personalPhone" => FILTER_SANITIZE_NUMBER_INT,
				// "card" => FILTER_SANITIZE_NUMBER_INT,
			)
		);

		$arFields["LOGIN"] = (is_null($arRequest["login"]) ? $arRequest["email"] : $arRequest["login"]);

		if(in_array("name", $_REQUEST["required"]) and !$arRequest["name"])
		{
			$arResult["error"]["name"] = "Не заполнено поле \"Имя\"";
		}
		else
		{
			$arFields["NAME"] = $arRequest["name"];
		}

		if(in_array("lastName", $_REQUEST["required"]) and !$arRequest["lastName"])
		{
			$arResult["error"]["lastName"] = "Не заполнено поле \"Фамилия\"";
		}
		else
		{
			$arFields["LAST_NAME"] = $arRequest["lastName"];
		}

		if(in_array("secondName", $_REQUEST["required"]) and !$arRequest["secondName"])
		{
			$arResult["error"]["secondName"] = "Не заполнено поле \"Отчество\"";
		}
		else
		{
			$arFields["SECOND_NAME"] = $arRequest["secondName"];
		}

		$result = static::CheckEmail($arRequest["email"]);

		if($result["result"])
		{
			$arFields["EMAIL"] = $arRequest["email"];
		}
		else
		{
			$arResult["error"]["email"] = $result["error"];
		}

		if(is_null($arRequest["password"]) and !in_array("password", $_REQUEST["required"]))
		{
			$arFields["PASSWORD"] = randString(10);
			// отметим, что пользователь регистрировался как будто бы из корзины (без указания пароля)
			// (влияет на шаблон письма, отправленного ES)
			$_SESSION["USER_REGISTER_PAGE"] = "basket";
		}
		else
		{
			if(!$arRequest["password"] or strlen($arRequest["password"]) < 6)
			{
				$arResult["error"]["password"] = "Пароль должен быть не короче 6 символов";
			}
			else
			{
				$arFields["PASSWORD"] = $arRequest["password"];
			}
		}

		if(is_null($arRequest["confirm_password"]) and !in_array("confirm_password", $_REQUEST["required"]))
		{
			$arFields["CONFIRM_PASSWORD"] = $arFields["PASSWORD"];
		}
		else
		{
			if($arRequest["password"] !== $arRequest["confirm_password"])
			{
				$arResult["error"]["confirm_password"] = "Не верное подтверждение пароля";
			}
			else
			{
				$arFields["CONFIRM_PASSWORD"] = $arFields["PASSWORD"];
			}
		}

		if(in_array("personalPhone", $_REQUEST["required"]) and (strlen($arRequest["personalPhone"]) != 10))
		{
			$arResult["error"]["personalPhone"] = "Не верный номер телефона";
		}
		else
		{
			$arFields["PERSONAL_PHONE"] = $arRequest["personalPhone"];
		}

		if(!isset($arResult["error"]))
		{
			$objUser = new CUser;

			if($userId = $objUser->Add($arFields))
			{
				$arResult["result"] = true;
				$objUser->Authorize($userId);
			}
		}

		return $arResult;
	}

	// то же что и userRegister только проверка рекапчи есть
	static function userRegisterAndRCapcha($arRequest)
	{
		$result = array();
		$t_flag = true;

		if ( !empty($arRequest['g-recaptcha-response']) )
		{
			$oCurl = new \ReCaptcha\RequestMethod\CurlPost();
			$recaptcha = new \ReCaptcha\ReCaptcha(RE_SEC_KEY, $oCurl); 
			$resp = $recaptcha->verify($arRequest['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			if (!$resp->isSuccess())
			{
				foreach ($resp->getErrorCodes() as $code) $t_flag = false;
			}
		} else {
			$t_flag = false;
		}

		$result = array();
		if ($t_flag === true) 
		{ 
			$result = static::UserRegister($arRequest);
		} else {
			$result['error']['rcapcha'] = "рекапча Проверка не пройдена";
			$result['result'] = false;	
		}

		return $result; 
	}

	// проверка карты
	static function CheckCard($cardNumber, $ml = NULL)
	{

		$cardNumber = filter_var($cardNumber, FILTER_SANITIZE_NUMBER_INT);
		$arResult["result"] = false;

		if(is_null($cardNumber) or (strlen($cardNumber) != 13))
		{
			$arResult["error"] = "not valid";
		}
		else
		{
			if(!$arResult["result"] = MyCCard::ValidateCard($cardNumber, 0, $ml))
			{
				$arResult["error"] = MyCCard::$LAST_ERROR["description"];
			}
		}

		return $arResult;
	}

	// подписка пользователя на уведомления о появлении в наличии товара
	static function SubscribeProduct($arFields)
	{
		global $USER;
		$arResult["result"] = false;
		$arRequest = filter_var_array(
			$arFields,
			array(
				"productId" => FILTER_SANITIZE_NUMBER_INT,
				"name" => FILTER_SANITIZE_STRING,
				"phone" => FILTER_SANITIZE_NUMBER_INT,
				"email" => FILTER_SANITIZE_EMAIL
			)
		);
		//теперь мыло необязательное... нада думать как дальше быть...
		if(!$arRequest["email"] and $USER->IsAuthorized())
		{
			$arRequest["email"] = $USER->GetEmail();
		}

		if($arRequest["productId"] and $arRequest["name"] and $arRequest["phone"])
		{
			\CModule::IncludeModule("iblock");

			// поверим, подписан ли данный емайл на данный товар
			$arRes = CIBlockElement::GetList(
				array(),
				array(
					"IBLOCK_ID" => (CIBlockTools::GetIBlockId("subscribeProduct") ?: 0),
					"PROPERTY_product" => $arRequest["productId"],
					"PROPERTY_name" => $arRequest["name"],
					"PROPERTY_phone" => $arRequest["phone"],
					"PROPERTY_email" => $arRequest["email"]
				),
				false,
				false,
				array("ID")
			)->Fetch();

			// если не подписан - подписываем
			if(!$arRes)
			{
				$objElement = new CIBlockElement;

				$arFields = array(
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => (CIBlockTools::GetIBlockId("subscribeProduct") ?: 0),
					"NAME" => time(),
					"ACTIVE" => "Y",
					"PROPERTY_VALUES" => array(
						"email" => $arRequest["email"],
						"product" => $arRequest["productId"],
						"name" => $arRequest["name"],
						"phone" => $arRequest["phone"]
					)
				);

				if($objElement->Add($arFields))
				{
					$arResult["result"] = true;

					// добавляем пользователя в список ExpertSender
					$objES = new APIExpertSender(API_EXPERTSENDER_KEY);

					$objES->AddSubscribers(array(
						"Mode" => "AddAndUpdate",
						"Force" => false,
						"ListId" => 36,
						"Email" => $arRequest["email"]
					));
				}
				else
				{
					$arResult["error"] = $objElement->LAST_ERROR;
				}
			}
		}

		return $arResult;
	}

	// обновление данных пользователя
	static function SetUserParameters($arFields)
	{
		global $USER;
		$arResult["result"] = false;
		$arParams = array();

		$arRequest = filter_var_array(
			$arFields,
			array(
				"user-phone" => FILTER_SANITIZE_NUMBER_INT,
				"card" => FILTER_SANITIZE_NUMBER_INT,
				// "email" => FILTER_SANITIZE_EMAIL
			)
		);

		foreach($arRequest as $key => &$value)
		{
			if(!is_null($value))
			{
				switch($key)
				{
					case "user-phone":
					{
						$value = preg_replace("/\D/", "", $value);

						if(strlen($value) != 10)
						{
							$arResult["error"][$key] = "Номер телефона должен состоять из 10 цифр";
						}
						else
						{
							$arParams["PERSONAL_PHONE"] = $value;
						}

						break;
					}

					case "card":
					{
						$result = static::CheckCard($value);

						if($result["result"] or ($result["error"] == "is used on ml"))
						{
							$arParams["UF_DISC"] = $value;
						}
						else
						{
							$arResult["error"][$key] = $result["error"];
						}

						break;
					}

					default:
						break;
				}
			}
		}

		if(!empty($arParams) and !$arResult["error"])
		{
			if($USER->IsAuthorized())
			{
				$objUser = new CUser;
				$objUser->Update($USER->GetID(), $arParams);
			}
			else
			{
				MyCCard::SetDataCard($arParams["UF_DISC"]);
			}

			$arResult["result"] = true;
		}

		return $arResult;
	}

	// получение структуры меню каталога для указанного раздела
	static function GetCatalogMenu($sectionId)
	{
		\CModule::IncludeModule("iblock");
		$arResult = array(
			"menu" => array(),
			"result" => true,
		);

		$sectionId = intval($sectionId);

		// получаем инфу по указанному  разделу
		$arSection = CIBlockSection::GetList(
			array(),
			array(
				"IBLOCK_ID" => CIBlockTools::GetIBlockId("shop"),
				"ID" => $sectionId,
				"ACTIVE" => "Y",
				"GLOBAL_ACTIVE" => "Y",
				"ELEMENT_SUBmenu" => "Y",
				"CNT_ACTIVE" => "Y",
				">ELEMENT_CNT" => 0,
			),
			true,
			array(
				"ID",
				"NAME",
				"SECTION_PAGE_URL",
				"DEPTH_LEVEL",
				"IBLOCK_SECTION_ID",
				"LEFT_MARGIN",
				"RIGHT_MARGIN",
			)
		)->GetNext();

		if($arSection)
		{
			// получаем все родительские разделы, включая сам указанный раздел
			$resNavChain = CIBlockSection::GetList(
				array(
					'left_margin' => 'asc'
				),
				array(
					"IBLOCK_ID" => CIBlockTools::GetIBlockId("shop"),
					"<=LEFT_BORDER" => $arSection["LEFT_MARGIN"],
					">=RIGHT_BORDER" => $arSection["RIGHT_MARGIN"],
					"<=DEPTH_LEVEL" => $arSection["DEPTH_LEVEL"],
					"ACTIVE" => "Y",
					"GLOBAL_ACTIVE" => "Y",
					"ELEMENT_SUBmenu" => "Y",
					"CNT_ACTIVE" => "Y",
					">ELEMENT_CNT" => 0,
				),
				true,
				array(
					"ID",
					"NAME",
					"SECTION_PAGE_URL",
					"DEPTH_LEVEL",
					"IBLOCK_SECTION_ID",
					"UF_SECTION3_NAME",
					"UF_SECTION4_NAME"
				)
			);

			while($arNavChain = $resNavChain->GetNext())
			{
				// получаем все смежные разделы, имеющие того же родителя
				$res = CIBlockSection::GetList(
					array(
						"SORT" => "ASC",
						"NAME" => "ASC"
					),
					array(
						"IBLOCK_ID" => CIBlockTools::GetIBlockId("shop"),
						"ACTIVE" => "Y",
						"GLOBAL_ACTIVE" => "Y",
						"IBLOCK_ACTIVE" => "Y",
						"SECTION_ID" => $arNavChain["IBLOCK_SECTION_ID"],
						"DEPTH_LEVEL" => $arNavChain["DEPTH_LEVEL"],
						"ELEMENT_SUBmenu" => "Y",
						"CNT_ACTIVE" => "Y",
						">ELEMENT_CNT" => 0,
					),
					true,
					array(
						"ID",
						"NAME",
						"SECTION_PAGE_URL",
						"DEPTH_LEVEL",
					)
				);

				while($arRes = $res->GetNext())
				{
					// добавляем в меню
					$arResult["menu"][$arRes["DEPTH_LEVEL"]]["items"][] = array(
						"id" => $arRes["ID"],
						"name" => $arRes["NAME"],
						"url" => $arRes["SECTION_PAGE_URL"],
						"selected" => (($arRes["ID"] == $arNavChain["ID"]) ? "Y" : "N"),
					);
				}

				// добавляем пункт "все"
				$arResult["menu"][$arNavChain["DEPTH_LEVEL"] + 1]["items"][] = array(
					"id" => $arNavChain["ID"],
					"name" => "Все",
					"url" => $arNavChain["SECTION_PAGE_URL"],
					"selected" => "N",
				);

				// добавляем название фильтра
				if($arNavChain["DEPTH_LEVEL"] == 2)
				{
					// не, не добавляем. как понадобится, тогда и добавим...
					if($arSection["DEPTH_LEVEL"] >= 2)
					{
						$arResult["menu"]["3"]["filterName"] = trim($arNavChain["UF_SECTION3_NAME"]);
					}

					if($arSection["DEPTH_LEVEL"] >= 3)
					{
						$arResult["menu"]["4"]["filterName"] = trim($arNavChain["UF_SECTION4_NAME"]);
					}
				}
			}

			// получаем все подразделы указанного раздела
			$res = CIBlockSection::GetList(
				array(
					"SORT" => "ASC",
					"NAME" => "ASC"
				),
				array(
					"IBLOCK_ID" => CIBlockTools::GetIBlockId("shop"),
					"ACTIVE" => "Y",
					"GLOBAL_ACTIVE" => "Y",
					"IBLOCK_ACTIVE" => "Y",
					"SECTION_ID" => $arSection["ID"],
					"DEPTH_LEVEL" => $arSection["DEPTH_LEVEL"] + 1,
					"ELEMENT_SUBmenu" => "Y",
					"CNT_ACTIVE" => "Y",
					">ELEMENT_CNT" => 0,
				),
				true,
				array(
					"ID",
					"NAME",
					"SECTION_PAGE_URL",
					"DEPTH_LEVEL",
				)
			);

			// подразделы имеются
			if($res->SelectedRowsCount() > 0)
			{
				// отмечаем пункт "все" как выбранный
				$arResult["menu"][$arSection["DEPTH_LEVEL"] + 1]["items"]["0"]["selected"] = "Y";
			}
			else
			{
				// иначе - удаляем
				unset($arResult["menu"][$arSection["DEPTH_LEVEL"] + 1]);
			}

			while($arRes = $res->GetNext())
			{
				// добавляем в меню
				$arResult["menu"][$arRes["DEPTH_LEVEL"]]["items"][] = array(
					"id" => $arRes["ID"],
					"name" => $arRes["NAME"],
					"url" => $arRes["SECTION_PAGE_URL"],
					"selected" => "N",
				);
			}
		}

		return $arResult;
	}

	// получение детальной информации по чеку
	static function GetCheque($chequeId)
	{
		global $USER;
		$objXml = new CDataXML();
		$arResult["result"] = false;
		$logID_ML_connect = start_log('GetCheque', 'ML_connect');

		try
		{
			// создаём soap-клиент и авторизуемся под админом
			ini_set('default_socket_timeout', 5);
			$params = array("trace" => true, "connection_timeout" => 5);
			$client = new SoapClient(API_ML_WSDL, $params);
			// $client = new SoapClient(API_ML_WSDL, array('trace' => 1));

			$objAuthenticate = $client->Authenticate(
				array(
					"login" => API_ML_LOGIN,
					"password" => API_ML_PASSWORD,
					"ip" => "127.0.0.1",
					"innerLogin" => $USER->GetLogin()
				)
			);
		}
		catch(Exception $e)
		{
			// запишем в журнал событий
			CEventLog::Add(array(
				"SEVERITY" => "WARNING",
				"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
				"MODULE_ID" => "",
				"ITEM_ID" => "Авторизация",
				"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
			));
		}
		end_log($logID_ML_connect);
		$logID_ML_work = start_log('GetCheque', 'ML_work');
		if(is_object($objAuthenticate))
		{
			try
			{
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "cheque_items",
							"parameters" => array(
								array("Name" => "cheque_id", "Value" => $chequeId),
							),
						)
					)->ExecuteResult->Value
				);
			}
			catch(Exception $e)
			{
			}

			if($objXml->selectnodes("/ChequeItems") and count($objXml->selectnodes("/ChequeItems")))
			{
				foreach($objXml->selectnodes("/ChequeItems")->children() as $objChequeItem)
				{
					$arChequeItem = array();

					foreach($objChequeItem->children() as $objNode)
					{
						switch($objNode->name())
						{
							case "ArticleName":
							{
								$arChequeItem["name"] = $objNode->textContent();
								break;
							}

							case "ArticleNumber":
							{
								$arChequeItem["article"] = intval($objNode->textContent());
								break;
							}

							case "Quantity":
							{
								$arChequeItem["quantity"] = round($objNode->textContent(), 3);
								break;
							}

							case "Bonus":
							{
								$arChequeItem["bonus"] = $objNode->textContent();
								if ($arChequeItem["bonus"] == 0)
								{
									$arChequeItem["bonus"] = 'Акция';
								}
								break;
							}
						}
					}

					if ($arChequeItem["article"] != "000000" and $arChequeItem["article"] != "033492")
					{
						$arResult["cheque"]["items"][] = $arChequeItem;
					}
				}

				$arResult["result"] = true;
			}
		}
		end_log($logID_ML_work);
		return $arResult;
	}

	// получение полной детальной информации по чеку
	static function SendFastEmailToSender($ar_REQUEST)
	{
		$objES = new APIExpertSender(API_EXPERTSENDER_KEY);

		// $a = $objES->GetUserID($old_email);

		$arFields = array(
			'Mode' => 'AddAndUpdate',
			'Force' => false,
			'ListId' => 4,
			'Email' => $ar_REQUEST['user_email'],
			// 'Id' => $a['id'],
			'Firstname' => $ar_REQUEST['user_name'],
			'Vendor' => 'quick_popup',
			// 'Lastname' => $USER->GetLastName(),
			// "Name" => false,
			'Properties' => array(
				array(
					'Id' => '33',
					'Value' => true,
				),
				array(
					'Id' => '29',
					'Value' => 'http://4lapy.ru/personal/cart/end.php',
				),
			)
		);
		$arRes_ = $objES->AddSubscribers($arFields);

		// return array($objES);
		return array('result'=>$arRes_, 'message'=>'Вроде ушло в сендер');
	}

	static function AddESSubscribersAtPopup($myRequest)
	{
		$id = 21;
		$objES = new APIExpertSender(API_EXPERTSENDER_KEY);
		$email = $myRequest["email"];
		$name = $myRequest["name"];
		$location = $myRequest["location"];
		$popup = $myRequest["popup"];
		$val = 1;
		switch($popup)
		{
			case "cat_popup":
				$id = 21;
				break;
			case "dog_popup":
				$id = 22;
				break;
			case "rodent_popup":
				$id = 26;
				break;
			case "bird_popup":
				$id = 25;
				break;
			case "fish_popup":
				$id = 27;
				break;
			case "reptile_popup":
				$id = 34;
				break;
			case "total_popup":
				$id = 12;
				$val = "all_popup";
				break;
		}
		if ($val == 1 ) {
			$arFields = array(
				'Mode' => 'AddAndUpdate',
				'ListId' => 130,
				'Email' => $email,
				'Firstname' => $name,
				'TrackingCode' => $popup,
				'Vendor' => '4lapy',
				'Ip' => $_SERVER['REMOTE_ADDR'],
				'Properties' => array(
					array(
						'Id' => $id,
						'Value' => $val,
					),
					array(
						'Id' => '12',
						'Value' => $popup,
					),
					array(
						'Id' => '29',
						'Value' => $location,
					),
					array(
						'Id' => '10',
						'Value' => 'ACF8C73A-723F-4F47-A5E7-A37990CC896A',
					),
					array(
						'Id' => '23',
						'Value' => 'true',
					),
					array(
						'Id' => '38',
						'Value' => 'true',
					)
				)
			);
		} else {
			$arFields = array(
				'Mode' => 'AddAndUpdate',
				'ListId' => 130,
				'Email' => $email,
				'Firstname' => $name,
				'TrackingCode' => $popup,
				'Vendor' => '4lapy',
				'Ip' => $_SERVER['REMOTE_ADDR'],
				'Properties' => array(
					array(
						'Id' => $id,
						'Value' => $val,
					),
					array(
						'Id' => '29',
						'Value' => $location,
					),
					array(
						'Id' => '10',
						'Value' => 'ACF8C73A-723F-4F47-A5E7-A37990CC896A',
					),
					array(
						'Id' => '23',
						'Value' => 'true',
					),
					array(
						'Id' => '38',
						'Value' => 'true',
					)
				)
			);
		}
		
		$objES->AddSubscribers($arFields);
	}

	// получение полной детальной информации по чеку
	static function GetChequeFull($chequeId)
	{
		global $USER;
		$objXml = new CDataXML();
		$arResult["result"] = false;
		$logID_ML_connect = start_log('GetCheque', 'ML_connect');

		try
		{
			// создаём soap-клиент и авторизуемся под админом
			ini_set('default_socket_timeout', 5);
			$params = array("trace" => true, "connection_timeout" => 5);
			$client = new SoapClient(API_ML_WSDL, $params);
			// $client = new SoapClient(API_ML_WSDL, array('trace' => 1));

			$objAuthenticate = $client->Authenticate(
				array(
					"login" => API_ML_LOGIN,
					"password" => API_ML_PASSWORD,
					"ip" => "127.0.0.1",
					"innerLogin" => $USER->GetLogin()
				)
			);
		}
		catch(Exception $e)
		{
			// запишем в журнал событий
			CEventLog::Add(array(
				"SEVERITY" => "WARNING",
				"AUDIT_TYPE_ID" => "MANZANA_LOYALTY",
				"MODULE_ID" => "",
				"ITEM_ID" => "Авторизация",
				"DESCRIPTION" => "[".$e->detail->details->code."] ".$e->detail->details->description,
			));
		}
		end_log($logID_ML_connect);
		$logID_ML_work = start_log('GetCheque', 'ML_work');
		if(is_object($objAuthenticate))
		{
			try
			{
				$objXml->LoadString(
					$client->Execute(
						array(
							"sessionId" => $objAuthenticate->AuthenticateResult->SessionId,
							"contractName" => "cheque_items",
							"parameters" => array(
								array("Name" => "cheque_id", "Value" => $chequeId),
							),
						)
					)->ExecuteResult->Value
				);
			}
			catch(Exception $e)
			{
			}

			if($objXml->selectnodes("/ChequeItems") and count($objXml->selectnodes("/ChequeItems")))
			{
				foreach($objXml->selectnodes("/ChequeItems")->children() as $objChequeItem)
				{
					$arChequeItem = array();

					foreach($objChequeItem->children() as $objNode)
					{
						$arChequeItem[$objNode->name()] = $objNode->textContent();
						// switch($objNode->name())
						// {
						// 	case "ArticleName":
						// 	{
						// 		$arChequeItem["name"] = $objNode->textContent();
						// 		break;
						// 	}

						// 	case "ArticleNumber":
						// 	{
						// 		$arChequeItem["article"] = intval($objNode->textContent());
						// 		break;
						// 	}

						// 	case "Quantity":
						// 	{
						// 		$arChequeItem["quantity"] = round($objNode->textContent(), 3);
						// 		break;
						// 	}
						// }
					}

					if ($arChequeItem["article"] != "000000" and $arChequeItem["article"] != "033492")
					{
						$arResult["cheque"]["items"][] = $arChequeItem;
					}
				}

				$arResult["result"] = true;
			}
		}
		end_log($logID_ML_work);
		return $arResult;
	}

	//получение способов доставки
	function GetDeliveryList($iLocation, $sLocationZip = '', $iWeight = '', $fPrice, $sCurrency, $sSiteId = null, $arShoppingCart = array())
	{
		\CModule::IncludeModule("sale");
		$arResult["result"] = false;

		$arResult['basket'] = self::GetBasket();
		$fPrice = ($fPrice)?:$arResult['basket']['basket']['summ'];

		$arDeliveries = CSaleDelivery::DoLoadDelivery(
			$iLocation,
			$sLocationZip,
			$iWeight,
			$fPrice,
			$sCurrency,
			$sSiteId,
			$arShoppingCart
		);

		foreach($arDeliveries as $arDelivery)
		{
			$arResult['data'][$arDelivery["ID"]] = array(
				"ID" => $arDelivery["ID"],
				"NAME" => $arDelivery["NAME"],
				"PRICE" => $arDelivery["PRICE"],
				"DESCRIPTION" => $arDelivery["DESCRIPTION"],
			);
		}
		if(!empty($arResult['data']))
			$arResult["result"] = true;

		if(!empty($arResult['data'][9]))
		{
			unset($arResult['data'][1]);
			unset($arResult['data'][4]);
			unset($arResult['data'][6]);
			unset($arResult['data'][11]);
			unset($arResult['data'][12]);
		}
		
		return $arResult;
	}

	//поиск выбранного города в нашей базе местоположений
	function GetLocationByName($sRegionName = '', $sCityName = '')
	{
		$arResult["result"] = false;

		if(isset($sCityName) and !empty($sCityName))
		{
			\CModule::IncludeModule("sale");

			$arFilter = array(
				"LID" => LANGUAGE_ID,
				"CITY_NAME" => $sCityName,
			);
			if($sCityName != 'Москва')
			{
				if(isset($sRegionName) and !empty($sRegionName))
					$arFilter["REGION_NAME"] = $sRegionName;

				if(($arFilter["REGION_NAME"] == "Москва и Московская область"))
					$arFilter["REGION_NAME"] = "Московская область";

				if($arFilter["REGION_NAME"] == 'Москва')
					$arFilter["REGION_NAME"] = '';
			}

			$db_vars = \CSaleLocation::GetList(
				array(
						"SORT" => "ASC",
						"COUNTRY_NAME_LANG" => "ASC",
						"CITY_NAME_LANG" => "ASC"
					),
				$arFilter,
				false,
				false,
				array(
						'ID',
						'CITY_ID',
						'CITY_NAME',
						'REGION_ID',
						'REGION_NAME',
					)
			);
			while ($vars = $db_vars->Fetch()):
				$arResult['data'][] = $vars;
			endwhile;

			if(!empty($arResult['data']))
				$arResult["result"] = true;
		}

		return $arResult;
	}
	
	// Поиск городов где есть в наличии заданный товар // вход: id товара, текущий город пользователя // выход: массив с городами и магазинами
 	function GetCityProducrs( $PRODUCT_ID, $town, $flag_see_in_stock = false )
	{	// $flag_see_in_stock - флаг по которому определяется все ли магазины выводить в конце или только те в которых есть наличие товара
		// например в одном клике если товар есть на РЦ то предполагается что он есть во всех магазинах
		\CModule::IncludeModule('iblock');
		\CModule::IncludeModule('catalog');

		$result = false;
					
		if (!empty($town) && !empty($PRODUCT_ID))
		{
			$CACHE_TIME = 36000;
			// надо закешировать это
			$CACHE_ID = SITE_ID."| ".$CACHE_TIME;
			$cache = new CPHPCache;

			if($cache->InitCache( $CACHE_TIME, $CACHE_ID , "/".SITE_ID.'/GetCityProducts' ) )
			{
				$arResult = $cache->GetVars();
			}
			else
			{

				// взять магазины все какие есть
				$filtr = array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('pet-shops'),
					'ACTIVE' => 'Y'
				);
				$ar_mas_metro = array(); // css станции метро
				$res = CIBlockElement::GetList(
					array('id'=>'asc'), 
					$filtr, 
					false, 
					false, 
					array(
						'ID', 
						'NAME', 
						'CODE', 
						'PROPERTY_code', 
						'PROPERTY_address', 
						'PROPERTY_METRO', 
						'PROPERTY_work_time', 
						'PROPERTY_city' 
					) 
				);
				
				$flag_shop_in_city = false; // флаг нахождения магазина в городе пользователя
				$metro = array();

				while ( $ar_res = $res->GetNext() ) {
					
					$arShops[$ar_res['PROPERTY_CODE_VALUE']] = $ar_res['PROPERTY_CODE_VALUE']; // R коды магазинов вообще всех какие есть
					
					if ( !empty($ar_res['PROPERTY_CITY_VALUE']) )
					{
						$arShopsAll[$ar_res['PROPERTY_CODE_VALUE']] = $ar_res; // здесь все магазины со всепи полями по R коду что бы брать	
						
						// определить есть ли магазин в городе текущего пользователя 
						if ( $ar_res['NAME'] == $town ) $flag_shop_in_city = true;

						// echo "<pre>";print_r($ar_res);echo "</pre>"."\r\n";
						if (!empty($ar_res['PROPERTY_METRO_VALUE'])) $metro[$ar_res['PROPERTY_METRO_VALUE']] = $ar_res['PROPERTY_METRO_VALUE'];
					}				
				}
				////
				
				// взять иконки станций метро
				$ar_metro = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=> CIBlockTools::GetIBlockId('st-metro'), "ID"=> $metro , "ACTIVE"=>"Y"), false, false, Array("ID", "NAME", "PROPERTY_css_class"));
				
				$metro = array();

				while( $ar_m = $ar_metro->Fetch()  )
				{
					$metro[$ar_m['ID']] = $ar_m;
				}

				$arResult['metro'] = $metro;
				$arResult['flag_shop_in_city'] = $flag_shop_in_city;
				$arResult['arShopsAll'] = $arShopsAll;
				$arResult['arShops'] = $arShops;

				$cache->StartDataCache();
				$cache->EndDataCache($arResult);
			}
			////

			//определить наличие в магазинах товара
			$stores = CCatalogStoreProduct::GetList(
				array(),
				array(
					// 'STORE_ID' => 'R108',
					'STORE_NAME' => $arResult['arShops'], // R коды магазинов
					'PRODUCT_ID' => $PRODUCT_ID,
				),
				false,
				false,
				array(
					'STORE_NAME',
					'AMOUNT',
					'PRODUCT_ID'
				)
			);
			while ($arrstores = $stores->Fetch())
			{
				if ($arrstores['AMOUNT'] > 0 ) 
				{
					$arShopsProduct[ $arrstores['STORE_NAME'] ] = array(
						'STORE_NAME' => $arrstores['STORE_NAME'],
						'AMOUNT' => $arrstores['AMOUNT']
					);
				}
			}
			////
			
			//нужно собрать данные в нужной форме
			$arRes = array(); // массив куда собирать
			foreach ($arResult['arShopsAll'] as $key => $value) {
				if ( !empty($arShopsProduct[$key]) || $flag_see_in_stock ) // если в магазине есть товар > 0 
				{
					$arRes[ $value['PROPERTY_CITY_VALUE'] ]['NAME_CITY'] = $value['NAME'] ; 
					$arRes[ $value['PROPERTY_CITY_VALUE'] ]['SHOPS'][$key]['STREET'] = $value['PROPERTY_ADDRESS_VALUE'];
					$arRes[ $value['PROPERTY_CITY_VALUE'] ]['SHOPS'][$key]['AMOUNT'] = $arShopsProduct[$key]['AMOUNT'];


					if (!empty($value['PROPERTY_METRO_VALUE'])) $arRes[ $value['PROPERTY_CITY_VALUE'] ]['SHOPS'][$key]['PROPERTY_METRO_VALUE'] = $value['PROPERTY_METRO_VALUE'];
					// надо отметить выбранный город 
					if ( ( ($arResult['flag_shop_in_city'] === true) && ( $value['NAME'] == $town  ) ) 
						|| ( ( $arResult['flag_shop_in_city'] === false) && ($value['NAME'] == 'Москва' ) ) ) 
					{
						$arRes[ $value['PROPERTY_CITY_VALUE'] ]['CHECK_CITY'] = '1' ;	
						$CHECK_CITY = 'Y';
					}
				}
			}
			if (!empty($arRes)) $result['city'] = $arRes;
			////
			if (!empty($arResult['metro'] )) $result['metro'] = $arResult['metro'] ; // метро
			if (!empty( $CHECK_CITY )) $result['CHECK_CITY'] = 'Y';
		}
		
		return $result;
	}

	// добавление/редактирование питомца
	function userPetEdit($arRequest, $arFiles)
	{
		\CModule::IncludeModule('iblock');

		$arResult = array(
			'result' => false
		);

		$categotyId = intval($arRequest['pet_type_id']);
		$petName = trim($arRequest['pet_name']);

		if ($categotyId <= 0) {
			$arResult['error'][] = 'pet_type_id';
		}
		if (strlen($petName) == 0) {
			$arResult['error'][] = 'pet_name';
		}
		$birthday = '';
		if($arRequest['pet_birthday']) {
			$birthday = new DateTime($arRequest['pet_birthday']);
			$now = new DateTime();
			if ($birthday > $now) {
				$arResult['error'][] = 'pet_birthday';
			}
		}

		if (!$arResult['error']) {
			$petId = intval($arRequest['pet_id']);
			global $USER;
			$userId = $USER->GetID();

			$arFields = array(
				'MODIFIED_BY' => $userId,
				'IBLOCK_ID' => CIBlockTools::GetIBlockId('user_pets'),
				'IBLOCK_SECTION_ID' => false,
				'NAME' => $petName,
				'PROPERTY_VALUES' => array(
					'USER_ID' => $userId,
					'PET_CATEGORY' => $categotyId,
					'PET_BREED' => intval($arRequest['pet_breed_id']),
					'PET_BREED_OTHER' => trim($arRequest['pet_breed_other']),
					'PET_SEX' => intval($arRequest['pet_gender_id']),
					'PET_BIRTHDAY' => ($birthday ? $birthday->format('d.m.Y') : '')
				)
			);

			$photoCnt = 0;
			$photoId = 0;

			if ($petId > 0) {
				$oPet = CIBlockElement::GetList(
					array(
						'NAME' => 'ASC'
					),
					array(
						'IBLOCK_ID' => CIBlockTools::GetIBlockId('user_pets'),
						'ID' => $petId,
						'ACTIVE' => 'Y',
						'PROPERTY_USER_ID' => $userId
					),
					false,
					false,
					array(
						'ID',
						'PROPERTY_PET_PHOTO'
					)
				);

				if ($arPet = $oPet->Fetch()) {
					$el = new CIBlockElement;
					$el->update($petId, $arFields);
					if (is_array($arPet['PROPERTY_PET_PHOTO_VALUE'])) {
						$photoCnt = count($arPet['PROPERTY_PET_PHOTO_VALUE']);
						$photoId = array_shift(array_reverse($arPet['PROPERTY_PET_PHOTO_VALUE']));
					}
				} else {
					$petId = 0;
				}
			}

			if ($petId <= 0) {
				$petsCount = CIBlockElement::GetList(
					array(
						'NAME' => 'ASC'
					),
					array(
						'IBLOCK_ID' => CIBlockTools::GetIBlockId('user_pets'),
						'ACTIVE' => 'Y',
						'PROPERTY_USER_ID' => $userId
					),
					false,
					false
				)->SelectedRowsCount();

				if ($petsCount >= 10) {
					$arResult['error'][] = 'pet_count';
				} else {
					$el = new CIBlockElement;
					$petId = $el->add($arFields);
				}
			}

			if (!$arResutl['error'] && $arFiles['pet_photo']) {
				if ($photoCnt >= 3) {
					$oProps = CIBlockElement::GetProperty(
						CIBlockTools::GetIBlockId('user_pets'),
						$petId,
						'sort',
						'asc',
						array(
							'CODE' => 'PET_PHOTO'
						)
					);

					while ($arProps = $oProps->Fetch())
					{
						if ($arProps['VALUE'] == $photoId) {
							CIBlockElement::SetPropertyValueCode(
								$petId,
								'PET_PHOTO',
								array(
									$arProps['PROPERTY_VALUE_ID'] => array(
										'VALUE' => array(
											'MODULE_ID' => 'iblock',
											'del' => 'Y'
										)
									)
								)
							);
							CFile::Delete($id);
						}
					}
				}
				$sRes = CFile::CheckImageFile($arFiles['pet_photo']);
				if (strlen($sRes) == 0)
				{
					$fileInfo = getimagesize($arFiles['pet_photo']['tmp_name']);
					if ($arFiles['pet_photo']['size'] > 1024 * 1024 || $fileInfo[0] > 800 || $fileInfo[1] > 800) {
						$tempName = tempnam(sys_get_temp_dir(), 'pet');
						CFile::ResizeImageFile(
							$arFiles['pet_photo']['tmp_name'],
							$tempName,
							array(
								'width' => 800,
								'height' => 800
							),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							array(),
							85
						);
						$arFiles['pet_photo']['tmp_name'] = $tempName;
					}
					CIBlockElement::SetPropertyValues($petId, CIBlockTools::GetIBlockId('user_pets'), $arFiles['pet_photo'], 'PET_PHOTO');
				}
			}

			if (!$arResult['error']) {
				$arResult['result'] = true;
				ob_start();
				global $APPLICATION;
				$APPLICATION->IncludeComponent('custom:user_pets', '', array('PETS_LIST_ONLY' => true), false);
				$arResult['html'] = ob_get_contents();
				ob_end_clean();
			}
		}

		return $arResult;
	}

	// удаление питомца
	function userPetRemove($arRequest)
	{
		$arResult = array(
			'result' => false
		);

		$petId = intval($arRequest['pet_id']);
		if ($petId > 0) {
			\CModule::IncludeModule('iblock');
			global $USER;
			$userId = $USER->GetID();

			$arPet = CIBlockElement::GetList(
				array(
					'NAME' => 'ASC'
				),
				array(
					'IBLOCK_ID' => CIBlockTools::GetIBlockId('user_pets'),
					'ID' => $petId,
					'ACTIVE' => 'Y',
					'PROPERTY_USER_ID' => $userId
				),
				false,
				false
			)->Fetch();

			if ($arPet) {
				CIBlockElement::Delete($petId);
				$arResult['pet_id'] = $petId;
				$arResult['result'] = true;
			}
		}

		return $arResult;
	}

}
?>