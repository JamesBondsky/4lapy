<?
//define('NEED_AUTH', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Поиск по паспорту');

?>
<? $APPLICATION->IncludeComponent('fourpaws:front_office.fest_search',
                                  'fo.17.0',
                                  [
                                      'BY_PASSPORT' => 'Y',
                                  ],
                                  null,
                                  [
                                      'HIDE_ICONS' => 'Y',
                                  ]); ?>
<?
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';