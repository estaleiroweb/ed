<?php
//putenv("ORACLE_SID=orcl");
class Conn_oracle extends Conn_Main {
	public $autocommit=false;
	public $startTableDelimiter='"';
	public $endTableDelimiter='"';
	public $startFieldDelimiter='"';
	public $endFieldDelimiter='"';
	public $charsets=[
		'AR8ADOS710T'=>'', //Arabic MS-DOS 710 8-bit Latin/Arabic
		'AR8ADOS710'=>'', //Arabic MS-DOS 710 Server 8-bit Latin/Arabic
		'AR8ADOS720T'=>'', //Arabic MS-DOS 720 8-bit Latin/Arabic
		'AR8ADOS720'=>'', //Arabic MS-DOS 720 Server 8-bit Latin/Arabic
		'AR8APTEC715T'=>'', //APTEC 715 8-bit Latin/Arabic
		'AR8APTEC715'=>'', //APTEC 715 Server 8-bit Latin/Arabic
		'AR8ARABICMACS'=>'', //Mac Server 8-bit Latin/Arabic
		'AR8ARABICMACT'=>'', //Mac 8-bit Latin/Arabic
		'AR8ARABICMAC'=>'', //Mac Client 8-bit Latin/Arabic
		'AR8ASMO708PLUS'=>'', //ASMO 708 Plus 8-bit Latin/Arabic
		'AR8ASMO8X'=>'', //ASMO Extended 708 8-bit Latin/Arabic
		'AR8EBCDIC420S'=>'', //EBCDIC Code Page 420 Server 8-bit Latin/Arabic
		'AR8EBCDICX'=>'', //EBCDIC XBASIC Server 8-bit Latin/Arabic
		'AR8HPARABIC8T'=>'', //HP 8-bit Latin/Arabic
		'WE8ISO8859P1'=>'ISO-8859-1', //ISO 8859-1 West European
		'AR8MSWIN1256'=>'', //MS Windows Code Page 1256 8-Bit Latin/Arabic
		'AR8MUSSAD768T'=>'', //Mussa'd Alarabi/2 768 8-bit Latin/Arabic
		'AR8MUSSAD768'=>'', //Mussa'd Alarabi/2 768 Server 8-bit Latin/Arabic
		'AR8NAFITHA711T'=>'', //Nafitha International 711 Server 8-bit Latin/Arabic
		'AR8NAFITHA711'=>'', //Nafitha Enhanced 711 Server 8-bit Latin/Arabic
		'AR8NAFITHA721T'=>'', //Nafitha International 721 8-bit Latin/Arabic
		'AR8NAFITHA721'=>'', //Nafitha International 721 Server 8-bit Latin/Arabic
		'AR8SAKHR706'=>'', //SAKHR 706 Server 8-bit Latin/Arabic
		'AR8SAKHR707T'=>'', //SAKHR 707 8-bit Latin/Arabic
		'AR8SAKHR707'=>'', //SAKHR 707 Server 8-bit Latin/Arabic
		'AR8XBASIC'=>'', //XBASIC 8-bit Latin/Arabic
		'NE8ISO8859P10'=>'ISO-8859-10', //ISO 8859-10 North European
		'BG8MSWIN'=>'MS-ANSI', //MS Windows 8-bit Bulgarian Cyrillic
		'BG8PC437S'=>'CP437', //IBM-PC Code Page 437 8-bit (Bulgarian Modification)
		'BLT8CP921'=>'CP921', //Latvian Standard LVS8-92(1) Windows/Unix 8-bit Baltic
		'BLT8EBCDIC1112S'=>'CP1112', //EBCDIC Code Page 1112 8-bit Server Baltic Multilingual
		'BLT8EBCDIC1112'=>'CP1112', //EBCDIC Code Page 1112 8-bit Baltic Multilingual
		'BLT8ISO8859P13'=>'ISO-8859-13', //ISO 8859-13 Baltic
		'BLT8MSWIN1257'=>'CP1257', //MS Windows Code Page 1257 8-bit Baltic
		'BLT8PC775'=>'CP775', //IBM-PC Code Page 775 8-bit Baltic
		'BN8BSCII'=>'', //Bangladesh National Code 8-bit BSCII
		'CDN8PC863'=>'CP863', //IBM-PC Code Page 863 8-bit Canadian French
		'CE8BS2000'=>'', //Siemens EBCDIC.DF.04-2 8-bit Central European
		'CEL8ISO8859P14'=>'ISO-8859-14', //ISO 8859-13 Celtic
		'CH7DEC'=>'', //DEC VT100 7-bit Swiss (German/French)
		'CL8BS2000'=>'', //Siemens EBCDIC.EHC.LC 8-bit Latin/Cyrillic-1
		'CL8EBCDIC1025C'=>'', //EBCDIC Code Page 1025 Client 8-bit Cyrillic
		'CL8EBCDIC1025R'=>'', //EBCDIC Code Page 1025 Server 8-bit Cyrillic
		'CL8EBCDIC1025S'=>'', //EBCDIC Code Page 1025 Server 8-bit Cyrillic
		'CL8EBCDIC1025'=>'', //EBCDIC Code Page 1025 8-bit Cyrillic
		'CL8EBCDIC1025X'=>'', //EBCDIC Code Page 1025 (Modified) 8-bit Cyrillic
		'CL8EBCDIC1158R'=>'', //EBCDIC Code Page 1158 Server 8-bit Cyrillic
		'CL8EBCDIC1158'=>'', //EBCDIC Code Page 1158 8-bit Cyrillic
		'WE8ISO8859P15'=>'ISO-8859-15', //ISO 8859-15 West European
		'CL8ISOIR111'=>'ISO-IR-111', //SOIR111 Cyrillic
		'CL8KOI8R'=>'', //RELCOM Internet Standard 8-bit Latin/Cyrillic
		'CL8KOI8U'=>'', //KOI8 Ukrainian Cyrillic
		'CL8MACCYRILLICS'=>'', //Mac Server 8-bit Latin/Cyrillic
		'CL8MACCYRILLIC'=>'', //Mac Client 8-bit Latin/Cyrillic
		'CL8MSWIN1251'=>'', //MS Windows Code Page 1251 8-bit Latin/Cyrillic
		'D7DEC'=>'', //DEC VT100 7-bit German
		'D7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit German
		'D8BS2000'=>'', //Siemens 9750-62 EBCDIC 8-bit German
		'D8EBCDIC1141'=>'', //EBCDIC Code Page 1141 8-bit Austrian German
		'D8EBCDIC273'=>'', //EBCDIC Code Page 273/1 8-bit Austrian German
		'DK7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit Danish
		'DK8BS2000'=>'', //Siemens 9750-62 EBCDIC 8-bit Danish
		'DK8EBCDIC1142'=>'', //EBCDIC Code Page 1142 8-bit Danish
		'DK8EBCDIC277'=>'', //EBCDIC Code Page 277/1 8-bit Danish
		'E7DEC'=>'', //DEC VT100 7-bit Spanish
		'E7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit Spanish
		'E8BS2000'=>'', //Siemens 9750-62 EBCDIC 8-bit Spanish
		'EE8BS2000'=>'', //Siemens EBCDIC.EHC.L2 8-bit East European
		'EE8EBCDIC870C'=>'', //EBCDIC Code Page 870 Client 8-bit East European
		'EE8EBCDIC870S'=>'', //EBCDIC Code Page 870 Server 8-bit East European
		'EE8EBCDIC870'=>'', //EBCDIC Code Page 870 8-bit East European
		'EE8ISO8859P2'=>'ISO-8859-2', //ISO 8859-2 East European
		'EE8MACCES'=>'', //Mac Server 8-bit Central European
		'EE8MACCE'=>'', //Mac Client 8-bit Central European
		'EE8MACCROATIANS'=>'', //Mac Server 8-bit Croatian
		'EE8MACCROATIAN'=>'', //Mac Client 8-bit Croatian
		'EE8MSWIN1250'=>'', //MS Windows Code Page 1250 8-bit East European
		'EE8PC852'=>'', //IBM-PC Code Page 852 8-bit East European
		'EEC8EUROASCI'=>'', //EEC Targon 35 ASCI West European/Greek
		'EEC8EUROPA3'=>'', //EEC EUROPA3 8-bit West European/Greek
		'EL8DEC'=>'', //DEC 8-bit Latin/Greek
		'EL8EBCDIC423R'=>'', //IBM EBCDIC Code Page 423 for RDBMS server-side
		'EL8EBCDIC875R'=>'', //EBCDIC Code Page 875 Server 8-bit Greek
		'EL8EBCDIC875S'=>'', //EBCDIC Code Page 875 Server 8-bit Greek
		'EL8EBCDIC875'=>'', //EBCDIC Code Page 875 8-bit Greek
		'EL8GCOS7'=>'', //Bull EBCDIC GCOS7 8-bit Greek
		'SE8ISO8859P3'=>'ISO-8859-3', //ISO 8859-3 South European
		'EL8MACGREEKS'=>'', //Mac Server 8-bit Greek
		'EL8MACGREEK'=>'', //Mac Client 8-bit Greek
		'EL8MSWIN1253'=>'', //MS Windows Code Page 1253 8-bit Latin/Greek
		'EL8PC437S'=>'', //IBM-PC Code Page 437 8-bit (Greek modification)
		'EL8PC737'=>'', //IBM-PC Code Page 737 8-bit Greek/Latin
		'EL8PC851'=>'', //IBM-PC Code Page 851 8-bit Greek/Latin
		'EL8PC869'=>'', //IBM-PC Code Page 869 8-bit Greek/Latin
		'ET8MSWIN923'=>'', //MS Windows Code Page 923 8-bit Estonian
		'F7DEC'=>'', //DEC VT100 7-bit French
		'F7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit French
		'F8BS2000'=>'', //Siemens 9750-62 EBCDIC 8-bit French
		'F8EBCDIC1147'=>'', //EBCDIC Code Page 1147 8-bit French
		'F8EBCDIC297'=>'', //EBCDIC Code Page 297 8-bit French
		'HU8ABMOD'=>'', //Hungarian 8-bit Special AB Mod
		'HU8CWI2'=>'', //Hungarian 8-bit CWI-2
		'I7DEC'=>'', //DEC VT100 7-bit Italian
		'I7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit Italian
		'I8EBCDIC1144'=>'', //EBCDIC Code Page 1144 8-bit Italian
		'I8EBCDIC280'=>'', //EBCDIC Code Page 280/1 8-bit Italian
		'IN8ISCII'=>'', //Multiple-Script Indian Standard 8-bit Latin/Indian
		'IS8MACICELANDICS'=>'', //Mac Server 8-bit Icelandic
		'IS8MACICELANDIC'=>'', //Mac Client 8-bit Icelandic
		'IS8PC861'=>'', //IBM-PC Code Page 861 8-bit Icelandic
		'IW7IS960'=>'', //Israeli Standard 960 7-bit Latin/Hebrew
		'IW8EBCDIC1086'=>'', //EBCDIC Code Page 1086 8-bit Hebrew
		'IW8EBCDIC424S'=>'', //EBCDIC Code Page 424 Server 8-bit Latin/Hebrew
		'IW8EBCDIC424'=>'', //EBCDIC Code Page 424 8-bit Latin/Hebrew
		'NEE8ISO8859P4'=>'ISO-8859-4', //ISO 8859-4 North and North-East European
		'IW8MACHEBREWS'=>'', //Mac Server 8-bit Hebrew
		'IW8MACHEBREW'=>'', //Mac Client 8-bit Hebrew
		'IW8MSWIN1255'=>'', //MS Windows Code Page 1255 8-bit Latin/Hebrew
		'IW8PC1507'=>'', //IBM-PC Code Page 1507/862 8-bit Latin/Hebrew
		'JA16DBCS'=>'', //IBM EBCDIC 16-bit Japanese
		'JA16EBCDIC930'=>'', //IBM DBCS Code Page 290 16-bit Japanese
		'JA16EUCTILDE'=>'', //Same as ja16euc except for the way that the wave dash and the tilde are mapped to and from Unicode
		'JA16EUC'=>'', //EUC 24-bit Japanese
		'JA16EUCYEN'=>'', //EUC 24-bit Japanese with '\' mapped to the Japanese yen character
		'JA16MACSJIS'=>'', //Mac client Shift-JIS 16-bit Japanese
		'JA16SJISTILDE'=>'', //Same as ja16sjis except for the way that the wave dash and the tilde are mapped to and from Unicode.
		'JA16SJIS'=>'', //Shift-JIS 16-bit Japanese
		'JA16SJISYEN'=>'', //Shift-JIS 16-bit Japanese with '\' mapped to the Japanese yen character
		'JA16VMS'=>'', //JVMS 16-bit Japanese
		'KO16DBCS'=>'', //IBM EBCDIC 16-bit Korean
		'KO16KSC5601'=>'', //KSC5601 16-bit Korean
		'KO16KSCCS'=>'', //KSCCS 16-bit Korean
		'KO16MSWIN949'=>'', //MS Windows Code Page 949 Korean
		'LA8ISO6937'=>'ISO6937', //ISO 6937 8-bit Coded Character Set for Text Communication
		'LA8PASSPORT'=>'', //German Government Printer 8-bit All-European Latin
		'LT8MSWIN921'=>'', //MS Windows Code Page 921 8-bit Lithuanian
		'LT8PC772'=>'', //IBM-PC Code Page 772 8-bit Lithuanian (Latin/Cyrillic)
		'LT8PC774'=>'', //IBM-PC Code Page 774 8-bit Lithuanian (Latin)
		'LV8PC1117'=>'', //IBM-PC Code Page 1117 8-bit Latvian
		'LV8PC8LR'=>'', //Latvian Version IBM-PC Code Page 866 8-bit Latin/Cyrillic
		'LV8RST104090'=>'', //IBM-PC Alternative Code Page 8-bit Latvian (Latin/Cyrillic)
		'N7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit Norwegian
		'N8PC865'=>'', //IBM-PC Code Page 865 8-bit Norwegian
		'NDK7DEC'=>'', //DEC VT100 7-bit Norwegian/Danish
		'CL8ISO8859P5'=>'ISO-8859-5', //ISO 8859-5 Latin/Cyrillic
		'AR8ISO8859P6'=>'ISO-8859-6', //ISO 8859-6 Latin/Arabic
		'NL7DEC'=>'', //DEC VT100 7-bit Dutch
		'RU8BESTA'=>'', //BESTA 8-bit Latin/Cyrillic
		'RU8PC855'=>'', //IBM-PC Code Page 855 8-bit Latin/Cyrillic
		'RU8PC866'=>'', //IBM-PC Code Page 866 8-bit Latin/Cyrillic
		'S7DEC'=>'', //DEC VT100 7-bit Swedish
		'S7SIEMENS9780X'=>'', //Siemens 97801/97808 7-bit Swedish
		'S8BS2000'=>'', //Siemens 9750-62 EBCDIC 8-bit Swedish
		'S8EBCDIC1143'=>'', //EBCDIC Code Page 1143 8-bit Swedish
		'S8EBCDIC278'=>'', //EBCDIC Code Page 278/1 8-bit Swedish
		'EL8ISO8859P7'=>'ISO-8859-7', //ISO 8859-7 Latin/Greek
		'SF7ASCII'=>'', //ASCII 7-bit Finnish
		'SF7DEC'=>'', //DEC VT100 7-bit Finnish
		'TH8MACTHAIS'=>'', //Mac Server 8-bit Latin/Thai
		'TH8MACTHAI'=>'', //Mac Client 8-bit Latin/Thai
		'TH8TISASCII'=>'', //Thai Industrial Standard 620-2533 - ASCII 8-bit
		'TH8TISEBCDICS'=>'', //Thai Industrial Standard 620-2533 - EBCDIC Server 8-bit
		'TH8TISEBCDIC'=>'', //Thai Industrial Standard 620-2533 - EBCDIC 8-bit
		'TIMESTEN8'=>'', //TimesTen Legacy
		'TR7DEC'=>'', //DEC VT100 7-bit Turkish
		'TR8DEC'=>'', //DEC 8-bit Turkish
		'TR8EBCDIC1026S'=>'', //EBCDIC Code Page 1026 Server 8-bit Turkish
		'TR8EBCDIC1026'=>'', //EBCDIC Code Page 1026 8-bit Turkish
		'TR8MACTURKISHS'=>'', //Mac Server 8-bit Turkish
		'TR8MACTURKISH'=>'', //Mac Client 8-bit Turkish
		'TR8MSWIN1254'=>'', //MS Windows Code Page 1254 8-bit Turkish
		'TR8PC857'=>'', //IBM-PC Code Page 857 8-bit Turkish
		'US7ASCII'=>'', //ASCII 7-bit American
		'US8BS2000'=>'', //Siemens 9750-62 EBCDIC 8-bit American
		'US8ICL'=>'', //ICL EBCDIC 8-bit American
		'US8PC437'=>'', //IBM-PC Code Page 437 8-bit American
		'VN8MSWIN1258'=>'', //MS Windows Code Page 1258 8-bit Vietnamese
		'VN8VN3'=>'', //VN3 8-bit Vietnamese
		'WE8BS2000E'=>'', //Siemens EBCDIC.DF.04-F 8-bit West European with Euro symbol
		'WE8BS2000L5'=>'', //Siemens EBCDIC.DF.04-9 8-bit WE & Turkish
		'WE8BS2000'=>'', //Siemens EBCDIC.DF.04-1 8-bit West European
		'WE8DEC'=>'', //DEC 8-bit West European
		'WE8DG'=>'', //DG 8-bit West European
		'WE8EBCDIC1047E'=>'', //Latin 1/Open Systems 1047
		'WE8EBCDIC1047'=>'', //EBCDIC Code Page 1047 8-bit West European
		'WE8EBCDIC1140C'=>'', //EBCDIC Code Page 1140 Client 8-bit West European
		'WE8EBCDIC1140'=>'', //EBCDIC Code Page 1140 8-bit West European
		'WE8EBCDIC1145'=>'', //EBCDIC Code Page 1145 8-bit West European
		'WE8EBCDIC1146'=>'', //EBCDIC Code Page 1146 8-bit West European
		'WE8EBCDIC1148C'=>'', //EBCDIC Code Page 1148 Client 8-bit West European
		'WE8EBCDIC1148'=>'', //EBCDIC Code Page 1148 8-bit West European
		'WE8EBCDIC284'=>'', //EBCDIC Code Page 284 8-bit Latin American/Spanish
		'WE8EBCDIC285'=>'', //EBCDIC Code Page 285 8-bit West European
		'WE8EBCDIC37C'=>'', //EBCDIC Code Page 37 8-bit Oracle/c
		'WE8EBCDIC37'=>'', //EBCDIC Code Page 37 8-bit West European
		'WE8EBCDIC500C'=>'', //EBCDIC Code Page 500 8-bit Oracle/c
		'WE8EBCDIC500'=>'', //EBCDIC Code Page 500 8-bit West European
		'WE8EBCDIC871'=>'', //EBCDIC Code Page 871 8-bit Icelandic
		'WE8EBCDIC924'=>'', //Latin 9 EBCDIC 924
		'WE8GCOS7'=>'', //Bull EBCDIC GCOS7 8-bit West European
		'WE8HP'=>'', //HP LaserJet 8-bit West European
		'WE8ICL'=>'', //ICL EBCDIC 8-bit West European
		'IW8ISO8859P8'=>'ISO-8859-8', //ISO 8859-8 Latin/Hebrew
		'WE8ISO8859P9'=>'ISO-8859-9', //ISO 8859-9 West European & Turkish
		'AZ8ISO8859P9E'=>'ISO-8859-9E', //ISO 8859-9 Azerbaijani
		'WE8ISOICLUK'=>'', //ICL special version ISO8859-1
		'WE8MACROMAN8S'=>'', //Mac Server 8-bit Extended Roman8 West European
		'WE8MACROMAN8'=>'', //Mac Client 8-bit Extended Roman8 West European
		'WE8MSWIN1252'=>'', //MS Windows Code Page 1252 8-bit West European
		'WE8NCR4970'=>'', //NCR 4970 8-bit West European
		'WE8NEXTSTEP'=>'', //NeXTSTEP PostScript 8-bit West European
		'WE8PC850'=>'', //IBM-PC Code Page 850 8-bit West European
		'WE8PC858'=>'', //IBM-PC Code Page 858 8-bit West European
		'WE8PC860'=>'', //IBM-PC Code Page 860 8-bit West European
		'WE8ROMAN8'=>'', //HP Roman8 8-bit West European
		'YUG7ASCII'=>'', //ASCII 7-bit Yugoslavian
		'ZHS16CGB231280'=>'', //CGB2312-80 16-bit Simplified Chinese
		'ZHS16DBCS'=>'', //IBM EBCDIC 16-bit Simplified Chinese
		'ZHS16GBK'=>'', //GBK 16-bit Simplified Chinese
		'ZHS16MACCGB231280'=>'', //Mac client CGB2312-80 16-bit Simplified Chinese
		'ZHT16BIG5'=>'', //BIG5 16-bit Traditional Chinese
		'ZHT16CCDC'=>'', //HP CCDC 16-bit Traditional Chinese
		'ZHT16DBCS'=>'', //IBM EBCDIC 16-bit Traditional Chinese
		'ZHT16DBT'=>'', //Taiwan Taxation 16-bit Traditional Chinese
		'ZHT16HKSCS31'=>'', //MS Windows Code Page 950 with Hong Kong Supplementary Character Set HKSCS-2001 (character set conversion to and from Unicode is based on Unicode 3.1)
		'ZHT16HKSCS'=>'', //MS Windows Code Page 950 with Hong Kong Supplementary Character Set HKSCS-2001 (character set conversion to and from Unicode is based on Unicode 3.0)
		'ZHT16MSWIN950'=>'', //MS Windows Code Page 950 Traditional Chinese
		'ZHT32EUC'=>'', //EUC 32-bit Traditional Chinese
		'ZHT32SOPS'=>'', //SOPS 32-bit Traditional Chinese
		'ZHT32TRIS'=>'', //TRIS 32-bit Traditional Chinese
		'AL24UTFFSS'=>'UTF-8', //
		'UTF8'=>'UTF-8', //
		'UTFE'=>'EBCDICUS', //
		'AL32UTF8'=>'UTF-8', //
		'AL16UTF16'=>'UTF-16', //		
	];
	
