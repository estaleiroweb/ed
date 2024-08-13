<?php
/**
  * $connFrom='connection_from';
  * $connTarget='connection_to'; // optional
  * $dbs=array('Database_From'=>'Database_To',); // $dbs=array('Database_From',);
  * $debug=false;
  * $d=new Dump($connFrom,$dbs,$connTarget,$debug);
  * $d->all(); //$d->let('privileges,dbs,tables,data,views,functions,procedures,triggers,events');
  **/
class Dump {
	private $protect=array(
		'conn'=>'','connTarget'=>'',
		'cmdInsert'=>'INSERT IGNORE', //REPLACE
		'maxRows'=>200,
		'file'=>'','compactFile'=>true,'utcTimeFileName'=>true,'timeStampFormat'=>"_%Y%m%d%H",'last'=>'',//last="now - 1 month"
		'dbs'=>false,'dropDataBase'=>true,'dropTable'=>true,'ifNotExistsTable'=>false, 'ifNotExists'=>false,
		'triggers'=>false,'functions'=>false,'tables'=>false,'views'=>false,'events'=>false,'procedures'=>false,'variables'=>true,
		'databases'=>array(),
		'er_tablesFilter'=>'/.*/',
		'er_viewsFilter'=>'/.*/',
		'er_functionsFilter'=>'/.*/',
		'er_proceduresFilter'=>'/.*/',
		'er_triggersFilter'=>'/.*/',
		'er_eventsFilter'=>'/.*/',
	);
	private $mysqlWordReserved=array(
		'abs','acos','action','add','adddate','all','alter','analyze','and','as','asc','ascii','asin','atan','atan2','avg',
		'before','benchmark','between','bigint','bin','binary','bit','bit_and','bit_count','bit_or','blob','both','by','call',
		'cascade','case','ceil','ceiling','change','char','char_length','character','character_length','check','coalesce',
		'collate','column','columns','concat','constraint','conv','convert','cos','cot','count','create','cross','curdate',
		'current','current_date','current_time','current_timestamp','current_user','database','databases','date','date_add',
		'date_format','date_sub','day_hour','day_microsecond','day_minute','day_second','dayname','dayofmonth','dayofweek','dec',
		'decimal','decode','default','degrees','delayed','delete','desc','describe','distinct','distinctrow','div','double',
		'drop','dual','else','elt','enclosed','encode','encrypt','enum','escaped','exists','exp','explain','export_set','extract',
		'field','fields','find_in_set','float','float4','float8','floor','for','force','foreign','format','from','from_days',
		'from_unixtime','fulltext','get_lock','grant','greatest','group','having','hex','high_priority','hour','hour_microsecond',
		'hour_minute','hour_second','if','ifnull','ignore','in','index','infile','inner','insert','instr','int','int1','int2',
		'int3','int4','int8','integer','interval','into','is','isnull','join','key','keys','kill','last_inset_id','lcase',
		'leading','least','left','length','like','limit','lines','load','load_file','localtime','localtimestamp','locate',
		'lock','log','log10','long','longblob','longtext','low_priority','lower','lpad','ltrim','make_set','match','max','md5',
		'mediumblob','mediumint','mediumtext','mid','middleint','min','minute','minute_microsecond','minute_second','mod',
		'mod','month','monthname','natural','no','no_write_to_binlog','not','now','null','numeric','oct','octet_length','on',
		'optimize','option','optionally','or','order','outer','outfile','password','period_add','period_diff','pi','position',
		'pow','power','precision','primary','privileges','procedure','purge','quarter','radians','raid0','rand','read','real',
		'references','regexp','release_lock','rename','repeat','replace','require','restrict','reverse','revoke','right',
		'rlike','round','rpad','rtrim','sec_to_time','second','second_microsecond','select','separator','session_user','set',
		'show','sign','sin','smallint','soname','soundex','space','spatial','sql_big_result','sql_calc_found_rows','sql_small_result',
		'sqrt','ssl','starting','std','stddev','straight_join','strcmp','subdate','substring','substring_index','sum','sysdate',
		'system_user','table','tables','tan','terminated','text','then','time','time_format','time_to_sec','timestamp',
		'tinyblob','tinyint','tinytext','to','to_days','trailing','trim','truncate','ucase','union','unique','unix_timestamp',
		'unlock','unsigned','update','upper','usage','use','user','using','utc_date','utc_time','utc_timestamp','values',
		'varbinary','varchar','varcharacter','varying','verssion','week','weekday','when','where','with','write','x509',
		'xor','year','year_month','zerofill',
	);
	private $tempFile='';
	private $handle='';
	private $fullVerssion='';
	private $verssion=0;
	private $db=array();
	private $cache=array();
	private $extruct=array();
	private $replaceFrom=array();
	private $replaceTo=array();

