<?php
/*
 * common
 * directories:
 * /path_cache
 * ./dsn
 * ./querys
 * ./hash_query
 * ./fields
 * field_name.json when expression
 * field_name.json.link
 * ./depends_on/links
 * ./used_by/links
 * auto_config.json
 * user_defined.json
 * ./schemas
 * ./schema_name
 * auto_config.json
 * user_defined.json
 * ./tables
 * ./table_name
 * auto_config.json
 * user_defined.json
 * ./fields
 * field_name.json
 * ./depends_on/links
 * ./used_by/links
 * ./views
 * ./view_name
 * auto_config.json
 * user_defined.json
 * ./fields
 * field_name.json when expression
 * field_name.json.link
 * ./depends_on/links
 * ./used_by/links
 * ./procedures
 * ./procedure_name
 * auto_config.json
 * user_defined.json
 * ./fields
 * 1.json parameter 1
 * 2.json file parameter 2
 * ./depends_on/links
 * ./used_by/links
 * ./functions
 * ./function_name
 * auto_config.json
 * user_defined.json
 * ./fields
 * return.json file return
 * 1.json file parameter 1
 * 2.json file parameter 2
 * ./depends_on/links
 * ./used_by/links
 * ./events
 * ./event_name
 * auto_config.json
 * user_defined.json
 * ./depends_on/links
 * ./used_by/links
 */
