<?
class CPHPCacheMemcacheHL implements ICacheBackend
{
	private static $obMemcache;
	private static $basedir_version = array();
	public $sid = "";
	//cache stats
	public $written = false;
	public $read = false;
	// unfortunately is not available for memcache...
	private $useLock = true;
	private $lock = '';

	function __construct()
	{
		if(!is_object(self::$obMemcache))
			self::$obMemcache = new Memcache;
		if(defined("BX_MEMCACHE_PORT"))
			$port = intval(BX_MEMCACHE_PORT);
		else
			$port = 11211;

		if(!defined("BX_MEMCACHE_CONNECTED"))
		{
			if(self::$obMemcache->connect(BX_MEMCACHE_HOST, $port))
			{
				define("BX_MEMCACHE_CONNECTED", true);
				register_shutdown_function(array("CPHPCacheMemcacheHL", "close"));
			}
		}

		if(defined("BX_CACHE_SID"))
			$this->sid = BX_CACHE_SID;
		else
			$this->sid = "BX";
			
		if (defined("BX_CACHE_DONT_LOCK"))
			$this->useLock = !BX_CACHE_DONT_LOCK;
		$this->sid .= '|'.$this->useLock;
	}

	function close()
	{
		if(defined("BX_MEMCACHE_CONNECTED") && is_object(self::$obMemcache))
			self::$obMemcache->close();
	}

	function IsAvailable()
	{
		return defined("BX_MEMCACHE_CONNECTED");
	}

	function clean($basedir, $initdir = false, $filename = false)
	{
		if(is_object(self::$obMemcache))
		{
			if(strlen($filename))
			{
				if(!isset(self::$basedir_version[$basedir]))
					self::$basedir_version[$basedir] = self::$obMemcache->get($this->sid.$basedir);

				if(self::$basedir_version[$basedir] === false || self::$basedir_version[$basedir] === '')
					return true;

				if($initdir !== false)
				{
					$initdir_version = self::$obMemcache->get(self::$basedir_version[$basedir]."|".$initdir);
					if($initdir_version === false || $initdir_version === '')
						return true;
				}
				else
				{
					$initdir_version = "";
				}

				self::$obMemcache->replace(self::$basedir_version[$basedir]."|".$initdir_version."|".$filename, "", 0, 1);
			}
			else
			{
				if(strlen($initdir))
				{
					if(!isset(self::$basedir_version[$basedir]))
						self::$basedir_version[$basedir] = self::$obMemcache->get($this->sid.$basedir);

					if(self::$basedir_version[$basedir] === false || self::$basedir_version[$basedir] === '')
						return true;

					self::$obMemcache->replace(self::$basedir_version[$basedir]."|".$initdir, "", 0, 1);
				}
				else
				{
					if(isset(self::$basedir_version[$basedir]))
						unset(self::$basedir_version[$basedir]);

					self::$obMemcache->replace($this->sid.$basedir, "", 0, 1);
				}
			}
			return true;
		}

		return false;
	}

	function read(&$arAllVars, $basedir, $initdir, $filename, $TTL)
	{
		if(!isset(self::$basedir_version[$basedir]))
			self::$basedir_version[$basedir] = self::$obMemcache->get($this->sid.$basedir);

		if(self::$basedir_version[$basedir] === false || self::$basedir_version[$basedir] === '')
			return false;

		if($initdir !== false)
		{
			$initdir_version = self::$obMemcache->get(self::$basedir_version[$basedir]."|".$initdir);
			if($initdir_version === false || $initdir_version === '')
				return false;
		}
		else
		{
			$initdir_version = "";
		}

		$key = self::$basedir_version[$basedir]."|".$initdir_version."|".$filename;
		
		if ($this->useLock)
		{
			$timemark = self::$obMemcache->get($key."~");
			if ($timemark === false || $timemark === '') //cache expired
			{
				if ($this->lock($key))
				{
					//self::$obMemcache->set($key."~", $key, 0, $TTL); //time mark
					return false;
				}
			}
		}
		
		$arAllVars = self::$obMemcache->get($key);

		if($arAllVars === false || $arAllVars === '')
			return false;

		return true;
	}

	function write($arAllVars, $basedir, $initdir, $filename, $TTL)
	{
		if(!isset(self::$basedir_version[$basedir]))
			self::$basedir_version[$basedir] = self::$obMemcache->get($this->sid.$basedir);

		if(self::$basedir_version[$basedir] === false || self::$basedir_version[$basedir] === '')
		{
			self::$basedir_version[$basedir] = $this->sid.md5(mt_rand());
			self::$obMemcache->set($this->sid.$basedir, self::$basedir_version[$basedir]);
		}

		if($initdir !== false)
		{
			$initdir_version = self::$obMemcache->get(self::$basedir_version[$basedir]."|".$initdir);
			if($initdir_version === false || $initdir_version === '')
			{
				$initdir_version = $this->sid.md5(mt_rand());
				self::$obMemcache->set(self::$basedir_version[$basedir]."|".$initdir, $initdir_version);
			}
		}
		else
		{
			$initdir_version = "";
		}

		$key = self::$basedir_version[$basedir]."|".$initdir_version."|".$filename;
		if ($this->useLock)
		{
			self::$obMemcache->set($key."~", $key, 0, $TTL); //time mark
			self::$obMemcache->set($key, $arAllVars, 0, 0); //data stored forever
			$this->unlock();
		}
		else
		{
			self::$obMemcache->set($key, $arAllVars, 0, $TTL);
		}
	}

	function lock($key)
	{
		global $DB;

		$this->lock = md5($key)."_cache";

		$db_lock = $DB->Query("SELECT GET_LOCK('".$this->lock."', 0) as L");
		$ar_lock = $db_lock->Fetch();
		if($ar_lock["L"] == "0")
		{
			$this->lock = '';
			return false;
		}
		else
		{
			return true;
		}
	}

	function unlock()
	{
		global $DB;
		if ($this->lock)
		{
			$DB->Query("SELECT RELEASE_LOCK('".$this->lock."') as L");
		}
	}

	function IsCacheExpired($path)
	{
		return false;
	}
}
?>