	private $useBank='';
	private $isBeginVariables=false;
	private $fileCreated=0;
	private $erWR='';
	private $now='';
	private $error=array();
	private $errorLevel=0;
	private $erro_file=array();
	private $printFileName=0;
	private $privilegesPass=false;
	public $utcFileNameTemp='';
	public $debug,$hostname;
	
	public $log=''; //Nome do arquivo de log
	public $completeCicle=0;
	public $charset='';
	public $from;
	private $mail_error=array();
	private $mail_ok=array();
	private $headMail=array();
	private $connOld='';
	private $exeTime;
	private $fileOut=array();
	private $dataTables=array();
	private $renameTables=array();
	private $paramTables=false;
	private $paramData=false;
	private $erFullTable='(?:(?:`([^`]+)`|(\w+))\.)?(?:`([^`]+)`|(\w+))';
	public $showSqlScreen=null;
	public $elementOrder=array();

	/**
	 * Bacukup Data Bases
	 *
	 * @param string|array|object $conn Conexao source (veja Dsn)
	 * @param string|array $dbs Bancos de target Ex. "DB1,DB2" ou array("DB1","DB2") ou array("DB1"=>"Banco1","DB2"=>"Banco2")
	 * @param string|array|object $connTarget Conexao target (veja Dsn)
	 * @param boolean|integer $debug Fazer debug ou nao 1 degub inclusive as querys
	 */
	function __construct($conn=false,$dbs=array(),$connTarget=false,$debug=false){
		set_time_limit(0);
		@header("content-type: text/plain");
		$this->tempFile=tempnam('/var/tmp','dump_');
		`> "{$this->tempFile}"`;
		$this->exeTime=microtime(true);
		$this->hostname=trim(`hostname`);
		//$this->from="DUMP {$this->hostname} <intelig-datanoc-sistemas@inteligtelecom.com.br>";
		$this->from="DUMP {$this->hostname} <sistemas@intelignet.com.br>";
		$this->now=strtotime("now");
		$this->erWR="/^".implode("|",$this->mysqlWordReserved)."$/i";
		if ($conn) $this->conn=$conn;
		if ($dbs) $this->databases=$dbs;
		if ($connTarget) $this->connTarget=$connTarget;
		$this->debug=$debug || in_array("-d",$GLOBALS['argv']);
	}
	function __destruct(){
		$this->compactFile();
		`rm -f "{$this->tempFile}"`;
		$this->sendTime('Tempo Total',$this->exeTime);
		if ($this->erro_file) foreach($this->erro_file as $v) $this->setError("[FILE] $v",1);
		if (!$this->completeCicle) $this->setError('[CICLE] Ciclo nao completo',1);
		$this->sendMail();
		if($this->error) print "## ERROS/WARNINGS:\n{$this->getErrors()}\n";
	}
	function __get($nm){ if (isset($this->protect[$nm])) return $this->protect[$nm]; }
	function __set($nm,$v){
		if($nm=='file') {
			$this->printFileName=0;
			$this->modifyUTC($this->protect['timeStampFormat']);
		}elseif($nm=='timeStampFormat') $this->modifyUTC($v);
		elseif($nm=='conn' || $nm=='connTarget'){
			if (!$v) return;
			if (is_string($v)) $v=Conn::dsn($v);
			elseif (is_array($v)) $v=Conn::singleton($v);
			$err='';
			if (!@$v->conn || $err=@$v->error) $this->setError("[CONN] ".print_r(@$v->dsn,true)."$err",1);
			if($nm=='conn') {
				$this->connOld=$v;
				$this->getVerssion($v);
			}
		}
		$this->protect[$nm]=$v;
	}
	function getParameters(){ return $this->protect; }
	function all($item='privileges,dbs,tables,data,views,functions,procedures,triggers,events'){ $this->let($item);} 
	function let($item='tables,data,views,functions,procedures'){
		if(is_null($this->showSqlScreen)) $this->showSqlScreen=!($this->protect['connTarget'] || $this->protect['file']);

		$this->headMail[]="Do: $item";
		$ch=array('privileges','dbs','data','tables','views','functions','procedures','triggers','events');
		$aItem=array_intersect($ch,preg_split('/\s*[,;]\s*/',trim(strtolower($item))));
		
		if (!$aItem) return $this->setError('Selecione itens a coletar: '.implode(', ',$ch));
		if(!$this->beginVariables() || !$this->validDataBases()) return false;

		$this->paramData=in_array('data', $aItem);
		$this->paramTables=in_array('tables', $aItem);
	
		$this->pr("-- Getting Informations\n");
		foreach($aItem as $item) {
			$this->pr("-- ### $item...\n");
			$fn="getElement_$item";
			foreach($this->databases as $dbOld=>$dbNew) $this->$fn($dbOld);
		}

		$this->pr("-- Chekking Dependency...\n");
		while($this->getOrder_items());

		$this->pr("-- Putting...\n");
		$this->sendExtruct();
		$this->completeCicle=1;
	}
	
