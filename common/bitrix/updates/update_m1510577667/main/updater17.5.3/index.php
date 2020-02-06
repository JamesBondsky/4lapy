<?
$updater->CopyFiles("install/components", "components");
$updater->CopyFiles("install/js", "js");

if ($updater->canUpdateDatabase())
{
	if ($updater->tableExists('b_event_type'))
	{
		global $MESS;

		$DB->query("DELETE FROM b_event_message WHERE EVENT_NAME = 'MAIN_MAIL_CONFIRM_CODE'");
		$DB->query("DELETE FROM b_event_type WHERE EVENT_NAME = 'MAIN_MAIL_CONFIRM_CODE'");

		$defaultLid = \Bitrix\Main\Localization\Loc::getDefaultLang($lid);
		$langs = \CLanguage::getList($b = '', $o = '');
		while ($lang = $langs->fetch())
		{
			$lid = $lang['LID'];

			$langFile = $_SERVER['DOCUMENT_ROOT'].$updater->curPath.'/lang/'.$lid.'/install/index.php';
			if (!file_exists($langFile))
				$langFile = $_SERVER['DOCUMENT_ROOT'].$updater->curPath.'/lang/'.$defaultLid.'/install/index.php';
			if (!file_exists($langFile))
				$langFile = $_SERVER['DOCUMENT_ROOT'].$updater->curPath.'/lang/en/install/index.php';

			if (file_exists($langFile))
			{
				include $langFile;

				\Bitrix\Main\Mail\Internal\EventTypeTable::add(array(
					'LID'         => $lid,
					'EVENT_NAME'  => 'MAIN_MAIL_CONFIRM_CODE',
					'NAME'        => getMessage('MAIN_MAIL_CONFIRM_EVENT_TYPE_NAME'),
					'DESCRIPTION' => getMessage('MAIN_MAIL_CONFIRM_EVENT_TYPE_DESC'),
					'SORT'        => 1,
				));
			}
		}

		$sitesIds = array();
		$sites = \CSite::getList($b = '', $o = '');
		while ($item = $sites->fetch())
			$sitesIds[] = $item['LID'];

		if (count($sitesIds) > 0)
		{
			$result = $DB->add('b_event_message', array(
				'ACTIVE'           => 'Y',
				'~TIMESTAMP_X'     => $DB->getNowFunction(),
				'EVENT_NAME'       => 'MAIN_MAIL_CONFIRM_CODE',
				'LID'              => end($sitesIds),
				'EMAIL_FROM'       => '#DEFAULT_EMAIL_FROM#',
				'EMAIL_TO'         => '#EMAIL_TO#',
				'SUBJECT'          => '#MESSAGE_SUBJECT#',
				'MESSAGE'          => "<? EventMessageThemeCompiler::includeComponent('bitrix:main.mail.confirm', '', \$arParams); ?>",
				'BODY_TYPE'        => 'html',
				'SITE_TEMPLATE_ID' => 'mail_join',
				'MESSAGE_PHP'      => "<? EventMessageThemeCompiler::includeComponent('bitrix:main.mail.confirm', '', \$arParams); ?>",
			));
			if ($result > 0)
			{
				foreach ($sitesIds as $subItem)
				{
					\Bitrix\Main\Mail\Internal\EventMessageSiteTable::add(array(
						'EVENT_MESSAGE_ID' => $result,
						'SITE_ID'          => $subItem,
					));
				}
			}
		}
	}
}
if($updater->CanUpdateKernel())
{
	$arToDelete = array(
		"modules/main/install/css/main/fonts/",
		"css/main/fonts/",
	);
	foreach($arToDelete as $file)
		CUpdateSystem::DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"].$updater->kernelPath."/".$file);
}