	public function __construct($splitConn=null){
		parent::__construct($splitConn);
		//$this->export_variables();
		$this->readOnly['conn']=$this->connect($this->readOnly['host'], @$this->readOnly['user'], $this->readOnly['pass']);
		//print_r($this->readOnly); 
		//$this->checkConnetctionSelectDb();
	}
	public function rebildConnVars($splitConn){
		parent::rebildConnVars($splitConn);
		$this->readOnly['host']=$splitConn['host'];
	}
	public function connect($host='localhost',$user='root',$passwd=''){
		//$error_reporting=ini_get('error_reporting');
		//error_reporting(0);
		error_reporting(0);
		try {
		//print __LINE__." :{$this->readOnly['user']}:{$this->readOnly['pass']}@{$this->readOnly['host']}\n"; exit;
			return oci_connect($user, $passwd, $host); //,null,OCI_SYSDBA
		} catch (PDOException $e) {
			$this->fatalError($e->getMessage());
		}
		restore_error_handler();
		//error_reporting($error_reporting);
	}
	public function export_variables(){
		$this->readOnly['version']=isset($this->readOnly['dsn']['version'])?($this->readOnly['dsn']['version']==10?10:11):11;
		$c=Config::singleton();
		exec(". {$c->ini}/export_ora{$this->readOnly['version']}.sh");
	}
	public function close(){
		if(!$this->readOnly['conn']) return false;
		@oci_close($this->readOnly['conn']);
		parent::close();
	}
	public function select_db($db){}
	public function error() { 
		if(is_null($this->readOnly['conn']) || $this->readOnly['conn']===false) {
			$e=oci_error();
			return @$e['message']?$e['message']:'Without Connection';
		}
		$e=oci_error($this->readOnly['conn']);
		return @$e['message'];
	}
	public function errno() { 
		$e=oci_error($this->readOnly['conn']);
		return $e['code'];
	}
	public function commit(){ return oci_commit($this->readOnly['conn']); }
	public function autocommit($bool){ return $this->autocommit=$bool; }
	public function change_user($user='root'){ 
		$this->connect($this->readOnly['host'],$user,$this->readOnly['pass']);
		return @$this->readOnly['conn']?true:false; 
	}
	public function affected_rows(){ return oci_num_rows($this->readOnly['conn']); }
	public function insert_id($sequence){ 
		$line=$this->fastline("SELECT $sequence.CURRVAL AS INSERT_ID FROM DUAL");
		return @$line['INSERT_ID']+0;
	}
	public function get_client_info(){ 
		$line=$this->fastline("SELECT UTL_INADDR.GET_HOST_NAME || ' - ' || UTL_INADDR.GET_HOST_ADDRESS as HOSTNAME FROM DUAL");
		return @$line['HOSTNAME'];
	}
	public function ping(){ 
		//verifica se conn está ativa //FIXME
		$line=$this->fastline("SELECT 1 AS TEST FROM DUAL");
		if(!@$line['TEST']) $this->connect($this->readOnly['host'], $this->readOnly['user'],$this->readOnly['pass']);
		return @$this->readOnly['conn']?true:false; 
	}
	public function get_server_info(){ 
		//select * from v$version
		$line=$this->fastline('SELECT BANNER FROM SYS.V_$VERSION WHERE ROWNUM=1');
		return @$line['BANNER'];
	}
	public function merge($tblTo,$line=null,$keysC=null,$caracater='.'){
		static $keyComp=array();
		static $keys=array();
		static $sum=array();
		
		if($line) {
			if(!@$keys[$tblTo]) {
				$keyComp[$tblTo]=array_flip(preg_split('/\s*[;,]\s*/',$keysC));
				$keys[$tblTo]=$this->mountFieldsKeys($line); 
				$sum[$tblTo]=0;
			}
			$where=$this->mountFieldsConpareValues(array_intersect_key($line, $keyComp[$tblTo]));
			$set=$this->mountFieldsSetValues(array_diff_key($line, $keyComp[$tblTo]));
			$sql="MERGE INTO $tblTo USING dual ON ($where) ";
			$sql.="WHEN MATCHED THEN UPDATE SET $set ";
			$sql.="WHEN NOT MATCHED THEN INSERT ({$keys[$tblTo]}) VALUES {$this->mountValueInsertLine($line)}";

			//show($sql);
			$this->query($sql);
			$sum[$tblTo]++;
			if($sum[$tblTo] % 100==0) print $caracater;
		}
		if(!@$keys[$tblTo]) return 0;
		$out=$sum[$tblTo];
		if(!$line) {
			unset($keys[$tblTo]);
			$sum[$tblTo]=0;
		}
		return $out;
	}
	public function addQuote($value){
		if(is_numeric($value)) return $this->stringDelimiter($this->escape_string($value));
		return parent::addQuote($value);
	}
	public function fieldCompareValue($field,$value){
		if(is_numeric($value)) return $this->fieldDelimiter($field).'='.$this->stringDelimiter($value);
		return parent::fieldCompareValue($field,$value);
	}
	/*
	function multi_query($sql){ return $this->query($sql); }
	function more_results(){ return $this->readOnly['conn']->more_results(); }
	function store_result(){ return $this->readOnly['conn']->store_result(); }
	function next_result(){ return $this->readOnly['conn']->next_result(); }
	function use_result(){ return $this->readOnly['conn']->use_result(); }


	function get_charset(){return @$this->readOnly['conn']->get_charset(); }
	function set_charset($charset){return @$this->readOnly['conn']->set_charset($charset); }
	*/
	public function showDatabases(){
		return $this->query_all('
			SELECT 
				u.USERNAME "SCHEMA", 
				u.USER_ID "DOMAIN",
				u.USERNAME "OWNER",
				NULL DEFAULT_CHARACTER_SET_NAME,
				NULL DEFAULT_COLLATION_NAME,
				NULL DEFAULT_CHARACTER_SET_CATALOG, 
				NULL DEFAULT_CHARACTER_SET_SCHEMA,
				NULL SQL_PATH
				-- u.CREATED, u.COMMON, u.ORACLE_MAINTAINED
			FROM ALL_USERS u 
			ORDER BY USERNAME
		');
	}
	public function showTables($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showViews($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showFunctions($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showProcedures($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showEvents($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showAllObjects($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function charset($conv=false){
		//$l=$this->connXTTs->fastValue("SELECT VALUE FROM NLS_DATABASE_PARAMETERS WHERE PARAMETER='NLS_NCHAR_CHARACTERSET'");
		$c=$this->fastValue("
			SELECT PROPERTY_VALUE 
			FROM DATABASE_PROPERTIES 
			WHERE PROPERTY_NAME='NLS_CHARACTERSET'
		");
		return $conv?$this->charset_tr($c):$c;
	}
	public function charset_nchar($conv=false){
		$c=$this->fastValue("
			SELECT PROPERTY_VALUE 
			FROM DATABASE_PROPERTIES 
			WHERE PROPERTY_NAME='NLS_NCHAR_CHARACTERSET'
		");
		return $conv?$this->charset_tr($c):$c;
	}
	public function charset_tr($charset){
		($c=@$this->charsets[$charset]) || $c='ASCII';
		return $c;
	}
	public function get_ddl_all($obj,$tp='TABLE'){ // TP=TABLE|VIEW|MATERIALIZED_VIEW|TABLESPACE|USER|ROLE
		//return $this->query_all("SELECT * FROM USER_OBJECTS WHERE OBJECT_NAME='$obj'");
		return $this->query_all("SELECT * FROM USER_OBJECTS WHERE OBJECT_NAME='$obj'");
		//select dbms_metadata.get_ddl(object_type, object_name) ddl  from user_objects where object_type = 'TABLE'; 
		//$fn=__FUNCTION__;
		//return $this->fastValue("SELECT dbms_metadata.$fn('$tp','$obj') D FROM DUAL");
	}
	public function get_ddl($obj,$tp='TABLE'){ // TP=TABLE|VIEW|MATERIALIZED_VIEW|TABLESPACE|USER|ROLE
		//return $this->query_all("SELECT * FROM USER_OBJECTS WHERE OBJECT_NAME='$obj'");
		return $this->fastValue("SELECT dbms_metadata.get_ddl(OBJECT_TYPE, OBJECT_NAME) FROM USER_OBJECTS WHERE OBJECT_NAME='$obj'")->load();
		//select dbms_metadata.get_ddl(object_type, object_name) ddl  from user_objects where object_type = 'TABLE'; 
		$fn=__FUNCTION__;
		return $this->fastValue("SELECT dbms_metadata.$fn('$tp','$obj') D FROM DUAL");
	}
	public function get_dependent_ddl($obj,$tp='CONSTRAINT'){ // TP=CONSTRAINT|INDEX|TRIGGER|REF_CONSTRAINT
		$fn=__FUNCTION__;
		return $this->fastValue("SELECT dbms_metadata.$fn('$tp','$obj') D FROM DUAL");
	}
	public function get_granted_ddl($obj,$tp='SYSTEM_GRANT'){ // TP=SYSTEM_GRANT|ROLE_GRANT|OBJECT_GRANT
		$fn=__FUNCTION__;
		return $this->fastValue("SELECT dbms_metadata.$fn('$tp','$obj') D FROM DUAL");
	}
}
class Conn_oracle_result extends Conn_Main_result {
	public function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
		$error_reporting=ini_get('error_reporting');
		error_reporting(0);
		oci_execute($this->res=oci_parse($this->conn, $sql), ($this->conn->autocommit?OCI_COMMIT_ON_SUCCESS:OCI_DEFAULT));
		error_reporting($error_reporting);
		$this->verifyError($sql);
	}
	public function close(){
		if(!$this->res) return;
		$this->free_result();
		$this->res=null;
	}
	public function fetch(){ return @oci_fetch($this->res); }
	public function fetch_all(&$output,$skip=0,$maxrows=-1,$flags=0){ return @oci_fetch_all($this->res,$output,$skip,$maxrows,$flags); }
	public function fetch_assoc(){ return @oci_fetch_assoc($this->res); }
	public function fetch_array($resulttype){ return @oci_fetch_array($this->res,$resulttype); }
	public function fetch_object(){ return @oci_fetch_object($this->res); }
	public function fetch_row(){ return @oci_fetch_row($this->res); }

	public function field_is_null($field){ return @oci_field_is_null($this->res,$field+1); }
	public function field_name($field){ return @oci_field_name($this->res,$field+1); }
	public function field_precision($field){ return @oci_field_precision($this->res,$field+1); }
	public function field_scale($field){ return @oci_field_scale($this->res,$field+1); }
	public function field_size($field){ return @oci_field_size($this->res,$field+1); }
	public function field_type($field){ return @oci_field_type($this->res,$field+1); }
	public function field_type_raw($field){ return @oci_field_type_raw($this->res,$field+1); }

	public function internal_debug($onoff){ return @oci_internal_debug($onoff); }
	public function free_result(){ return @oci_free_statement($this->res); }
	public function num_fields(){ return @oci_num_fields($this->res); }
	public function num_rows(){ 
		static $num=null;
		if($num!==null) return $num;
		if(!preg_match('/^(\s|\()*SELECT\b/i',$this->sql)) return null;
		$sql="SELECT COUNT(1) AS QUANT FROM ( \n".preg_replace('/(\s|;)+$/','',$this->sql)." \n) T";
		$error_reporting=ini_get('error_reporting');
		error_reporting(0);
		oci_execute($res=oci_parse($this->conn, $sql), OCI_DEFAULT);
		error_reporting($error_reporting);
		$line=@oci_fetch_assoc($res);
		return $num=@$line['QUANT']+0;
	}
	/*

	function data_seek($offset){ return @$this->res->data_seek($offset); }
	
	//retorna numero de campos
	function fetch_field_direct($fieldnr){ return @$this->res->fetch_field_direct($fieldnr); }
	function field_seek($fieldnr){ return @$this->res->field_seek($fieldnr); }
	function current_field(){ return @$this->res->current_field; }
	function lengths(){ return @$this->res->lengths; }
	*/
	public function error() { 
		$e=@oci_error($this->res);
		return $e['message'];
	}
	public function errno() { 
		$e=@oci_error($this->res);
		return $e['code'];
	}
}
class Conn_oracle_result_field extends Conn_Main_result_field{
	public $dataTypes=array(
		'bit'=>array(
			'number'     =>array('length'=>38,               'descr'=>'Variable-length numeric data. Maximum precision p and/or scale s is 38.'),
		),
		'int'=>array(
			'number'     =>array('length'=>38,               'descr'=>'Variable-length numeric data. Maximum precision p and/or scale s is 38.'),
		),
		'dec'=>array(
			'number'     =>array('length'=>38,               'descr'=>'Variable-length numeric data. Maximum precision p and/or scale s is 38.'),
		),
		'float'=>array(
			'number'     =>array('length'=>38,               'descr'=>'Variable-length numeric data. Maximum precision p and/or scale s is 38.'),
		),
		'datetime'=>array(
			'date'       =>array('length'=>null,             'descr'=>'Fixed-length date and time data, ranging from Jan. 1, 4712 B.C.E. to Dec. 31, 4712 C.E.'),
		),
		'char'=>array(
			'nchar'      =>array('length'=>2000,             'descr'=>'Fixed-length character data of length size characters or bytes, depending on the national character set.'),
			'char'       =>array('length'=>2000,             'descr'=>'Fixed-length character data of length size bytes.'),
		),
		'string'=>array(
			'nvarchar2'  =>array('length'=>4000,             'descr'=>'Variable-length character data of length size characters or bytes, depending on national character set. A maximum size must be specified.'),
			'varchar2'   =>array('length'=>4000,             'descr'=>'Variable-length character data.'),
		),
		'text'=>array(
			'long'       =>array('length'=>2199023255552,    'descr'=>'Variable-length character data.'),
		),
		'binary'=>array(
			'raw'        =>array('length'=>2000,             'descr'=>'Variable-length raw binary data.'),
			'long raw'   =>array('length'=>2199023255552,    'descr'=>'Variable-length raw binary data.'),
		),
		'lob'=>array(
			'nclob'      =>array('length'=>4398046511104,    'descr'=>'Single-byte or fixed-length multibyte national character set (NCHAR) data.'),
			'clob'       =>array('length'=>4398046511104,    'descr'=>'Single-byte character data.'),
			'blob'       =>array('length'=>4398046511104,    'descr'=>'Unstructured binary data.'),
			'bfile'      =>array('length'=>4398046511104,    'descr'=>'Binary data stored in an external file.'),
		),
		'others'=>array(
			'rowid'      =>array('length'=>null,             'descr'=>'Binary data representing row addresses.'),
			'mlslabel'   =>array('length'=>null,             'descr'=>'Trusted Oracle datatype.'),
		),
	);
	public $startFieldDelimiter='"';
	public $endFieldDelimiter='"';

	public function __construct($index=null,$res=null,$oConn=null){
		parent::__construct($index,$res,$oConn);
		if(!$res) return;

		$this->name=$this->orgname=@oci_field_name($res,$index+1);
		$this->table=$this->orgtable=null;
		$this->max_length=@oci_field_size($res,$index+1);//o limite de tamanho da coluna 
		$this->length=@oci_field_precision($res,$index+1);
		$this->vartype=@oci_field_type($res,$index+1);//o tipo da coluna 
		$this->type=$this->charsetnr=@oci_field_type_raw($res,$index+1);//o tipo da coluna 
		$this->decimals=$this->scale=@oci_field_scale($res,$index+1);

		//$o->not_null=!@oci_field_is_null($res,$index+1);//1 se a coluna não puder ser NULL 
		//$o->primary_key=null;//1 se a coluna é a chave primária 
		//$o->unique_key=null;//1 se a coluna é a chave única 
		//$o->multiple_key=null;//1 se a coluna é uma chave não-única 
		//$o->numeric=null;//1 se a coluna é numérica 
		//$o->unsigned=null;//1 se a coluna é sem sinal 
		//$o->blob=null;//1 se a coluna é um BLOB 
		//$o->zerofill=null;//1 se a coluna é prenchida com zero 
		//$this->flags=null;
		//$this->mysqlExtra=$fld;
	}
}