	function modifyUTC($mask){ $this->utcFileNameTemp=strftime($mask,$this->now);}
	private function sendTime($text,$time){
		$ts=round(microtime(true)-$time,2);
		$tm=round($ts/60,2);
		$text.=": {$ts}s {$tm}m";
		//$this->pr("$text\n");
		$this->setError($text);
	}
	private function getVerssion($conn){
		$res=$conn->query("select @@version");
		$this->fullVerssion=current($res->fetch_row());
		$res->close();
		$fn=explode('.',$this->fullVerssion);
		$this->verssion=(float)(array_shift($fn).".".implode('',$fn));
		$this->pr("Vesao Mysql: $this->verssion ($this->fullVerssion)\n");
	}
	private function getTables($db){
		static $tables=array();
		if(!@$tables[$db]) {
			$tables[$db]=array();
			$sql=($this->verssion<5)?"show tables from `$db`":"
				SELECT t.`TABLE_NAME` FROM information_schema.`TABLES` t
				WHERE t.`TABLE_SCHEMA`='$db' AND TRIM(t.TABLE_TYPE)='BASE TABLE'
				ORDER BY t.`TABLE_NAME`
			";
			$res=$this->protect['conn']->query($sql,false);
			while ($line=$res->fetch_row()) if(preg_match($this->protect['er_tablesFilter'],$line[0])) $tables[$db][]=$line[0];
			$res->close();
		}
		return $tables[$db];
	}
	private function isTmpTableDrop(){
		return $this->paramData && $this->paramTables && $this->dropTable;
	}
	private function getFullNameToTable($db,$tbl){
		if($this->isTmpTableDrop()) {
			$tbl2="__tmpDump_$tbl";
			$this->renameTables["`$db`.`$tbl`"]="`$db`.`$tbl2`";
			$tbl=$tbl2;
		}
		return "`$db`.`$tbl`";
	}
	private function useDB($db){
		static $lastDb='';

		if(!$db || $db==$lastDb) return '';
		$lastDb=$db;
		($dbInUse=@$this->databases[$db]) || ($dbInUse=$db);
		return "USE `{$dbInUse}`;";
	}

	private function beginVariables(){
		if ($this->isBeginVariables) return true;
		if(!$this->conn) {
			$this->setError("Nao existe conexao");
			return false;
		}
		if(!$this->charset) {
			$cs=$this->conn->get_charset();
			$this->charset=$cs->charset;
		}

		$this->isBeginVariables=true;
		$ok=$this->outTitle('-- Begin',array(
			"-- MySQL {$this->fullVerssion}",
			'SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT ;',
			'SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS ;',
			'SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION ;',
			"SET NAMES {$this->charset} ;",
			'SET @OLD_TIME_ZONE=@@TIME_ZONE ;',
			"SET TIME_ZONE='+00:00' ;",
			'SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 ;',
			'SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 ;',
			"SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' ;",
			"SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0;\n",
		));
		return true;
	}
	private function endVariables(){
		if ($this->isBeginVariables) {
			$this->isBeginVariables=false;
			$ok=$this->outTitle('-- End',array(
				'-- End',
				"SET TIME_ZONE=@OLD_TIME_ZONE ;",
				"SET SQL_MODE=@OLD_SQL_MODE ;",
				"SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS ;",
				"SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS ;",
				"SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT ;",
				"SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS ;",
				"SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION ;",
				"SET SQL_NOTES=@OLD_SQL_NOTES ;",
			));
			$this->useBank='';
		}
	}
	private function validDataBases(){
		$res=$this->protect['conn']->query("show databases");
		while ($line=$res->fetch_row()) $allDb[$line[0]]=$line[0];
		$res->close();
		if(is_string($this->databases)) $this->databases=preg_split('/\s*[,;]\s*/',trim($this->databases));
		if(!$this->databases) $this->databases=$allDb;

		$db=$this->replaceFrom=$this->replaceTo=array();
		foreach($this->databases as $dbOld=>$dbNew) {
			if(!$dbOld || is_numeric($dbOld)) $dbOld=$dbNew;
			if(!$dbNew || is_numeric($dbNew)) $dbNew=$dbOld;
			if(!@$allDb[$dbOld] || preg_match('/^(information_schema|mysql)$/',$dbOld)) continue;
			$db[$dbOld]=$dbNew;
			if($dbNew!=$dbOld) {
				$this->replaceFrom[]='/(`?)('.preg_quote($dbOld).')\1\./';
				$this->replaceTo[]="\\1$dbNew\\1.";
			}
		}
		if(!$db) {
			$this->setError("Nao existe Banco de Dados para Dump",1);
			return false;
		}
		$this->databases=$db;
		$this->headMail[]="Data Bases: ".implode(', ',$this->databases);
		return true;
	}