/* mysql */
{
    $details[] = array(
        'type' => 'conn',
        'dtCol' => '0000-00-00 00:00:00',
        'database' => 'MARIADB', /*@@version_comment*/
	'version' => '10.1.0', /*@@version*/
	'os' => 'Linux',/*@@version_compile_os*/
	'os_bits' => 'x86_64',/*@@version_compile_machine*/
	
	'variables' => array( /*SHOW VARIABLES*/
		'var' => 'value'
        ),
        'engine' => 'MyISAM',
        'engines' => array( /*SHOW ENGINES*/
		'InnoDB' => array(
                'support' => 'NO',
                'Comment' => 'Percona-XtraDB, Supports transactions,.',
                'Transactions' => null,
                'XA' => null,
                'Savepoints' => null
            ),
            'CSV' => array(
                'support' => 'YES',
                'Comment' => 'CSV storage engine',
                'Transactions' => 'NO',
                'XA' => 'NO',
                'Savepoints' => 'NO'
            ),
            'PERFORMANCE_SCHEMA' => array(
                'support' => 'YES',
                'Comment' => 'Performance Schema',
                'Transactions' => 'NO',
                'XA' => 'NO',
                'Savepoints' => 'NO'
            ),
            'MyISAM' => array(
                'support' => 'DEFAULT',
                'Comment' => 'MyISAM storage engine',
                'Transactions' => 'NO',
                'XA' => 'NO',
                'Savepoints' => 'NO'
            ),
            'MRG_MyISAM' => array(
                'support' => 'YES',
                'Comment' => 'Collection of identical MyISAM tables',
                'Transactions' => 'NO',
                'XA' => 'NO',
                'Savepoints' => 'NO'
            ),
            'MEMORY' => array(
                'support' => 'YES',
                'Comment' => 'Hash based, stored in memory, useful...',
                'Transactions' => 'NO',
                'XA' => 'NO',
                'Savepoints' => 'NO'
            ),
            'Aria' => array(
                'support' => 'YES',
                'Comment' => 'Crash-safe tables with MyISAM heritage',
                'Transactions' => 'NO',
                'XA' => 'NO',
                'Savepoints' => 'NO'
            )
        ),
        'plugins' => array(/*SHOW PLUGINS*/
		'InnoDB' => array(
                'status' => 'DISABLED',
                'type' => 'STORAGE ENGINE',
                'library' => null,
                'license' => 'GPL'
            )
        ),
        'privileges' => array(/*SHOW PRIVILEGES*/
		'Alter' => array(
                'context' => 'Tables',
                'comment' => 'To alter the table'
            )
        ),
        'status' => array(/*SHOW STATUS*/
		'Aborted_clients' => 0
        ),
        'users' => array(/*select * from mysql.user*/
		'`user`@`host`' => array(
                'Host,User,Password,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Reload_priv,Shutdown_priv,Process_priv,File_priv,Grant_priv,References_priv,Index_priv,Alter_priv,Show_db_priv,Super_priv,Create_tmp_table_priv,Lock_tables_priv,Execute_priv,Repl_slave_priv,Repl_client_priv,Create_view_priv,Show_view_priv,Create_routine_priv,Alter_routine_priv,Create_user_priv,Event_priv,Trigger_priv,Create_tablespace_priv,ssl_type,ssl_cipher,x509_issuer,x509_subject,max_questions,max_updates,max_connections,max_user_connections,plugin,authentication_string,password_expired,is_role',
                'grants' => array(
                    '*.*' => "GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' IDENTIFIED BY PASSWORD '*1EE6184E16C2569F7E9CAEFB5A23B6010CDAAE3E' WITH GRANT OPTION"
                )
            )
        )
    );
}
{
    $details[] = array(
        'type' => 'schema',
        'dtCol' => '0000-00-00 00:00:00',
        'charset' => '',
        'collation' => ''
    );
}
{
    $details = array(
        'type' => 'field',
        'position' => 1,
        'name' => 'name',
        'refer' => array(
            'type' => 'table', /*table|view|function|expression*/
		'schema' => 'schema_name',
            'name' => 'tbl_name'
        ),
        'properties_main' => array( /*idem properties writed by user and will replace properties*/
		'label' => 'name'
        ),
        'properties' => array( /*properties is build automatically by meta_data*/
		'Element' => 'ElementNumber',
            'type' => 'Number',
            'conn' => null,
            'sql' => null,
            'fields' => null,
            'order' => null,
            'separator' => null,
            'groupSource' => null, // Fields names array or string,string which build the $value option
            'source' => null,
            'saveTo' => null
        ),
        'meta_data' => array(
            'charset' => '',
            'collation' => '',
            'comment' => '',
            'data_type' => 'int',
            'default_value' => '0',
            'isAutoIncrement' => false,
            'isBinary' => false,
            'isUnique' => false,
            'isUnsigned' => false,
            'length' => 0,
            'nullable' => false,
            'precision' => 11,
            'scale' => 0,
            'zeroFill' => false
        )
    );
}
{
    $details = array(
        'type' => 'parameter',
        'position' => 1,
        'name' => 'name',
        'refer' => array(
            'type' => 'function', /*function|procedure*/
		'schema' => 'schema_name',
            'name' => 'fn_name'
        ),
        'properties_main' => array( /*idem properties writed by user and will replace properties*/
		'label' => 'name'
        ),
        'properties' => array( /*properties is build automatically by meta_data*/
		'Element' => 'ElementNumber',
            'type' => 'Number',
            'conn' => null,
            'sql' => null,
            'fields' => null,
            'order' => null,
            'separator' => null,
            'groupSource' => null, // Fields names array or string,string which build the $value option
            'source' => null,
            'saveTo' => null
        ),
        'meta_data' => array(
            'charset' => '',
            'collation' => '',
            'comment' => '',
            'data_type' => 'int',
            'default_value' => '0',
            'isAutoIncrement' => false,
            'isBinary' => false,
            'isUnique' => false,
            'isUnsigned' => false,
            'length' => 0,
            'nullable' => false,
            'precision' => 11,
            'scale' => 0,
            'zeroFill' => false
        )
    );
}
{
    $details = array(
        'type' => 'table',
        'name' => 'name',
        'dtCol' => '0000-00-00 00:00:00',
        'hash' => 'AF09012349837495682374GFG3451112',
        'parts' => array(
            'CREATE' => '[TEMPORARY] TABLE [IF NOT EXISTS]', /*[ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] select_expr [, select_expr ...]*/
		'NAME' => 'tbl_name',
            'DEFINITION' => 'create_definition',
            'OPTIONS' => 'table_options',
            'PARTITION' => 'partition_options',
            'ENDING' => '[IGNORE | REPLACE] [AS] query_expression'
        ),
        'temporary' => false,
        'if not exists' => false,
        'fields' => array(/*auto_config.json*/
		'name' => array(
                'position' => 1,
                'type' => 'table|view|function|procedure|trigger|event'
            )
            /* ... */
        ),
        'fields' => array(/*user_defined.json*/
		'name' => array(
                'properties_main' => array()
            )
            /* ... */
        ),
        'options' => array(),
        'partition' => array(),
        'charset' => '',
        'collation' => '',
        'engine' => 'MyISAM',
        'comment' => '',
        'keys' => array(
            'PRIMARY' => 'field,...',
            'UNIQUE' => array(
                'name' => array(
                    'fields' => 'fields'
                )
            ),
            'INDEX' => 'idem unique'
        ),
        '',
        'DLL' => ''
    );
}
{$details[]=array('type'=>'query',
	'dtCol'=>'0000-00-00 00:00:00',
	'hash'=>'AF09012349837495682374GFG3451112',
	'parts'=>array(
		'SELECT'=>'select_syntax', /*[ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] select_expr [, select_expr ...]*/
		'FROM'=>'join_syntax',
		'PARTITION'=>partition_syntax,
		'WHERE'=>'where_condition',
		'GROUP BY'=>'group_by_expression', /*{col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]*/
		'HAVING'=>'where_condition',
		'ORDER BY'=>'order_by_expression',
		'LIMIT'=>'limit_expression',
		'PROCEDURE'=>'procedure_expression',
		'INTO'=>'select_into_syntax',
		'ENDING'=>'ending_syntax',
	),
	'fields'=>array(), /*idem table*/
	'sql'=>'table',
	'sqlFull'=>'select * from table', /*change names when repeated*/
	'comment'=>'',
	'DML'=>$sqlFull,
);
}
{$details[]=array('type'=>'function',
	'dtCol'=>'0000-00-00 00:00:00',
	'hash'=>'AF09012349837495682374GFG3451112',
	'parts'=>array(
		'SELECT'=>'select_syntax', /*[ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] select_expr [, select_expr ...]*/
		'FROM'=>'join_syntax',
		'PARTITION'=>partition_syntax,
		'WHERE'=>'where_condition',
		'GROUP BY'=>'group_by_expression', /*{col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]*/
		'HAVING'=>'where_condition',
		'ORDER BY'=>'order_by_expression',
		'LIMIT'=>'limit_expression',
		'PROCEDURE'=>'procedure_expression',
		'INTO'=>'select_into_syntax',
		'ENDING'=>'ending_syntax',
	),
	'fields'=>array(), /*idem table*/
	'sql'=>'table',
	'sqlFull'=>'select * from table', /*change names when repeated*/
	'comment'=>'',
	'DML'=>$sqlFull,
);
}
{$details[]=array('type'=>'procedure',
	'dtCol'=>'0000-00-00 00:00:00',
	'hash'=>'AF09012349837495682374GFG3451112',
	'parts'=>array(
		'SELECT'=>'select_syntax', /*[ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] select_expr [, select_expr ...]*/
		'FROM'=>'join_syntax',
		'PARTITION'=>partition_syntax,
		'WHERE'=>'where_condition',
		'GROUP BY'=>'group_by_expression', /*{col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]*/
		'HAVING'=>'where_condition',
		'ORDER BY'=>'order_by_expression',
		'LIMIT'=>'limit_expression',
		'PROCEDURE'=>'procedure_expression',
		'INTO'=>'select_into_syntax',
		'ENDING'=>'ending_syntax',
	),
	'fields'=>array(), /*idem table*/
	'sql'=>'table',
	'sqlFull'=>'select * from table', /*change names when repeated*/
	'comment'=>'',
	'DML'=>$sqlFull,
);
}
{$details[]=array('type'=>'event',
	'dtCol'=>'0000-00-00 00:00:00',
	'hash'=>'AF09012349837495682374GFG3451112',
	'parts'=>array(
		'SELECT'=>'select_syntax', /*[ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] select_expr [, select_expr ...]*/
		'FROM'=>'join_syntax',
		'PARTITION'=>partition_syntax,
		'WHERE'=>'where_condition',
		'GROUP BY'=>'group_by_expression', /*{col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]*/
		'HAVING'=>'where_condition',
		'ORDER BY'=>'order_by_expression',
		'LIMIT'=>'limit_expression',
		'PROCEDURE'=>'procedure_expression',
		'INTO'=>'select_into_syntax',
		'ENDING'=>'ending_syntax',
	),
	'fields'=>array(), /*idem table*/
	'sql'=>'table',
	'sqlFull'=>'select * from table', /*change names when repeated*/
	'comment'=>'',
	'DML'=>$sqlFull,
);
}
{$details[]=array('type'=>'udf',
	'dtCol'=>'0000-00-00 00:00:00',
	'hash'=>'AF09012349837495682374GFG3451112',
	'parts'=>array(
		'SELECT'=>'select_syntax', /*[ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] select_expr [, select_expr ...]*/
		'FROM'=>'join_syntax',
		'PARTITION'=>partition_syntax,
		'WHERE'=>'where_condition',
		'GROUP BY'=>'group_by_expression', /*{col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]*/
		'HAVING'=>'where_condition',
		'ORDER BY'=>'order_by_expression',
		'LIMIT'=>'limit_expression',
		'PROCEDURE'=>'procedure_expression',
		'INTO'=>'select_into_syntax',
		'ENDING'=>'ending_syntax',
	),
	'fields'=>array(), /*idem table*/
	'sql'=>'table',
	'sqlFull'=>'select * from table', /*change names when repeated*/
	'comment'=>'',
	'DML'=>$sqlFull,
);
}


