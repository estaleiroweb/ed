<?php

namespace EstaleiroWeb\ED\Db\Conn;

class Conn_SQLSRV extends Conn_DBLIB {
	public function getDb($db = null) {
		if ($db == '') $db = @$this->parts['dbname'];
		return $db;
	}
	public function insert_id() {
		return $this->fastValue('SELECT @@IDENTITY as last_insert_id');
	}
	public function listSchema() {
		return $this->falseLine("
			SELECT 
				tab.name AS table_name, 
				col.name AS column_name, 
				col.colid AS column_id, 
				typ.name AS data_type,
				col.length AS length, 
				col.prec AS prec,
				col.scale AS scale, 
				com.text AS default_value, 
				obj.name AS default_cons_name
			FROM systypes typ 
			INNER JOIN syscolumns col ON typ.xusertype = col.xusertype 
			INNER JOIN sysobjects tab ON col.id = tab.id 
			LEFT OUTER JOIN syscomments com ON col.cdefault = com.id AND com.colid = 1
			INNER JOIN sysobjects obj ON com.id = obj.id 
			WHERE (tab.xtype = 'U')
			ORDER BY tab.name, col.colid
		");
	}
	public function showDatabases() {
		return $this->query_all('
			SELECT
				s.CATALOG_NAME [SCHEMA],
				s.SCHEMA_NAME [DOMAIN],
				s.SCHEMA_OWNER [OWNER], 
				s.DEFAULT_CHARACTER_SET_NAME,
				NULL DEFAULT_COLLATION_NAME,
				s.DEFAULT_CHARACTER_SET_CATALOG, 
				s.DEFAULT_CHARACTER_SET_SCHEMA,
				NULL SQL_PATH
			FROM INFORMATION_SCHEMA.SCHEMATA s
			ORDER BY s.CATALOG_NAME
		');
	}
	public function showTables($db = null) {
		return $this->query_all('
			SELECT 
				t.TABLE_NAME [TABLE], 
				t.TABLE_CATALOG [DOMAIN], 
				t.TABLE_SCHEMA [SCHEMA], 
				t.TABLE_TYPE [TYPE],

				NULL ENGINE, 
				NULL VERSION, 
				NULL ROW_FORMAT, 
				NULL TABLE_ROWS [ROWS], 
				NULL AVG_ROW_LENGTH, 
				NULL DATA_LENGTH, 
				NULL MAX_DATA_LENGTH, 
				NULL INDEX_LENGTH, 
				NULL DATA_FREE, 
				NULL AUTO_INCREMENT, 
				NULL CREATE_TIME, 
				NULL UPDATE_TIME, 
				NULL CHECK_TIME, 
				NULL TABLE_COLLATION COLLATION, 
				NULL CHECKSUM, 
				NULL CREATE_OPTIONS, 
				NULL TABLE_COMMENT [COMMENT]
			FROM INFORMATION_SCHEMA.TABLES 
			WHERE TABLE_TYPE="BASE TABLE" AND t.TABLE_SCHEMA = ' . $this->quote($this->getDb($db)) . '
			ORDER BY t.TABLE_NAME
		');
	}
	public function showViews($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
		return $this->query_all('
			SELECT 
				t.TABLE_NAME [TABLE], 
				t.TABLE_CATALOG [DOMAIN], 
				t.TABLE_SCHEMA [SCHEMA], 
				t.TABLE_TYPE [TYPE],

				NULL ENGINE, 
				NULL VERSION, 
				NULL ROW_FORMAT, 
				NULL TABLE_ROWS [ROWS], 
				NULL AVG_ROW_LENGTH, 
				NULL DATA_LENGTH, 
				NULL MAX_DATA_LENGTH, 
				NULL INDEX_LENGTH, 
				NULL DATA_FREE, 
				NULL AUTO_INCREMENT, 
				NULL CREATE_TIME, 
				NULL UPDATE_TIME, 
				NULL CHECK_TIME, 
				NULL TABLE_COLLATION COLLATION, 
				NULL CHECKSUM, 
				NULL CREATE_OPTIONS, 
				NULL TABLE_COMMENT [COMMENT]
			FROM INFORMATION_SCHEMA.TABLES 
			WHERE TABLE_TYPE="VIEW" AND t.TABLE_SCHEMA = ' . $this->quote($this->getDb($db)) . '
			ORDER BY t.TABLE_NAME
		');
	}
	public function showFunctions($db = null) {
	}
	public function showProcedures($db = null) {
	}
	public function showEvents($db = null) {
	}
	public function showAllObjects($db = null) {
	}
}