	private function getElement_privileges($db){
		if ($this->privilegesPass) return;
		$this->privilegesPass=true;

		$sql=$this->verssion<5?"SELECT CONCAT(\"'\",`User`,\"'@'\",`Host`,\"'\") as Usr FROM mysql.user":'SELECT GRANTEE as Usr FROM information_schema.USER_PRIVILEGES GROUP BY GRANTEE';
		$res=$this->protect['conn']->query($sql);
		$var=array();
		while ($line=$res->fetch_assoc()) {
			$sql="SHOW GRANTS FOR {$line['Usr']}";
			$r=$this->protect['conn']->query($sql,false);
			if($this->protect['conn']->error) continue;
			$var[]="-- Grant for {$line['Usr']}";
			while($gl=$r->fetch_row()) {
				$gl=str_replace('\_','_',$gl[0]).';';
				//$var[]="$gl;";
				$this->extruct['privileges'][''][]=$gl;
			}
			$r->close();
		}
		if(@$this->extruct['privileges']) $this->extruct['privileges'][''][]="FLUSH PRIVILEGES;\n";
		$res->close();
	}
	private function getElement_dbs($db){
		$dbNew=$this->databases[$db];
		$sql=array();
		if ($this->dropDataBase) $sql[]="DROP DATABASE IF EXISTS `$dbNew`;";
		$sql[]="CREATE DATABASE IF NOT EXISTS `$dbNew`;\n";
		$this->extruct['db']["`$db`"]=$sql;
	}
	private function getElement_data($db){
		$tbls=$this->getTables($db);
		foreach($tbls as $tbl){
			$fromFullName="`$db`.`$tbl`";
			$this->dataTables[$fromFullName]=$this->replaceDb($this->getFullNameToTable($db,$tbl));
			$this->extruct['tables'][$fromFullName]='';
		}
	}
	private function getElement_tables($db){
		$tbls=$this->getTables($db);
		foreach($tbls as $tbl){
			$fromFullName="`$db`.`$tbl`";
			$toFullName=$this->getFullNameToTable($db,$tbl);
		
			$res=$this->protect['conn']->query("SHOW CREATE TABLE $fromFullName",false);
			if ($this->conn->error || !($line=$res->fetch_assoc()) || !isset($line['Create Table'])) continue;
			$res->close();

			$ine=$this->ifNotExistsTable?' IF NOT EXISTS':'';
			$sql=preg_replace(
				array('/CREATE\s+TABLE\s+.+?\(/','/ USING (BTREE|HASH)/i'),
				array("CREATE TABLE$ine $toFullName(",''),
				$line['Create Table']
			).";\n";
			if($this->isTmpTableDrop()) $sql=array("DROP TABLE IF EXISTS $toFullName;",$sql);
			$this->extruct['tables'][$fromFullName]=$sql;
		}
	}
	private function getElement_views($db){
		if ($this->verssion<5) return;
		$res=$this->protect['conn']->query($sql="SELECT * FROM information_schema.`VIEWS` t WHERE `TABLE_SCHEMA`='{$this->conn->escape_string($db)}' ORDER BY `TABLE_NAME`",false);
		if($this->verifyQueryError($this->protect['conn'],$sql)) return;
		
		while ($line=$res->fetch_assoc()) if(preg_match($this->protect['er_viewsFilter'],$line['TABLE_NAME'])) {
			$fullName="`$db`.`{$line['TABLE_NAME']}`";
			$sql=$line['VIEW_DEFINITION'];
			$comp='';
			if(preg_match('/(\/\*(.*?)\*\/)/',$sql,$ret)) {
				$comp=$ret[2];
				$sql=str_replace($ret[1],'',$sql);
			}
			$resV=$this->protect['conn']->query($sql="show create view $fullName",false);
			if($this->verifyQueryError($this->protect['conn'],$sql)) continue;
			if($l=$resV->fetch_row()) {
				$sql=preg_replace(
					array(
							'/(CREATE\b.*?\bVIEW\b)(\s*.*?\s*)(\bAS\b\s*)/',
							'/(\bAS\b\s+select\b\s*)/i',
							'/\s+((?:cross|inner|(?:natural\s+)?(?:right|left)(?:\s+outer)?)?join|straight_join|from)/i',
							'/(\bAS\b.*?,)/',
					),
					array("\\1 $fullName \\3\n","\\1\n\t"," \n\\1","\\1\n\t"),
					"{$l[1]};\n"
				);
				if($this->dropTable) {
					$sql=array("DROP TABLE IF EXISTS $fullName;",$sql);
					if($this->verssion>=5) array_unshift($sql,"DROP VIEW IF EXISTS $fullName;");
				}
				$this->extruct['views'][$fullName]=$sql;
			}
			@$resV->close();
		}
		@$res->close();
	}
	private function getElement_functions($db){ 
		return $this->getElement_functions_procedures($db); 
	}
	private function getElement_procedures($db){ 
		return $this->getElement_functions_procedures($db,'procedures','PROCEDURE'); 
	}
	private function getElement_functions_procedures($db,$item='functions',$sqlItem='FUNCTION'){
		if ($this->verssion<5 || !$item) return ;
		$sql="
			SELECT * FROM information_schema.`ROUTINES` t
			WHERE `ROUTINE_TYPE`='$sqlItem' AND `ROUTINE_SCHEMA`='{$this->conn->escape_string($db)}' 
			ORDER BY `SPECIFIC_NAME`
		";
		$res=$this->protect['conn']->query($sql,false);
		if($this->verifyQueryError($this->protect['conn'],$sql)) return;
		while ($line=$res->fetch_assoc()) if(preg_match($this->protect["er_{$item}Filter"],$line['SPECIFIC_NAME'])) {
			$fullName="`$db`.`{$line['SPECIFIC_NAME']}`";
			$resSql=$this->protect['conn']->query("SHOW CREATE $sqlItem $fullName",false);
			if ($lineSql=$resSql->fetch_row()) {
				$sql=preg_replace("/($sqlItem\s*)(`.*)/","\\1`$db`.\\2",$lineSql[2])."\n";
				if($this->dropTable) $sql=array("DROP $sqlItem IF EXISTS $fullName;",$sql);
				$this->extruct[$item][$fullName]=$sql;
			}
			@$resSql->close();
		}
		@$res->close();
	}
	private function getElement_triggers($db){
		if ($this->verssion<5) return ;
		$res=$this->protect['conn']->query("SELECT * FROM information_schema.`TRIGGERS` t WHERE `TRIGGER_SCHEMA`='{$this->conn->escape_string($db)}' ORDER BY `TRIGGER_NAME`",false);
		while ($l=$res->fetch_assoc()) if(preg_match($this->protect['er_triggersFilter'],$l['TRIGGER_NAME'])){
			$fullName="`$db`.`{$l['TRIGGER_NAME']}`";
			$sql="CREATE TRIGGER $fullName ";
			$sql.="{$l['ACTION_TIMING']} {$l['EVENT_MANIPULATION']} ";
			$sql.="ON `{$l['EVENT_OBJECT_SCHEMA']}`.`{$l['EVENT_OBJECT_TABLE']}` ";
			$sql.="FOR EACH {$l['ACTION_ORIENTATION']} ";
			$sql.=$l['ACTION_STATEMENT'];
			$sql.=";\n";
			if($this->dropTable) $sql=array("DROP TRIGGER IF EXISTS $fullName;",$sql);
			$this->extruct['triggers'][$fullName]=$sql;
		}
		$res->close();
	}
	private function getElement_events($db){
		if ($this->verssion<5.1) return;
		$res=$this->protect['conn']->query("SELECT * FROM information_schema.`EVENTS` WHERE EVENT_SCHEMA='$db'");
		while ($l=$res->fetch_assoc()) if(preg_match($this->protect['er_eventsFilter'],$l['EVENT_NAME'])){
			$fullName="`$db`.`{$l['EVENT_NAME']}`";
			$sql='CREATE ';
			if($l['DEFINER']) $sql.='DEFINER='.$l['DEFINER'].' ';
			$sql.='EVENT ';
			if($this->ifNotExists) $sql.='IF NOT EXISTS ';
			$sql.="EVENT $fullName \n";
			
			$sql.='ON SCHEDULE ';
			
			
			if($l['EVENT_TYPE']=='ONE TIME') $sql.="AT '{$l['EXECUTE_AT']}' \n";
			else {
				$ends=$l['ENDS']?"ENDS '{$l['ENDS']}'":'';
				$sql.="EVERY '{$l['INTERVAL_VALUE']}' {$l['INTERVAL_FIELD']} STARTS '{$l['STARTS']}' $ends\n";
			}

			if($l['ON_COMPLETION']) $sql.='ON COMPLETION '.$l['ON_COMPLETION'].' ';
			$sql.="\n{$l['STATUS']} ";
			if($l['EVENT_COMMENT']) $sql.="\nCOMMENT '{$this->protect['conn']->escape_string($l['EVENT_COMMENT'])}'";
			
			$sql.="DO \n{$l['EVENT_DEFINITION']};";
			if($this->dropTable) $sql=array("DROP EVENT IF EXISTS $fullName;",$sql);
			$this->extruct['events'][$fullName]=$sql;
		}
	}