/*select_syntax:
	SELECT
		[ALL | DISTINCT | DISTINCTROW ]
		[HIGH_PRIORITY]
		[STRAIGHT_JOIN]
		[SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
		[SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
		select_expr [, select_expr ...]
	[FROM table_references
	[PARTITION partition_list]
	[WHERE where_condition]
	[GROUP BY {col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]]
	[HAVING where_condition]
	[ORDER BY {col_name | expr | position} [ASC | DESC], ...]
	[LIMIT {[offset,] row_count | row_count OFFSET offset}]
	[PROCEDURE procedure_name(argument_list)]
	[INTO OUTFILE 'file_name'
	[CHARACTER SET charset_name]
	export_options
	| INTO DUMPFILE 'file_name'
	| INTO var_name [, var_name]]
	[FOR UPDATE | LOCK IN SHARE MODE]]
*/
/*select_into_syntax:
	SELECT ... INTO var_list
	SELECT ... INTO OUTFILE
	SELECT ... INTO DUMPFILE
*/
/*union_syntax:
	SELECT ...
	UNION [ALL | DISTINCT] SELECT ...
	[UNION [ALL | DISTINCT] SELECT ...]
*/
/*join_syntax:
	table_references:
		escaped_table_reference [, escaped_table_reference] ...

	escaped_table_reference:
		table_reference
	  | { OJ table_reference }

	table_reference:
		table_factor
	  | join_table

	table_factor:
		tbl_name [PARTITION (partition_names)]
			[[AS] alias] [index_hint_list]
	  | table_subquery [AS] alias
	  | ( table_references )

	join_table:
		table_reference [INNER | CROSS] JOIN table_factor [join_condition]
	  | table_reference STRAIGHT_JOIN table_factor
	  | table_reference STRAIGHT_JOIN table_factor ON conditional_expr
	  | table_reference {LEFT|RIGHT} [OUTER] JOIN table_reference join_condition
	  | table_reference NATURAL [{LEFT|RIGHT} [OUTER]] JOIN table_factor

	join_condition:
		ON conditional_expr
	  | USING (column_list)

	index_hint_list:
		index_hint [, index_hint] ...

	index_hint:
		USE {INDEX|KEY}
		  [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
	  | IGNORE {INDEX|KEY}
		  [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
	  | FORCE {INDEX|KEY}
		  [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)

	index_list:
		index_name [, index_name] ...
*/

