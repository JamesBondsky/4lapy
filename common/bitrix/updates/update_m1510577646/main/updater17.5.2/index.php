<?
$updater->CopyFiles("install/components", "components");
$updater->CopyFiles("install/gadgets", "gadgets");
$updater->CopyFiles("install/js", "js");
$updater->CopyFiles("install/tools", "tools");

if ($updater->CanUpdateDatabase())
{
	if(!$updater->TableExists("b_user_auth_action"))
	{
		$updater->Query(array(
			"MySQL" => "
				create table b_user_auth_action
				(
					ID int NOT NULL AUTO_INCREMENT,
					USER_ID int NOT NULL,
					PRIORITY int NOT NULL DEFAULT 100,
					ACTION varchar(20),
					ACTION_DATE datetime NOT NULL,
					PRIMARY KEY (ID),
					index ix_auth_action_user(USER_ID, PRIORITY),
					index ix_auth_action_date(ACTION_DATE)
				)
			")
		);
	}

	$agent = $DB->ForSql("CUser::AuthActionsCleanUpAgent();");
	$res = $DB->Query("SELECT 'x' FROM b_agent WHERE MODULE_ID='main' AND NAME='".$agent."'");
	if(!$res->Fetch())
	{
		$date = $DB->CharToDateFunction(ConvertTimeStamp(strtotime(date('Y-m-d 04:15:00', time() + 86400)), 'FULL'));
		$updater->Query("INSERT INTO b_agent (MODULE_ID, SORT, NAME, ACTIVE, AGENT_INTERVAL, IS_PERIOD, NEXT_EXEC) VALUES('main', 100, '".$agent."', 'Y', 60*60*24, 'N', ".$date.")");
	}

	if ($updater->tableExists('b_event_type'))
	{
		$res = $DB->query("SELECT COUNT(*) CNT FROM b_event_type WHERE EVENT_NAME = 'MAIN_MAIL_CONFIRM_CODE'");
		$row = $res ? $res->fetch() : false;
		if (!$row || !($row['CNT'] > 0))
		{
			global $MESS;

			$langs = \CLanguage::getList($b = '', $o = '');
			while ($lang = $langs->fetch())
			{
				$lid = $lang['LID'];
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$updater->curPath.'/lang/'.$lid.'/install/index.php'))
					include $_SERVER['DOCUMENT_ROOT'].$updater->curPath.'/lang/'.$lid.'/install/index.php';

				$eventTypes = array(
					array(
						'LID'         => $lid,
						'EVENT_NAME'  => 'MAIN_MAIL_CONFIRM_CODE',
						'NAME'        => getMessage('MAIN_MAIL_CONFIRM_EVENT_TYPE_NAME'),
						'DESCRIPTION' => getMessage('MAIN_MAIL_CONFIRM_EVENT_TYPE_DESC'),
						'SORT'        => 1,
					)
				);

				foreach ($eventTypes as $item)
					\Bitrix\Main\Mail\Internal\EventTypeTable::add($item);

				$sitesIds = array();
				$sites = \CSite::getList($b = '', $o = '', array('LANGUAGE_ID' => $lid));
				while ($item = $sites->fetch())
					$sitesIds[] = $item['LID'];

				if (count($sitesIds) > 0)
				{
					$eventMessages = array(
						array(
							'ACTIVE'           => 'Y',
							'~TIMESTAMP_X'     => $DB->getNowFunction(),
							'EVENT_NAME'       => 'MAIN_MAIL_CONFIRM_CODE',
							'LID'              => end($sitesIds),
							'LANGUAGE_ID'      => $lid,
							'EMAIL_FROM'       => '#DEFAULT_EMAIL_FROM#',
							'EMAIL_TO'         => '#EMAIL_TO#',
							'SUBJECT'          => getMessage('MAIN_MAIL_CONFIRM_EVENT_NAME'),
							'MESSAGE'          => getMessage('MAIN_MAIL_CONFIRM_EVENT_DESC'),
							'BODY_TYPE'        => 'html',
							'SITE_TEMPLATE_ID' => 'mail_join',
							'MESSAGE_PHP'      => \Bitrix\Main\Mail\Internal\EventMessageTable::replaceTemplateToPhp(getMessage('MAIN_MAIL_CONFIRM_EVENT_DESC')),
						)
					);

					foreach ($eventMessages as $item)
					{
						$result = $DB->add('b_event_message', $item);
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
		}
	}
}

if($updater->CanUpdateKernel())
{
	$arToDelete = array(
		"modules/main/install/components/bitrix/main.file.input/templates/.default/template.htmlXXX.php",
		"components/bitrix/main.file.input/templates/.default/template.htmlXXX.php",
		"modules/main/install/js/main/phonenumber/flag/flag.css",
		"js/main/phonenumber/flag/flag.css",
	);
	foreach($arToDelete as $file)
		CUpdateSystem::DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"].$updater->kernelPath."/".$file);
}