	private function replaceDb($sql){
		return preg_replace($this->replaceFrom,$this->replaceTo,$sql);
	}
	private function renameTables($fullTable){
		if(!@$this->renameTables[$fullTable]) return;
		$tmp=$this->replaceDb($this->renameTables[$fullTable]);
		$fullTable=$this->replaceDb($fullTable);
		unset($this->renameTables[$fullTable]);
		$this->out("DROP TABLE IF EXISTS {$fullTable};");
		if($ok=$this->out("ALTER TABLE {$tmp} RENAME TO {$fullTable};\n")) $this->pr(" ERROR\n-- ".implode("\n-- ",$ok)."\n");
	}
	private function shiftItem(&$item,&$fullName){
		$sql='';
		while(!$sql){
			if(!$this->extruct) return false;
			if($item===false) {
				reset($this->extruct);
				$item=key($this->extruct);
			}
			if(!@$this->extruct[$item]) return false;
			if($fullName===false) {
				reset($this->extruct[$item]);
				$fullName=key($this->extruct[$item]);
			}
			$sql=@$this->extruct[$item][$fullName];
			
			if(array_key_exists($fullName,$this->extruct[$item])) {
				unset($this->extruct[$item][$fullName]);
				if(!@$this->extruct[$item]) unset($this->extruct[$item]);
			} else return false;
		}
		return $sql;
	}
	private function splitFullName($fullName){
		preg_match('/`([^`]+)`(?:\.`([^`]+)`)?/',$fullName,$ret);
		return array('db'=>@$ret[1],'name'=>@$ret[2]);
	}
	private function getOrder_items($item=false,$fullName=false){
		if(!($aSql=$this->shiftItem($item,$fullName))) return false;
		//print "$item: $fullName\n";
		$aSql=(array)$aSql;
		$tbl=$this->splitFullName($fullName);
		$aCases=array(
			array('tables',"/CONSTRAINT\b.*\bFOREIGN KEY\b.*\bREFERENCES\b\s*{$this->erFullTable}/i"),
			array('functions',"/{$this->erFullTable}\s*\(/i"),
			array('procedures',"/call\b\s*{$this->erFullTable}/i"),
			array('views',"/(?:FROM|JOIN)(?:\(|\s)+{$this->erFullTable}/i"),
		);

		foreach($aSql as $sql) foreach($aCases as $cases) if (preg_match_all($cases[1],$sql,$ret,PREG_SET_ORDER)) foreach($ret as $v){
			$dbProc=$tbl['db'];
			$tbProc=$v[3];
			if($v[1]) $dbProc=$v[1];
			elseif(@$v[2]) $dbProc=$v[2];
			elseif(@$v[4] && preg_match($this->erWR,$v[4])) continue;
			if(!$tbProc) $tbProc=$v[4];
			if($cases[0]==$item && $dbProc==$tbl['db'] && $tbProc==$tbl['name']) continue;
			//print "$item: $fullName => {$cases[0]}: `$dbProc`.`$tbProc`\n";
			$this->getOrder_items($cases[0],"`$dbProc`.`$tbProc`");
		}

		//print "$item: $fullName\n";
		if($item=='tables' && $this->paramTables && $this->dropTable && !$this->paramData) array_unshift($aSql,"DROP TABLE IF EXISTS $fullName;\n");
		$aSql=$this->replaceDb($aSql);
		if($db=$this->useDB($tbl['db'])) array_unshift($aSql,$db);
		$this->elementOrder[preg_replace('/s$/','',$item).($fullName?": $fullName":'')]=array(
			'item'=>$item,
			'sql'=>$aSql,
			'fullName'=>$fullName,
		);
		return true;
	}
	private function sendExtruct(){
		foreach($this->elementOrder as $k=>$job){
			$this->outTitle("-- ### $k",$job['sql']);
			if($job['item']=='tables' && @$this->dataTables[$job['fullName']]) if($this->send_data($job['fullName'])) $this->renameTables($job['fullName']);
		}
	}
	
