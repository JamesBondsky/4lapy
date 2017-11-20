<?
$aMenuLinks = Array(
	Array(
		"Мои заказы", 
		"/personal/orders/", 
		Array(), 
		Array("icon"=>"icon-order"), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Адреса доставки", 
		"/personal/address/", 
		Array(), 
		Array("icon"=>"icon-delivery-header"), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Мои питомцы", 
		"/personal/pets/", 
		Array(), 
		Array("icon"=>"icon-pet"), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Бонусы", 
		"/personal/bonus/", 
		Array(), 
		Array("icon"=>"icon-bonus"), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Профиль", 
		"/personal/", 
		Array(), 
		Array("icon"=>"icon-profile"), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Выход", 
		"?logout=yes", 
		Array(), 
		Array("icon"=>"icon-exit"), 
		"\$USER->IsAuthorized()" 
	)
);
?>