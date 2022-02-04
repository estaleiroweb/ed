<?php
class Conn_mysqli_details_function extends Conn_mysqli_details_main
{

    protected function getStatus()
    {
        static $obj = false;
        if ($this->version < 5) {
            $view = array();
            $this->setError("Database version doesn't support Functions");
        }
        if ($obj === false) {
            $obj = array();
            $line = $this->conn->fastLine("SELECT * FROM information_schema.`ROUTINES` WHERE `ROUTINE_TYPE`='FUNCTION' AND `ROUTINE_SCHEMA`='{$this->conn->escape_string($this->readonly['db'])}' AND `ROUTINE_NAME`='{$this->conn->escape_string($this->readonly['name'])}'");
            if ($line) {
                $obj = line;
                $obj['dtUpdate'] = $line['LAST_ALTERED'];
            } else
                $obj = $this->setError("Function doesn't exist");
            /*
             * CREATE TABLE information_schema.ROUTINES (
             * SPECIFIC_NAME varchar(64) NOT NULL DEFAULT '',
             * ROUTINE_CATALOG varchar(512) DEFAULT NULL,
             * ROUTINE_SCHEMA varchar(64) NOT NULL DEFAULT '',
             * ROUTINE_NAME varchar(64) NOT NULL DEFAULT '',
             * ROUTINE_TYPE varchar(9) NOT NULL DEFAULT '',
             * DTD_IDENTIFIER varchar(64) DEFAULT NULL,
             * ROUTINE_BODY varchar(8) NOT NULL DEFAULT '',
             * ROUTINE_DEFINITION longtext DEFAULT NULL,
             * EXTERNAL_NAME varchar(64) DEFAULT NULL,
             * EXTERNAL_LANGUAGE varchar(64) DEFAULT NULL,
             * PARAMETER_STYLE varchar(8) NOT NULL DEFAULT '',
             * IS_DETERMINISTIC varchar(3) NOT NULL DEFAULT '',
             * SQL_DATA_ACCESS varchar(64) NOT NULL DEFAULT '',
             * SQL_PATH varchar(64) DEFAULT NULL,
             * SECURITY_TYPE varchar(7) NOT NULL DEFAULT '',
             * CREATED datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
             * LAST_ALTERED datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
             * SQL_MODE varchar(8192) NOT NULL DEFAULT '',
             * ROUTINE_COMMENT varchar(64) NOT NULL DEFAULT '',
             * DEFINER varchar(77) NOT NULL DEFAULT '',
             * CHARACTER_SET_CLIENT varchar(32) NOT NULL DEFAULT '',
             * COLLATION_CONNECTION varchar(32) NOT NULL DEFAULT '',
             * DATABASE_COLLATION varchar(32) NOT NULL DEFAULT ''
             * )
             */
        }
        return $obj;
    }

    protected function rebuildObj()
    {
        $s = $this->getStatus();
        $ddl = 'CREATE DEFINER=\'' . str_replace('@', "'@'", $s['DEFINER']) . '\' SQL SECURITY ' . $s['SECURITY_TYPE'] . " VIEW `$this->readonly['db']`.`$this->readonly['name']` AS \n" . $s['VIEW_DEFINITION'] . ($s['CHECK_OPTION'] == 'NONE' ? '' : " \nWITH {$s['CHECK_OPTION']} CHECK OPTION");
        /*
         * CREATE [OR REPLACE]
         * [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
         * [DEFINER = { user | CURRENT_USER }]
         * [SQL SECURITY { DEFINER | INVOKER }]
         * VIEW view_name [(column_list)]
         * AS select_statement
         * [WITH [CASCADED | LOCAL] CHECK OPTION]
         */
        if (@$this->readonly['DDL'] == $ddl)
            return false;
        $this->readonly['isUpdateble'] = $s['IS_UPDATABLE'];
        $this->readonly['DDL'] = $ddl;
        $this->readonly['sql'] = $s['VIEW_DEFINITION'];
        $this->readonly['definer'] = $s['DEFINER'];
        $this->readonly['securityType'] = $s['SECURITY_TYPE'];
        $this->readonly['checkOption'] = $s['CHECK_OPTION'];
        $this->readonly['charsetClient'] = $s['CHARACTER_SET_CLIENT'];
        $this->readonly['collationConnection'] = $s['COLLATION_CONNECTION'];
        // Fields
        // Dependences
        // Constraints
        return true;
    }
}