	private function send_data($fromTable){
		$toTable=@$this->dataTables[$fromTable];
		if(!$toTable) return false;
		unset($this->dataTables[$fromTable]);
		$head='-- ### data:  ';

		$time=microtime(true);
		if(!$this->protect['conn']->ping()) {
			@$this->protect['conn']->close();
			$this->protect['conn']=null;
			$this->conn=$this->connOld;
		}

		$res=$this->protect['conn']->query($sql="SELECT * FROM $fromTable",false);
		if($this->verifyQueryError($this->protect['conn'],$sql,"$head$fromTable ERROR SOURCE {$this->protect['conn']->error}\n")) return false;
		$numRows=$res->num_rows;
		if($numRows) {
			$fields=$res->fetch_fields();
			$keys=array();
			foreach($fields as $fld) $keys[]="`{$fld->name}`";
			$sql="{$this->protect['cmdInsert']} $toTable (".implode(',',$keys).") VALUES \n";
			$i=0;
			$percntOld=-1;
			$estima=$estimaOld='';
			$tam=10;
			$percent=100;
			$restLine=str_repeat(' ',$tam).str_repeat(chr(8),$tam);
			$timeMinuto=microtime(true);
			while($line=$res->fetch_row()) {
				$i++;

				$percentual=$i*100/$numRows;
				$percent=round($percentual);
				$dur=microtime(true)-$time;
				$eta=(int)@$dur*100/$percentual;
				$rest=round(max(0,$eta-$dur));
				$eta=round($eta);

				if(microtime(true)-$timeMinuto>=1){
					$timeMinuto=microtime(true);
					$estima=" (ETA {$eta}s, REST {$rest}s)";
				}
				if($percntOld!=$percent || $estimaOld!=$estima) {
					$percntOld=$percent;
					$estimaOld=$estima;
					if(!$this->showSqlScreen) $this->show("\r$head$fromTable $percent%$estima$restLine");
				}
				$this->outData($sql,$line);
			}
			$this->outData($sql);
			if(!$this->showSqlScreen) $this->show("\r");
			$this->pr("$head$fromTable $percent%$estima ".sprintf("(%01.2fs) $numRows Registros\n", microtime(true)-$time));
		}else $this->pr("$head$fromTable 0 Registros\n");
		$res->close();
		return true;
	}
	private function outData($sql,$line=[]){
		if($line) {
			foreach ($line as $k=>$v) $line[$k]=is_null($v)?"NULL":(is_string($v)?"'{$this->conn->escape_string($v)}'":$v);
			$this->cache[]="(".implode(",",$line).")";
			if (count($this->cache)<$this->maxRows) return;
		}
		$this->protect['conn']->ping();
		if ($this->cache) $this->out($sql.implode(",\n",$this->cache).";\n\n");
		$this->cache=array();
	}
	private function outTitle($title,$var){
		$erro=array();
		$this->pr("$title\n");
		$erro=$this->out($var);
		if($erro) $this->pr("$title ERROR\n-- ".implode("\n-- ",$erro)."\n".print_r($var,true));
		return !$erro;
	}
	private function out($text){
		$out=array();
		if (is_array($text)) foreach($text as $t) $out=array_merge($out,$this->out($t));
		else {
			if(!$this->printFileName && $this->protect['file']) {
				$this->pr("-- Create File {$this->tempFile}\n");
				$this->headMail[]='File: '.$this->getFileName();
				if (!is_file($this->tempFile)) $this->erro_file[$this->tempFile]=$this->tempFile;
			}
			$this->printFileName=1;
			$text=str_replace(chr(13)," ",$text)."\n";
			if (trim($text) && $this->protect['connTarget']) {
				$this->protect['connTarget']->ping();
				
				if(preg_match('/^USE `([^`]+)`/i',$text,$ret)) $this->protect['connTarget']->select_db($ret[1]);
				$this->protect['connTarget']->query($text,false);
				if($this->verifyQueryError($this->protect['connTarget'],$text)) $out[]=$this->protect['connTarget']->error;
				//$this->protect['connTarget']->commit();
			}
			if ($this->protect['file']) {
				$this->fileCreated=1;
				file_put_contents($this->tempFile,$text,FILE_APPEND);
			}			
			if($this->showSqlScreen) print $text;
		}
		return $out;
	}

