<?php

namespace EstaleiroWeb\ED\Db\Conn;

//options array(PDO::MYSQL_ATTR_FOUND_ROWS => true)
class Conn_MYSQL extends ConnMain {
	public $maxInsert = 200;
	
	public function close() {
		if($this->conn) $this->conn->query('KILL CONNECTION_ID()');
		return parent::close();
	}

	public function get_charset() {
	}
	public function set_charset($charset) {
	}
	public function mountComparation($keys, $aliasSource = null, $aliasTarget = null, $div = ' AND ') {
		($aliasSource) || ($aliasSource = 's');
		($aliasTarget) || ($aliasTarget = 't');
		if (!is_array($keys)) $keys = preg_split('/\s*[,;]\s*/', $keys);
		foreach ($keys as $keySource => &$keyTarget) {
			if (is_numeric($keySource)) $keySource = $keyTarget;
			$keyTarget = "{$aliasTarget}.{$keyTarget}={$aliasSource}.{$keySource}";
		}
		return implode($div, $keys);
	}
	public function insertLine($tbl, $line = false) {
		static $sql = array();
		if ($line) $sql[$tbl][] = $this->mountValueInsertLine($line);
		if (@$sql[$tbl] && (!$line || count(@$sql[$tbl]) >= $this->maxLinesInsert)) {
			$this->query("INSERT IGNORE $tbl VALUES \n" . implode(", \n", $sql[$tbl]));
			$sql[$tbl] = array();
			return true;
		}
		return false;
	}

