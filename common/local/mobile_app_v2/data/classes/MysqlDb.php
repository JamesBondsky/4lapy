<?php

/**
 * Класс для работы с MysqlDB
 */
class MysqlDb
{
	private static $_pdo;
	private static $_instance;

	// Данные о подключение для переподнятия коннекта к MySQL
	private static $_connectionString;
	private static $_dbUser;
	private static $_dbPassword;

	/**
	 * Получение подключения
	 * @return object || false
	 */
	public static function getConnection($_connectionString = '', $_dbUser = '', $_dbPassword = '') {

		if(!self::$_pdo) {
			try {
				if($_connectionString) {
					self::$_connectionString = $_connectionString;
				}
				if($_dbUser) {
					self::$_dbUser = $_dbUser;
				}
				if($_dbPassword) {
					self::$_dbPassword = $_dbPassword;
				}

				self::$_pdo = new PDO(self::$_connectionString, self::$_dbUser, self::$_dbPassword);
			} catch(PDOException $e) {
				return false;
			}
		}
		return self::$_pdo;
	}

	/**
	 * Получение состояния объекта
	 * @return MysqlDb
	 */
	public static function getInstance($_connectionString, $_dbUser, $_dbPassword) {
		if(!self::$_instance) {
			self::$_instance = new self();
			self::$_instance->getConnection($_connectionString, $_dbUser, $_dbPassword);
		}
		return self::$_instance;
	}

	public static function getPDO() {
		return self::$_pdo;
	}

	/**
	 * Конструктор запроса SELECT
	 * @param  string $table Таблица
	 * @param  array $rows Столбцы выборки
	 * @param  array $conditions Параметры WHERE
	 * @param  array $row_conditions Дополнительные параметры WHERE
	 * @param  string $sort Сортировка
	 * @return array                  Результат запроса
	 */
	public function select($table, $rows = null, $conditions = array(), $row_conditions = array(), $sort) {
		if($rows) {
			$tmpRows = array();
			foreach($rows as $column) {
				$tmpRows[] = ($column != '*') ? ('`'.$column.'`') : $column;
			}
			$rows = implode(', ', $tmpRows);
		} else {
			$rows = '*';
		}
		$sql = "SELECT $rows FROM `$table`";
		$bind = array();
		$delim = ' WHERE';
		foreach($conditions as $field => $value) {
			$sql .= "$delim `$field`=:$field";
			$delim = ' AND';
			$bind[":$field"] = $value;
		}
		if($row_conditions) {
			if(!$conditions) {
				$sql .= ' WHERE ';
			}
			foreach($row_conditions as $str) {
				$sql .= $str;
			}
		}
		if($sort) {
			$sql .= " ORDER BY $sort";
		}
		$sql .= ';';

		$result = $this->query($sql, $bind);
		return $result;
	}

	/**
	 * Выполнение запроса
	 * @param  string $sql Запрос
	 * @param  array $bind Параметры запроса
	 * @return PDOStatement       Результат запроса
	 */
	public function query($sql = '', $bind = array(), $throwExceptionOnError = true) {
		$start = microtime(true);

		if(!$sql) {
			return false;
		}

		if(!self::$_pdo) {
			self::getConnection();
		}
		try {
			$query = self::getPDO()->prepare($sql);
		} catch(Exception $e) {
			self::getConnection();
			$query = self::getPDO()->prepare($sql);
		} catch(PDOException $e) {
			self::getConnection();
			$query = self::getPDO()->prepare($sql);
		}

		if(count($bind)) {
			foreach($bind as $key => $value) {
				if(is_array($value)) {
					$bind[$key] = self::escape($value);
				}
			}
		}
		$success = $query->execute($bind);
		if(!$success && $throwExceptionOnError) {
			$error = $query->errorInfo();
			throw new PDOException($error[2]);
		}

		$runtime = microtime(true) - $start;
		if($runtime > 0.1) {
			file_put_contents(TMPPATH.'/log/mysql.slow.log', date('Y-m-d H:i:s', time()).' '.$runtime.' sec: '.$sql."\r\n", FILE_APPEND);
		}
		save2log($runtime);
		return $query;
	}

