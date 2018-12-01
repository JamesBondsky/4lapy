<?php

abstract class Storage
{
	protected $attachManager;

	/**
	 * Инициализация AttachManager
	 * @return object || false
	 */
	public function initAttachManager($letterId = null, $projectId = null) {
		if(!$this->attachManager) {
			$this->attachManager = new AttachManager;
			if($letterId) {
				$staticServer = App::db()->fetchAssoc(
					'SELECT `server_static`.`id`, `server_static`.`webdav_url`, `server_static`.`webdav_user`,'
					.' `server_static`.`webdav_password`, `server_static`.`external_url`,'
					.' `server_static`.`type`, `server_static`.`container`, `server_static`.`token`,'
					.' `server_static`.`dt_token`, `server_static`.`storage_url`'
					.' FROM `server_static`, `project`, `letter_template`'
					.' WHERE `letter_template`.`project_id`=`project`.`id`'
					.' AND `project`.`server_static_id`=`server_static`.`id`'
					.' AND `letter_template`.`id`=:letter_template_id',
					array(
						'letter_template_id' => $letterId,
					)
				);
			} elseif($projectId) {
				$staticServer = App::db()->fetchAssoc(
					'SELECT `server_static`.`id`, `server_static`.`webdav_url`, `server_static`.`webdav_user`,'
					.' `server_static`.`webdav_password`, `server_static`.`external_url`,'
					.' `server_static`.`type`, `server_static`.`container`, `server_static`.`token`,'
					.' `server_static`.`dt_token`, `server_static`.`storage_url`'
					.' FROM `server_static`, `project`'
					.' WHERE `project`.`server_static_id`=`server_static`.`id` AND `project`.`id`=:project_id',
					array(
						'project_id' => $projectId,
					)
				);
			} else {
				return false;
			}

			$this->attachManager->init($staticServer);
		}
		return $this->attachManager;
	}

}
