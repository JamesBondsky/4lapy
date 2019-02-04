<?php

use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;

global $APPLICATION;

if (!$USER->IsAdmin()) {
    return;
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();
//айди модуля
$moduleId = htmlspecialcharsbx($request['mid'] != '' ? $request['mid'] : $request['id']);

Loader::includeModule($moduleId);

//вкладки
$tabs = [
    [
        'DIV' => 'auth',
        'TAB' => GetMessage('DOSTAVISTA_TAB_TITLE'),
        'TITLE' => GetMessage('DOSTAVISTA_TAB_TITLE'),
        'OPTIONS' => [
            [
                'dev_mode',
                GetMessage('DOSTAVISTA_DEV_MODE'),
                'N',
                [
                    'checkbox'
                ]
            ],
            [
                'callback_secret_key',
                GetMessage('DOSTAVISTA_CALLBACK_SECRET_KEY'),
                '',
                [
                    'text',
                    30
                ]
            ],
            [
                'heading' => true,
                'title' => GetMessage('DOSTAVISTA_PRODUCTION_MODE_OPTIONS'),
            ],
            [
                'client_id_prod',
                GetMessage('DOSTAVISTA_CLIENT_ID_PROD'),
                '',
                [
                    'text',
                    30
                ]
            ],
            [
                'token_prod',
                GetMessage('DOSTAVISTA_TOKEN_PROD'),
                '',
                [
                    'text',
                    30
                ]
            ],
            [
                'heading' => true,
                'title' => GetMessage('DOSTAVISTA_DEV_MODE_OPTIONS'),
            ],
            [
                'client_id_dev',
                GetMessage('DOSTAVISTA_CLIENT_ID_DEV'),
                '',
                [
                    'text',
                    30
                ]
            ],
            [
                'token_dev',
                GetMessage('DOSTAVISTA_TOKEN_DEV'),
                '',
                [
                    'text',
                    30
                ]
            ],
            [
                'heading' => true,
                'title' => GetMessage('DOSTAVISTA_SMS_HEADING')
            ],
            [
                'sms_courier_set',
                'Отправлять sms о назначении курьера на заказ',
                'N',
                [
                    'checkbox'
                ]
            ],
            [
                'sms_courier_time_phone',
                'Отправлять sms с интервалом прибытия и телефоном курьера',
                'Y',
                [
                    'checkbox'
                ]
            ],
            [
                'heading' => true,
                'title' => 'Настройки времени доставки'
            ],
            [
                'type' => 'time',
                'name' => 'delivery_start_time',
                'label' => 'Время начала работы доставки'
            ],
            [
                'type' => 'time',
                'name' => 'delivery_end_time',
                'label' => 'Время окончания работы доставки'
            ],
            [
                'heading' => true,
                'title' => 'Контент'
            ],
            [
                'text_express_delivery',
                'Текст с информацией, что пользователю доступна Экспресс-доставка',
                '',
                [
                    'textarea',
                    10
                ]
            ],
            [
                'text_express_delivery_time',
                'Текст с временем доставки для кнопки Экспресс-доставки',
                '',
                [
                    'text',
                    30
                ]
            ],
        ],
    ]
];

//установка параметров модуля
if ($request->isPost() && check_bitrix_sessid() && (!empty($request['apply']) || !empty($request['RestoreDefaults']))) {
    foreach ($tabs as $tab) {
        foreach ($tab['OPTIONS'] as $option) {
            if (!is_array($option)) {
                continue;
            }

            if (!empty($option['heading'])) {
                continue;
            }

            if ($request['apply']) {
                $name = $option[0] ? $option[0] : $option['name'];
                $optionValue = $request->getPost($name);
                COption::SetOptionString($moduleId, $name, is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
            } elseif ($request['RestoreDefaults']) {
                COption::RemoveOption($moduleId, $option[0]);
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . $moduleId . '&lang=' . LANG);
}

$tabControl = new CAdminTabControl(
    'tabControl',
    $tabs
);

$tabControl->Begin();
?>
    <form action="<?= htmlspecialchars($APPLICATION->GetCurPage()) ?>?<?= http_build_query(['mid' => $moduleId, 'lang' => LANG]) ?>" method="post">
        <?php
        foreach ($tabs as $tab) {
            $tabControl->BeginNextTab();
            foreach ($tab['OPTIONS'] as $option) {
                if ($option['heading']) {
                    ?>
                    <tr class="heading">
                        <td colspan="2"><?= $option['title']; ?></td>
                    </tr>
                    <?
                } elseif (!isset($option['type'])) {
                    __AdmSettingsDrawRow($moduleId, $option);
                } else {
                    ?>
                    <tr>
                        <td width="50%" class="adm-detail-content-cell-l">
                            <?= $option['label'] ?>
                        </td>
                        <td width="50%" class="adm-detail-content-cell-r">
                            <input type="<?= $option['type'] ?>" name="<?= $option['name'] ?>" value="<?=COption::GetOptionString($moduleId, $option['name'])?>">
                        </td>
                    </tr>

                    <?
                }
            }
        }
        $tabControl->Buttons();
        ?>
        <input type="submit" name="apply" value="<?= GetMessage('DOSTAVISTA_APPLY_BUTTON') ?>" class="adm-btn-save">
        <input type="submit" name="RestoreDefaults" value="<?= GetMessage('DOSTAVISTA_RESTORE_BUTTON') ?>" onclick="return confirm('<?= GetMessage('DOSTAVISTA_RESTORE_BUTTON_CONFIRM') ?>')" title="<?= GetMessage('DOSTAVISTA_RESTORE_BUTTON'); ?>">

        <?= bitrix_sessid_post() ?>
    </form>
<?php
$tabControl->End();