	private function pr($text){
		$this->show($text);
		if ($this->log) file_put_contents($this->log,$text,FILE_APPEND);
	}
	private function show($text){ if ($this->debug) print $text; }
	
	private function getFileName(){
		if (!$this->protect['file']) return '';
		if(!(@$this->fileOut[$this->protect['file']][$this->protect['utcTimeFileName']][$this->protect['timeStampFormat']])) {
			if (!is_dir($dir=dirname($this->protect['file']))) `mkdir -p $dir`;
			if ($this->protect['utcTimeFileName'] && $this->protect['timeStampFormat']) $fileOut=preg_replace('/(\.\w+)$/',$this->utcFileNameTemp.'\1',$this->protect['file']);
			else $fileOut=$this->protect['file'];
			$this->fileOut[$this->protect['file']][$this->protect['utcTimeFileName']][$this->protect['timeStampFormat']]=$fileOut;
		}
		return $this->fileOut[$this->protect['file']][$this->protect['utcTimeFileName']][$this->protect['timeStampFormat']];
	}
	private function compactFile(){
		$this->endVariables();
		if(!$this->protect['file'] || !$this->fileCreated) return;
		$file=$this->getFileName();
		$this->deleteFiles();
		if ($this->protect['compactFile']) {
			$this->sendTime('Tempo do Dump',$this->exeTime);
			$time=microtime(true);
			$this->pr("-- Compacting {$this->tempFile} and moving to {$file}.bz2 ...\n");
			`rm -f "{$this->tempFile}.bz2"`;
			`bzip2 "{$this->tempFile}" && mv -f "{$this->tempFile}.bz2" "{$file}.bz2"`;
			//$this->setError("File Compacted {$this->tempFile}.bz2");
			//$this->sendTime('Tempo de Compactacao',$time);
		} else `mv -f "{$this->tempFile}" "{$file}.bz2"`;
	}
	private function deleteFiles(){
		if ($this->protect['utcTimeFileName'] && $this->protect['timeStampFormat'] && $this->protect['last'] && $this->protect['file']){
			$this->headMail[]="Delete less: {$this->protect['last']}";
			$fParts=preg_match('/(.*?)(\.\w+)?$/',basename($this->protect['file']),$ret);
			$ret[2].=$this->protect['compactFile']?'.bz2':'';
			$last=preg_replace('/\D/','',$ret[1].strftime($this->protect['timeStampFormat'],strtotime($this->protect['last'])).$ret[2]);
			$grep=preg_quote($ret[1]).".*?".preg_quote($ret[2]);
			$dir=dirname($this->protect['file']);
			$out=trim(`for i in \`ls -1 $dir | egrep "^$grep$"\`; do T=\`echo \$i | sed -r "s/[^0-9]//g"\`; if [ "\$T" -lt "$last" ]; then rm -f "$dir/\$i"; echo "Delete $dir/\$i"; fi; done`);
			if($out) {
				$this->setError($out);
				$this->pr("$out\n");
			}
		}
	}
	
