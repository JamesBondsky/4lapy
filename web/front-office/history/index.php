<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("История по карте");
?>
<? $APPLICATION->IncludeComponent('fourpaws:front_office.card.history',
                                  'fo.17.0',
                                  [
                                      'LAST_CHEQUES_CNT' => 10,
                                  ],
                                  null,
                                  [
                                      'HIDE_ICONS' => 'Y',
                                  ]); ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
