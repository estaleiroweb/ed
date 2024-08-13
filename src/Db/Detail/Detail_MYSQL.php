<?php

namespace EstaleiroWeb\ED\Db\Detail;

class Detail_MYSQL extends Detail {
	protected $erTable = '(`[^`]+`|\b\w+\b)';
	protected $reserved_words=[
		'DQL'=>'SHOW|CALL|EXPLAIN|DESCRIBE|HELP',//Data Query Language
		'DDL'=>'CREATE|DROP|ALTER|TRUNCATE|COMMENT|RENAME|(UN)?INSTALL\s+PLUGIN',//Data Definition Language
		'DML'=>'INSERT|UPDATE|UPDATETEXT|DELETE|REPLACE|MERGE|WRITETEXT|LOAD|CALL|EXPLAIN|DESCRIBE|HELP|HANDLER|DO|DEALLOCATE|PREPARE|EXECUTE|USE',//Data Manipulation Language
		'DCL'=>'GRANT|REVOKE|FLUSH',//Data Control Language
		'DTL'=>'START\s+TRANSACTION|COMMIT|SAVEPOINT|ROLLBACK|RELEASE\s+SAVEPOINT|(?:UN)?LOCK\s+TABLES?|SET(?:\s+(?:GLOBAL|SESSION))?\s+TRANSACTION', //TCL - Transaction Control Language|DTL - Data Transaction Language
		'DLS'=>'LOAD\s+DATA',//Data loading statements
		'ADS'=>'(ANALYZE|CHECK|OPTIMIZE|REPAIR|CHECKSUM)\s+TABLE|CACHE\s+INDEX|RESET\s+QUERY\s+CACHE|KILL|SHUTDOWN',//Administrative statements
		'RCS'=>'(START|STOP|RESET)\s+SLAVE|CHANGE\s+MASTER',//Replication control statements
		'FCS'=>'CASE|IF|ITERATE|LEAVE|RETURN|(?:\w+:)?(LOOP|REPEAT|WHILE)',
		'PRS'=>'SET|IF|WHILE|SWITCH|DO|BEGIN|END',//Program statements
	];

	public function __construct($conn, $query) {
		$er='`[^`]+`|\b\w+\b|'.self::DELIMIT.'\d+'.self::DELIMIT;
		$this->erAlias='(?:(?<db>'.$er.')\.)?(?<tbl>'.$er.')(?:\s+(?:as\s+)?(?<alias>'.$er.'))?';
		parent::__construct($conn, $query);
	}
}