	function sendMail(){
		$mail=($this->mail_error && $this->errorLevel)?$this->mail_error:(($this->mail_ok && !$this->errorLevel)?$this->mail_ok:array());
		if ($mail) {
			$body='';
			if (!@$mail['to']) return false;
			if (!@$mail['subject']) $mail['subject']="Dump ".($this->errorLevel?"ERROR":"OK");
			if (@$mail['body']) $body==trim(@$mail['body'])."\n\n";
			$body.="HOST: {$this->hostname}";
			if ($this->headMail) $body.="\n".implode("\n",$this->headMail);
			if ($this->error) $body.="\n\n".implode("\n",$this->error);
			$from=$this->from?"From: {$this->from}\n":'';
			$this->pr("Enviando E-mail...\nTo: {$mail['to']}\n{$from}Subject: {$mail['subject']}\n\n");
			mail($mail['to'],$mail['subject'],$body,$from);
		}
		return (bool)$mail;
	}
	function mail_error($to,$subject='',$body=''){ $this->mail_error=array('to'=>$to,'subject'=>$subject,'body'=>$body); }
	function mail_ok($to,$subject='',$body=''){ $this->mail_ok=array('to'=>$to,'subject'=>$subject,'body'=>$body); }
	function setError($error,$level=0){
		$errType=$level?'ERROR':'WARNNING';
		$this->error[]="-- $errType: $error";
		$this->errorLevel|=$level;
	}
	function getErrors(){ return implode("\n",$this->error); }
	private function verifyQueryError($conn,$sql='',$message=''){
		if($conn->error) {
			$d=debug_backtrace();
			$where="[{$d[0]['line']}] {$d[1]['class']}{$d[1]['type']}{$d[1]['function']}";
			$sql=trim($sql);
			$sql=$sql?"QUERY: $sql":'';
			$this->setError("$where\n{$conn->error}\n$sql",1);
			$this->pr($message?$message:"ERROR: $where\n{$conn->error}\n");
			return true;
		} else return false;
	}
}