	public function getAll($table, $columns = array(), $conditions = array(), $row_conditions = array(), $sort = '') {
		$query = $this->select($table, $columns, $conditions, $row_conditions, $sort);
		if(!$query)
			return false;
		$result = array();
		if(in_array('id', $columns) || in_array($table.'.id', $columns)) {
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
				$result[$row['id']] = $row;
			}
		} else {
			try {
				$result = $query->fetchAll(PDO::FETCH_ASSOC);
			} catch(Exception $e) {
				die($e->getMessage());
			}
		}
		return $result;
	}

	public function getOne($table, $columns = array(), $conditions = array(), $row_conditions = array(), $sort = '') {
		$query = $this->select($table, $columns, $conditions, $row_conditions, $sort);
		if(!$query)
			return false;
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function getScalar($table, $column, $conditions = array()) {
		$sql = "/*ms=last_used*/ SELECT $column FROM $table";
		$bind = array();
		if($conditions) {
			$sql .= ' WHERE';
			$delim = '';
			foreach($conditions as $field => $value) {
				$sql .= "$delim $field=:$field";
				$delim = ' AND';
				$bind[":$field"] = $value;
			}
		}

		$sql .= ';';

		$query = $this->query($sql, $bind);
		return $query->fetchColumn();
	}

	public function getCount($table, $conditions) {
		$sql = "/*ms=last_used*/ SELECT COUNT(*) FROM $table";
		$bind = array();
		if($conditions) {
			$sql .= ' WHERE';
			$delim = '';
			foreach($conditions as $field => $value) {
				$sql .= "$delim $field=:$field";
				$delim = ' AND';
				$bind[":$field"] = $value;
			}
		}

		$sql .= ';';

		$query = $this->query($sql, $bind);
		return intval($query->fetchColumn());
	}

	public function update($table, $data, $conditions) {
		$sql = "UPDATE $table SET";
		$bind = array();
		$delim = '';
		foreach($data as $field => $value) {
			$sql .= "$delim $field=:$field";
			$delim = ',';
			$bind[":$field"] = $value;
		}
		$sql .= " WHERE";

		$delim = '';
		foreach($conditions as $field => $value) {
			$sql .= "$delim $field=:where_$field";
			$delim = ' AND';
			$bind[":where_$field"] = $value;
		}
		$sql .= ';';

		return $this->query($sql, $bind);
	}

	public function insert($table, $data) {
		$sql = "INSERT INTO $table (`".implode('`, `', array_keys($data))."`) VALUES (:".implode(', :', array_keys($data)).");";
		$bind = array();
		foreach($data as $field => $value) {
			$bind[":$field"] = $value;
		}

		if($this->query($sql, $bind)) {
			return self::getPDO()->lastInsertId();
		}

		return 0;
	}

	// С параметром $onDuplicatedKeyUpdate=true можно исполоьзовать только если в таблице есть автоинкрементное поле id
	public function insertIgnore($table, $data, $onDuplicatedKeyUpdate = false) {
		$sql = 'INSERT'
			.($onDuplicatedKeyUpdate ? '' : ' IGNORE')
			.' INTO '.$table.' (`'.implode('`, `', array_keys($data)).'`) VALUES (:'.implode(', :', array_keys($data)).')'
			.($onDuplicatedKeyUpdate ? ' ON DUPLICATE KEY UPDATE `id`=LAST_INSERT_ID(`id`)' : '')
			.';';
		$bind = array();
		foreach($data as $field => $value) {
			$bind[":$field"] = $value;
		}

		try {
			if($this->query($sql, $bind)) {
				return self::getPDO()->lastInsertId();
			}
		} catch(PDOException $e) {
			// Обработка ситуации когда в таблице нет поля id а программси всетаки передал параметр $onDuplicatedKeyUpdate
			$sql = 'INSERT IGNORE INTO '.$table.' (`'.implode('`, `', array_keys($data)).'`) VALUES (:'.implode(', :', array_keys($data)).');';
			$bind = array();
			foreach($data as $field => $value) {
				$bind[":$field"] = $value;
			}
			if($this->query($sql, $bind)) {
				return self::getPDO()->lastInsertId();
			}
		}

		return 0;
	}

	public function multiInsertIgnore($table, $aData) {
		return $this->multiInsert($table, $aData, true);
	}

	public function multiInsert($table, $aData, $isIgnore = false) {
		$bind = array();
		$sql = '';
		$iteration = 1;
		$aValues = array();
		foreach($aData as $data) {
			if($sql == '') {
				$sql = "INSERT ".($isIgnore ? 'IGNORE ' : '')."INTO `$table` (`".implode('`, `', array_keys($data))."`) VALUES";
			}
			$array_keys = array();
			foreach($data as $field => $value) {
				$array_keys[] = $field.$iteration;
				$bind[':'.$field.$iteration] = $value;
			}
			$aValues[] = "(:".implode(', :', $array_keys).")";

			$iteration++;
		}
		$sql .= implode(', ', $aValues).';';
		if($this->query($sql, $bind)) {
			return self::getPDO()->lastInsertId();
		}

		return 0;
	}

	public function delete($table, $conditions, $row_conditions = array()) {
		$sql = "DELETE FROM `$table` WHERE ";

		$bind = array();
		$delim = '';
		foreach($conditions as $field => $value) {
			$sql .= "$delim `$field`=:$field";
			$delim = ' AND';
			$bind[":$field"] = $value;
		}

		if($row_conditions) {
			$isFirst = true;
			foreach($row_conditions as $condition) {
				$sql .= ($isFirst && !$conditions ? '' : ' AND ').'('.$condition.')';
				$isFirst = false;
			}
		}

		$sql .= ';';
		return $this->query($sql, $bind);
	}

	/**
	 * Извлечение всех элементов из результата запроса
	 * @param  [type] $query [description]
	 * @param  string $key Ключ группировки
	 * @return array        Результат
	 */
	public function fetchAll($query, $key = 'id') {
		$result = array();
		while($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$result[$row[$key]] = $row;
		}
		return $result;
	}

	/**
	 * Извлечение одного числового значения из результата запроса
	 * @param  [type] $query Результат запроса
	 * @return integer      Результат
	 */
	public function fetchScalar($query) {
		return intval($this->fetchColumn($query));
	}

	/**
	 * Извлечение одного значения из результата запроса
	 * @param  [type] $query Результат запроса
	 * @return string      Результат
	 */
	public function fetchColumn($query) {
		$result = 0;
		if($row = $query->fetch(PDO::FETCH_NUM)) {
			$result = current($row);
		}
		return $result;
	}

	public function upsert($table, $data) {
		$sql = "INSERT INTO $table (".implode(', ', array_keys($data)).") VALUES (:".implode(', :', array_keys($data)).")".
			"ON DUPLICATE KEY UPDATE ";
		$bind = array();
		$last_key = end(array_keys($data));
		foreach($data as $field => $value) {
			$sql .= $field." = :$field";
			if($last_key != $field) {
				$sql .= ', ';
			}
			$bind[":$field"] = $value;
		}
		if($this->query($sql, $bind)) {
			return self::getPDO()->lastInsertId();
		}

		return 0;
	}

	public function fetchAssoc($sql, $bind = array()) {
		$query = $this->query($sql, $bind);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	/*
	 * $rowSetNumber - если в запросе более одного возврата данных - то данный параметр позволит выбрать нужный по счету
	 */
	public function fetchAllAssoc($sql, $bind = array(), $rowSetNumber = 0) {
		$query = $this->query($sql, $bind);
		if($rowSetNumber > 0) {
			for($i = 0; $i < $rowSetNumber; $i++) {
				$query->nextRowset();
			}
		}
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Экранизация вводных данных
	 * @param  mixed $value Данные
	 * @return mixed
	 */
	public static function escape($value) {
		if(is_array($value)) {
			$result = array();
			foreach($value as $val) {
				$result[] = self::getPDO()->quote($val);
			}
			return implode(',', $result);
		} else {
			return self::getPDO()->quote($value);
		}
	}

	public function getLastInsertId() {
		$this->fetchScalar($this->query('/*ms=last_used*/ SELECT LAST_INSERT_ID();'));
	}

	public function d($sql, $aBinds = array(), $exit = true) {
		foreach($aBinds as $k => $v) {
			$v = self::escape($v);
			$sql = preg_replace('/:'.$k.'/', $v, $sql);
		}
		print_r($sql);
		if($exit) {
			exit;
		}
	}

}
