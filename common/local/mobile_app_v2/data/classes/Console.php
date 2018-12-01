<?php

require_once CLASSESPATH.'/Storage.php';
require_once CLASSESPATH.'/MyMongoDb.php';

class Console extends Storage
{
	protected $conf;

	protected $res;

	public function __construct($conf) {
		$this->res = [
			'result' => [],
			'errors' => [],
		];
		$this->conf = $conf;
	}

	public function attachManager($letterId = null, $projectId = null) {
		if(!$this->attachManager) {
			$this->initAttachManager($letterId, $projectId);
		}
		return $this->attachManager;
	}
}