/*CREATE TABLE Syntax:
CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
    (create_definition,...)
    [table_options]
    [partition_options]

CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
    [(create_definition,...)]
    [table_options]
    [partition_options]
    [IGNORE | REPLACE]
    [AS] query_expression

CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
    { LIKE old_tbl_name | (LIKE old_tbl_name) }
*/
/*create_definition:
    col_name column_definition
  | [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name,...)
      [index_option] ...
  | {INDEX|KEY} [index_name] [index_type] (index_col_name,...)
      [index_option] ...
  | [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY]
      [index_name] [index_type] (index_col_name,...)
      [index_option] ...
  | {FULLTEXT|SPATIAL} [INDEX|KEY] [index_name] (index_col_name,...)
      [index_option] ...
  | [CONSTRAINT [symbol]] FOREIGN KEY
      [index_name] (index_col_name,...) reference_definition
  | CHECK (expr)
*/
/*column_definition:
    data_type [NOT NULL | NULL] [DEFAULT default_value]
      [AUTO_INCREMENT] [UNIQUE [KEY]] [[PRIMARY] KEY]
      [COMMENT 'string']
      [COLUMN_FORMAT {FIXED|DYNAMIC|DEFAULT}]
      [STORAGE {DISK|MEMORY|DEFAULT}]
      [reference_definition]
  | data_type [GENERATED ALWAYS] AS (expression)
      [VIRTUAL | STORED] [NOT NULL | NULL]
      [UNIQUE [KEY]] [[PRIMARY] KEY]
      [COMMENT 'string']
*/
/*data_type:
    BIT[(length)]
  | TINYINT[(length)] [UNSIGNED] [ZEROFILL]
  | SMALLINT[(length)] [UNSIGNED] [ZEROFILL]
  | MEDIUMINT[(length)] [UNSIGNED] [ZEROFILL]
  | INT[(length)] [UNSIGNED] [ZEROFILL]
  | INTEGER[(length)] [UNSIGNED] [ZEROFILL]
  | BIGINT[(length)] [UNSIGNED] [ZEROFILL]
  | REAL[(length,decimals)] [UNSIGNED] [ZEROFILL]
  | DOUBLE[(length,decimals)] [UNSIGNED] [ZEROFILL]
  | FLOAT[(length,decimals)] [UNSIGNED] [ZEROFILL]
  | DECIMAL[(length[,decimals])] [UNSIGNED] [ZEROFILL]
  | NUMERIC[(length[,decimals])] [UNSIGNED] [ZEROFILL]
  | DATE
  | TIME[(fsp)]
  | TIMESTAMP[(fsp)]
  | DATETIME[(fsp)]
  | YEAR
  | CHAR[(length)]
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | VARCHAR(length)
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | BINARY[(length)]
  | VARBINARY(length)
  | TINYBLOB
  | BLOB[(length)]
  | MEDIUMBLOB
  | LONGBLOB
  | TINYTEXT
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | TEXT[(length)]
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | MEDIUMTEXT
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | LONGTEXT
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | ENUM(value1,value2,value3,...)
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | SET(value1,value2,value3,...)
      [CHARACTER SET charset_name] [COLLATE collation_name]
  | JSON
  | spatial_type
*/
/*index_col_name:
    col_name [(length)] [ASC | DESC]
*/
/*index_type:
    USING {BTREE | HASH}
*/
/*index_option:
    KEY_BLOCK_SIZE [=] value
  | index_type
  | WITH PARSER parser_name
  | COMMENT 'string'
*/
/*reference_definition:
    REFERENCES tbl_name (index_col_name,...)
      [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
      [ON DELETE reference_option]
      [ON UPDATE reference_option]
*/
/*reference_option:
    RESTRICT | CASCADE | SET NULL | NO ACTION | SET DEFAULT
*/
/*table_options:
    table_option [[,] table_option] ...
*/
/*table_option:
    AUTO_INCREMENT [=] value
  | AVG_ROW_LENGTH [=] value
  | [DEFAULT] CHARACTER SET [=] charset_name
  | CHECKSUM [=] {0 | 1}
  | [DEFAULT] COLLATE [=] collation_name
  | COMMENT [=] 'string'
  | COMPRESSION [=] {'ZLIB'|'LZ4'|'NONE'}
  | CONNECTION [=] 'connect_string'
  | {DATA|INDEX} DIRECTORY [=] 'absolute path to directory'
  | DELAY_KEY_WRITE [=] {0 | 1}
  | ENCRYPTION [=] {'Y' | 'N'}
  | ENGINE [=] engine_name
  | INSERT_METHOD [=] { NO | FIRST | LAST }
  | KEY_BLOCK_SIZE [=] value
  | MAX_ROWS [=] value
  | MIN_ROWS [=] value
  | PACK_KEYS [=] {0 | 1 | DEFAULT}
  | PASSWORD [=] 'string'
  | ROW_FORMAT [=] {DEFAULT|DYNAMIC|FIXED|COMPRESSED|REDUNDANT|COMPACT}
  | STATS_AUTO_RECALC [=] {DEFAULT|0|1}
  | STATS_PERSISTENT [=] {DEFAULT|0|1}
  | STATS_SAMPLE_PAGES [=] value
  | TABLESPACE tablespace_name [STORAGE {DISK|MEMORY|DEFAULT}]
  | UNION [=] (tbl_name[,tbl_name]...)
*/
/*partition_options:
    PARTITION BY
        { [LINEAR] HASH(expr)
        | [LINEAR] KEY [ALGORITHM={1|2}] (column_list)
        | RANGE{(expr) | COLUMNS(column_list)}
        | LIST{(expr) | COLUMNS(column_list)} }
    [PARTITIONS num]
    [SUBPARTITION BY
        { [LINEAR] HASH(expr)
        | [LINEAR] KEY [ALGORITHM={1|2}] (column_list) }
      [SUBPARTITIONS num]
    ]
    [(partition_definition [, partition_definition] ...)]
*/
/*partition_definition:
    PARTITION partition_name
        [VALUES
            {LESS THAN {(expr | value_list) | MAXVALUE}
            |
            IN (value_list)}]
        [[STORAGE] ENGINE [=] engine_name]
        [COMMENT [=] 'string' ]
        [DATA DIRECTORY [=] 'data_dir']
        [INDEX DIRECTORY [=] 'index_dir']
        [MAX_ROWS [=] max_number_of_rows]
        [MIN_ROWS [=] min_number_of_rows]
        [TABLESPACE [=] tablespace_name]
        [(subpartition_definition [, subpartition_definition] ...)]
*/
/*subpartition_definition:
    SUBPARTITION logical_name
        [[STORAGE] ENGINE [=] engine_name]
        [COMMENT [=] 'string' ]
        [DATA DIRECTORY [=] 'data_dir']
        [INDEX DIRECTORY [=] 'index_dir']
        [MAX_ROWS [=] max_number_of_rows]
        [MIN_ROWS [=] min_number_of_rows]
        [TABLESPACE [=] tablespace_name]
*/
/*query_expression:
	SELECT ...   (Some valid select or union statement)
*/