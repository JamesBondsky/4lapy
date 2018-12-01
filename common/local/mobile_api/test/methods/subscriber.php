<?php
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'offset' => '0',
			'limit' => '10',
			'sort' => 'email',
			'sort_order' => 'asc',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'offset' => '0',
			'limit' => '10',
			'sort' => 'email',
			'sort_order' => 'desc',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'offset' => '0',
			'limit' => '10',
			'sort' => 'unsubscribe',
			'sort_order' => 'desc',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'offset' => '0',
			'limit' => '10',
			'sort' => 'dt_last_activity',
			'sort_order' => 'asc',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'offset' => '0',
			'limit' => '10',
			'sort' => 'is_valid',
			'sort_order' => 'asc',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'group_id' => '1,3',
			'offset' => '0',
			'limit' => '20',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			//'email' => '',
			'project_id' => '6',
			'group_id' => '',
			'offset' => '0',
			'limit' => '15',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'group_id' => '100',
			'offset' => '0',
			'limit' => '20',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'email' => 'slin',
			'offset' => '0',
			'limit' => '10',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'group_id' => '1,3',
			'email' => 'slin',
			'offset' => '0',
			'limit' => '10',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'group_id' => '1000',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'id' => '546c77f064d39b88468c4df3',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'limit' => '0',
			'is_unsubscribe' => '0',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
			'limit' => '0',
			'is_activity' => '1',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'project_id' => '10',
			'email' => 'ilicherv.am@gmail.com',
			'unsubscribe' => '2015-04-16 16:00:00',
		],
	],

	[
		'request_method' => 'post',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'project_id' => '10',
			'email' => 'FAKE_EMAIL',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'project_id' => '10',
			'email' => 'ilicherv.am@gmail.com
				ilicherv-am@mail.ru
				   ailichev@market-soft.ru   ',
			'group_id' => '5,6,9,10',
			'props' => '{"sex":"\u041c","age":"24","var1":"10","var2":"20"}',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '120774',
			'project_id' => '10',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
			'unsubscribe' => 'Неизвестный формат даты', // если поле не пустое, но дата не распознана то ставится текущая дата
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '120774',
			'project_id' => '10',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'vil',
			'project_id' => '6',
			'group_id' => '',
			'offset' => '0',
			'limit' => '10',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'email' => 'vil',
			'project_id' => '6',
			'group_id' => '',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '6',
			'is_valid' => '1', // VALID
			'offset' => '0',
			'limit' => '10',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '6',
			'is_valid' => '0', // INVALID
			'offset' => '0',
			'limit' => '10',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '6',
			'is_valid' => '-1', // UNKNOWN
			'offset' => '0',
			'limit' => '10',
		],
	],

	[
		'request_method' => 'put',
		'h' => [
			'emails_props' => '{"21451":{"prop1":"1","prop2":"2","prop3":"3"}, "21452":{"prop1":"1","prop2":"2","prop3":"3"}}',
			'project_id' => '6',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'emails_props' => '{"test1@mail.ru":{"prop1":"1","prop2":"2","prop3":"3"}, "test2@mail.ru":{"prop1":"1","prop2":"2","prop3":"3"}}',
			'project_id' => '6',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'emails' => 'test1@mail.ru,test2@mail.ru',
			'project_id' => '6',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'emails' => '21451,21452',
			'project_id' => '6',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
		],
	],
	// upsert
	[
		'request_method' => 'put',
		'h' => [
			'emails_props' => '{"test1@mail.ru":{"prop1":"1","prop2":"2","prop3":"3"}, "test2@mail.ru":{"prop1":"1","prop2":"2","prop3":"3"}}',
			'project_id' => '6',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
			'upsert' => '1',
		],
	],
	// add to set (группы и свой свойства дополняют уже имеющиеся, а не заменяют)
	[
		'request_method' => 'put',
		'h' => [
			'emails_props' => '{"test1@mail.ru":{"prop1":"1","prop2":"2","prop3":"3"}, "test2@mail.ru":{"prop1":"1","prop2":"2","prop3":"3"}}',
			'project_id' => '6',
			'group_id' => '1,5,6,9',
			'props' => '{"sex":"\u041c","age":"240","var1":"10","var2":"20"}',
			'upsert' => '1',
			'add_to_set' => '1',
		],
	],
];