	public function dbTbl(&$tbl) {
		if (!preg_match('/(`[^`]+`|\w+)(?:\.(`[^`]+`|\w+))?/', $tbl, $ret)) die("Erro de formato: $tbl\n");
		array_shift($ret);
		$ret = preg_replace('/^`([^`]+)`$/', '\1', $ret);
		$db = count($ret) > 1 ? array_shift($ret) : $this->db;
		if (!$db) die("Daba base não identificado: $tbl\n");
		$tb = array_shift($ret);
		if (!$tb) die("Tabela não identificada: $tbl\n");
		return $db;
	}
	public function getFields($tbl) {
		$db = $this->splitDbTbl($tbl);
	}
	public function existsTable($tbl) {
		$db = $this->splitDbTbl($tbl);
		return $this->fastValue("
			SELECT COUNT(1) q
			FROM information_schema.TABLES t
			WHERE t.TABLE_SCHEMA={$this->addQuote($db)} 
			AND t.TABLE_TYPE='BASE TABLE'
			AND t.TABLE_NAME={$this->addQuote($tbl)}
		");
	}
	public function showDatabases() {
		$out = $this->query_all('
		SELECT 
		s.SCHEMA_NAME `SCHEMA`, 
		s.CATALOG_NAME `DOMAIN`, 
		NULL `OWNER`, 
		s.DEFAULT_CHARACTER_SET_NAME,
		s.DEFAULT_COLLATION_NAME,
		NULL DEFAULT_CHARACTER_SET_CATALOG, 
		NULL DEFAULT_CHARACTER_SET_SCHEMA,
		s.SQL_PATH
		FROM information_schema.SCHEMATA s
		ORDER BY s.SCHEMA_NAME
		', false);
		if (is_null($out)) {
			$res = $this->query('SHOW DATABASES');
			$out = array();
			while ($line = $res->fetch_row()) {
				$out[] = array(
					'SCHEMA' => $line[0],
					'DOMAIN' => 'def',
					'OWNER' => null,
					'DEFAULT_CHARACTER_SET_NAME' => '',
					'DEFAULT_COLLATION_NAME' => '',
					'DEFAULT_CHARACTER_SET_CATALOG' => null,
					'DEFAULT_CHARACTER_SET_SCHEMA' => null,
					'SQL_PATH' => '',
				);
			}
			$res->close();
		}
		return $out;
	}
	public function showTables($db = null) {
		if (!$db) $db = $this->db;
		if (!$db) return;

		$out = $this->query_all($sql = "
		SELECT
			t.TABLE_NAME `TABLE`, 
			t.TABLE_CATALOG `DOMAIN`, 
			t.TABLE_SCHEMA `SCHEMA`, 
			t.TABLE_TYPE `TYPE`, 
			t.ENGINE, 
			t.VERSION, 
			t.ROW_FORMAT, 
			t.TABLE_ROWS `ROWS`, 
			t.AVG_ROW_LENGTH, 
			t.DATA_LENGTH, 
			t.MAX_DATA_LENGTH, 
			t.INDEX_LENGTH, 
			t.DATA_FREE, 
			t.AUTO_INCREMENT, 
			t.CREATE_TIME, 
			t.UPDATE_TIME, 
			t.CHECK_TIME, 
			t.TABLE_COLLATION COLLATION, 
			t.CHECKSUM, 
			t.CREATE_OPTIONS, 
			t.TABLE_COMMENT `COMMENT`
		FROM information_schema.TABLES t
		WHERE t.TABLE_SCHEMA={$this->addQuote($db)} AND t.TABLE_TYPE='BASE TABLE'
		ORDER BY t.TABLE_NAME
		", false);
		if (is_null($out)) {
			$res = $this->query('SHOW TABLES FROM `' . $db . '`');
			$out = array();
			while ($line = $res->fetch_row()) {
				$out[] = array(
					'TABLE' => $line[0],
					'DOMAIN' => 'def',
					'SCHEMA' => $db,
					'TYPE' => 'BASE TABLE',
					'ENGINE' => null,
					'VERSION' => null,
					'ROW_FORMAT' => null,
					'ROWS' => null,
					'AVG_ROW_LENGTH' => null,
					'DATA_LENGTH' => null,
					'MAX_DATA_LENGTH' => null,
					'INDEX_LENGTH' => null,
					'DATA_FREE' => null,
					'AUTO_INCREMENT' => null,
					'CREATE_TIME' => null,
					'UPDATE_TIME' => null,
					'CHECK_TIME' => null,
					'COLLATION' => null,
					'CHECKSUM' => null,
					'CREATE_OPTIONS' => null,
					'COMMENT' => null,
				);
			}
			$res->close();
		}
		return $out;
	}
	public function showViews($db = null) {
		if (!($db = $db ? $db : $this->db)) return;

		return $this->query_all("
		SELECT
		t.TABLE_NAME `TABLE`, 
		t.TABLE_CATALOG `DOMAIN`, 
		t.TABLE_SCHEMA `SCHEMA`, 
		t.TABLE_TYPE `TYPE`, 
		t.ENGINE, 
		t.VERSION, 
		t.ROW_FORMAT, 
		t.TABLE_ROWS `ROWS`, 
		t.AVG_ROW_LENGTH, 
		t.DATA_LENGTH, 
		t.MAX_DATA_LENGTH, 
		t.INDEX_LENGTH, 
		t.DATA_FREE, 
		t.AUTO_INCREMENT, 
		t.CREATE_TIME, 
		t.UPDATE_TIME, 
		t.CHECK_TIME, 
		t.TABLE_COLLATION COLLATION, 
		t.CHECKSUM, 
		t.CREATE_OPTIONS, 
		t.TABLE_COMMENT `COMMENT`
		FROM information_schema.TABLES t
		WHERE t.TABLE_SCHEMA={$this->addQuote($db)} AND t.TABLE_TYPE!='BASE TABLE'
		ORDER BY t.TABLE_NAME
		", false);
	}
	public function showFunctions($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showProcedures($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showEvents($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showAllObjects($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function buildSQL_Table() {
		$argv = func_get_args();
		if (@$argv[1]) {
			$tbl = $argv[1];
			($db = $argv[0]) || ($db = $this->db);
		} elseif (@$argv[0]) {
			$tbl = $argv[0];
			$db = $this->db;
		} else return; { //Get Privileges
			$user = $this->addQuote(preg_replace('/(.+)@(.+)/', '\'\1\'@\'\2\'', $this->fastValue('SELECT CURRENT_USER()')));
			$dbPriv = $this->addQuote($db);
			$tblPriv = $this->addQuote($tbl);
			$tablePrivilege = $this->fastValue("SELECT IFNULL(
			(SELECT 'GLOBAL' FROM information_schema.USER_PRIVILEGES pu WHERE pu.PRIVILEGE_TYPE='SELECT' AND pu.GRANTEE=$user),
			IFNULL(
			(SELECT 'SCHEMA' FROM information_schema.SCHEMA_PRIVILEGES WHERE PRIVILEGE_TYPE='SELECT' AND TABLE_SCHEMA=$dbPriv AND GRANTEE=$user),
			(SELECT 'TABLE'  FROM information_schema.TABLE_PRIVILEGES  WHERE PRIVILEGE_TYPE='SELECT' AND TABLE_SCHEMA=$dbPriv AND TABLE_NAME=$tblPriv AND GRANTEE=$user)
			)
		) p");
		}
		if ($tablePrivilege) $fields = '*';
		else {
			$res = $this->query("SELECT * FROM information_schema.COLUMN_PRIVILEGES WHERE PRIVILEGE_TYPE='SELECT' AND TABLE_SCHEMA=$dbPriv AND TABLE_NAME=$tblPriv AND GRANTEE=$user");
			$fields = array();
			while ($line = $res->fetch_assoc()) $fields[] = $this->fieldDelimiter($line['COLUMN_NAME']);
			$res->close();
			if (!$fields) return;
			$fields = implode(', ', $fields);
		}

		return "SELECT $fields \nFROM {$this->buildTableName($db,$tbl)}";
	}
}
