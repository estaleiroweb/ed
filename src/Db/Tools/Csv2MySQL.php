<?php
class Csv2MySQL {
	use \Traits\OO\GetterAndSetter;
	const TAB = "\t";

	public function __construct($param = null) {
		$this->protect = [
			'file' => null, //NOTE: URL or Local File <required>
			'table' => null, //NOTE: INTO TABLE <required>
			'db' => null, //NOTE: schemma
			'conn' => null, //NOTE: Connection to MySQL Database [Default '']

			'pc_before' => null, //NOTE: Stored Procedure antes de carregar 
			'pc_after' => null, //NOTE: Stored Procedure depois de carregar 

			'deleteFile' => false, //NOTE: Apagar arquivo após carregar
			'tmpDir' => sys_get_temp_dir(), //NOTE: Pasta temporária
			'erCutCommentHead' => '/^\s*#\s*/', //NOTE: Expressão regular para retirar comentário no Cabeçalho
			'sed' => null, //NOTE: parametro do comando sed (sed -ri '<param>' FILE) para retirar linhas ou editar

			'priority' => null, //NOTE: [LOW_PRIORITY | CONCURRENT]
			'charset' => null, //NOTE: [CHARACTER SET charset_name]
			'method' => null, //NOTE: [REPLACE | IGNORE | UPDATE]
			'init' => null, //NOTE: [TRUNCATE | DROPCREATE]

			'skipLines' => null,
			'head' => true, //NOTE: true|false tem/não cabeçalho, caso não exista, passar cabeçalho por array no delimiter ou fields
			'nullCode' => '\\N', //NOTE: Null code
			'maxLenFile' => 10000, //NOTE: fragmenta arquivo acima do configurado
			//'maxLenFile' => 100000000, //NOTE: fragmenta arquivo acima do configurado

			// ! delimiter pode ser array quando for delimitado por tamanho de campo ['Campo1'=>Inicio,'Campo2'=>Inicio,]
			'delimiter' => "\t", // NOTE: [FIELDS TERMINATED BY 'string']
			'enclose_optional' => true, // NOTE: [FIELDS OPTIONALLY ENCLOSED BY 'string']
			'enclose' => null, // NOTE: [FIELDS ENCLOSED BY 'string']
			'escape' => '\\', // NOTE: [FIELDS ESCAPED BY 'string']

			// ! alias terminator
			'line_terminator' => "\n", // NOTE: [LINES TERMINATED BY 'string']
			'line_start' => null, // NOTE: [LINES STARTING BY 'string']

			/**
			 * NOTE: Array associativo com nome de campo => configurações
			 * 	{
			 * 		"type": "string",
			 * 		"dateFormat": "",
			 * 		"timeFormat": "",
			 * 		"milSep": "",
			 * 		"decSep": "",
			 * 		"default": "",
			 * 		"label": "Serial number",
			 * 		"import": "1",
			 * 		"comment": "'[Serial_number]'",
			 * 		"name": "Serial_number",
			 * 		"t": "varchar(23) COMMENT '[Serial_number]'",
			 * 		"fn": "parser_field_string"
			 * 	}
			 */
			'fields' => null,
		];
		$this->readonly = [
			'lenFile' => null,
			'headFile' => null,

			'er' => [],
			'erCut' => '',

			'sqlIni' => [],
			'sqlFim' => [],

			'fieldsUpdate' => '',
			'loadDataFields' => '',
			'loadDataSet' => '',
		];

		if ($param) $this->initProtect();
	}
	public function __destruct() {
		if ($this->deleteFile) {
			$files = (array)$this->file;
			foreach ($files as $file) if ($file != '') `rm -f '{$file}'`;
		}
	}
	public function __toString() {
		return "LOAD DATA
			[LOW_PRIORITY | CONCURRENT] [LOCAL]
			INFILE 'file_name'
			[REPLACE | IGNORE]
			INTO TABLE tbl_name
			[PARTITION (partition_name [, partition_name] ...)]
			[CHARACTER SET charset_name]
			[{FIELDS | COLUMNS}
				[TERMINATED BY 'string']
				[[OPTIONALLY] ENCLOSED BY 'char']
				[ESCAPED BY 'char']
			]
			[LINES
				[STARTING BY 'string']
				[TERMINATED BY 'string']
			]
			[IGNORE number {LINES | ROWS}]
			[(col_name_or_user_var
				[, col_name_or_user_var] ...)]
			[SET col_name={expr | DEFAULT}
				[, col_name={expr | DEFAULT}] ...]
		";
	}
	public function __invoke($param = null) {
		if ($param) $this->initProtect();

		//NOTE: Inicializa
		$sqlDefault = is_array($this->delimiter) ? $this->build_len() : $this->build_delimiter();
		$sqlDefault = implode("\n", $sqlDefault) . ';';

		//NOTE: Executa queryies - Início
		$this->query($this->pc_before, 'Executing Stored Procedure Before');
		$this->query($this->sqlIni, 'Preparing');

		//NOTE: Executa queryies - Carregamento
		$aSQL = [];
		$files = $this->file;
		foreach ($files as $k => $file) {
			$file = escapeString($file);
			$aSQL[] = str_replace('<FILE_NAME>', $file, str_replace('<TABLE_NAME>', $this->table, $sqlDefault));
		}
		$this->query($aSQL, 'Loading');

		//NOTE: Executa queryies - Fim
		$this->query($this->sqlFim, 'Finishing');
		$this->query($this->pc_after, 'Executing Stored Procedure After');

		return $this;
	}

	public function set_file($val) {
		if (preg_match('/^\w+:\/\//', $val)) { //URL
			$this->deleteFile = true;
			$file_name = tempnam($this->tmpDir, __CLASS__ . '_');
			disable_error_handler();
			if (!file_put_contents($file_name, @file_get_contents($val))) $this->fatalError('URL não encontrada');
			restore_error_handler();
			$val = $file_name;
		} else { //LOCAL
			if (!is_file($val)) $this->fatalError('Arquivo não encontrado');
		}
		$this->readonly['lenFile'] = filesize($val);
		$this->protect['file'] = $val;
		return $this;
	}
	public function set_table($val) {
		$this->protect['table'] = $val;
		return $this;
	}
	public function set_targetTable($val) {
		return $this->set_table($val);
	}
	public function set_targetDB($val) {
		$this->protect['db'] = $val;
		return $this;
	}
	public function set_pc_after($val) {
		$this->protect['pc_after'] = preg_replace('/^\s*(?:CALL\s+)?(.*)$/','CALL \\1;',$val);
		return $this;
	}
	public function set_pc($val) {
		return $this->set_pc_after($val);
	}
	public function set_procedure($val) {
		return $this->set_pc_after($val);
	}
	public function set_pc_before($val) {
		$this->protect['pc_before'] = preg_replace('/^\s*(?:CALL\s+)?(.*)$/','CALL \\1;',$val);
		return $this;
	}
	public function set_procedureBefore($val) {
		return $this->set_pc_before($val);
	}
	public function set_procedure_before($val) {
		return $this->set_pc_before($val);
	}

	public function set_terminator($val) {
		$this->protect['line_terminator'] = $val;
		return $this;
	}
	public function set_priority($val) {
		return $this->enum('priority', $val, [0 => 'LOW_PRIORITY', 1 => 'CONCURRENT']);
	}
	public function set_method($val) {
		return $this->enum('method', $val, ['REPLACE', 'IGNORE', 'UPDATE']);
	}
	public function set_targetInsert($val) {
		return $this->set_method($val);
	}
	public function set_init($val) {
		return $this->enum('init', $val, ['TRUNCATE', 'DROPCREATE', 'CREATE']);
	}
	public function set_targetCreate($val) {
		return $this->set_init($val);
	}
	public function set_charset($val) {
		//SHOW CHARACTER SET;
		static $arr_old = [
			'ASCII', 'UTF8', 'LATIN1', 'BINARY',

			'UTF8MB4', 'UTF16', 'UTF16LE', 'UTF32',
			'LATIN2', 'LATIN5', 'LATIN7',
			'CP850', 'CP852', 'CP866', 'CP932', 'CP1250', 'CP1251', 'CP1256', 'CP1257',

			'ARMSCII8',
			'BIG5',
			'DEC8',
			'EUCKR', 'EUCJPMS',
			'GB2312', 'GBK', 'GEOSTD8', 'GREEK',
			'HEBREW', 'HP8',
			'KEYBCS2', 'KOI8R', 'KOI8U',
			'MACCE', 'MACROMAN',
			'TIS620',
			'SJIS', 'SWE7',
			'UCS2', 'UJIS',
		];
		static $arr = [
			'437'                     => 'CP850',
			'500'                     => 'CP850',
			'500V1'                   => 'CP850',
			'850'                     => 'CP850',
			'851'                     => 'CP850',
			'852'                     => 'CP852',
			'855'                     => 'CP852',
			'856'                     => 'CP852',
			'857'                     => 'CP852',
			'860'                     => 'CP852',
			'861'                     => 'CP852',
			'862'                     => 'CP852',
			'863'                     => 'CP852',
			'864'                     => 'CP852',
			'865'                     => 'CP852',
			'866'                     => 'CP866',
			'866NAV'                  => 'CP866',
			'869'                     => 'CP866',
			'874'                     => 'CP866',
			'904'                     => 'CP932',
			'1026'                    => 'CP932',
			'1046'                    => 'CP932',
			'1047'                    => 'CP932',
			'88591'                   => 'LATIN1',
			'88592'                   => 'LATIN2',
			'88593'                   => 'LATIN2',
			'88594'                   => 'LATIN2',
			'88595'                   => 'LATIN5',
			'88596'                   => 'LATIN7',
			'88597'                   => 'LATIN7',
			'88598'                   => 'LATIN7',
			'88599'                   => 'LATIN7',
			'1064611993'              => 'UCS2',
			'1064611993UCS4'          => 'UCS2',
			'ANSIX31101983'           => 'CP1256',
			'ANSIX3110'               => 'CP1256',
			'ANSIX341968'             => 'CP1256',
			'ANSIX341986'             => 'CP1256',
			'ANSIX34'                 => 'CP1256',
			'ARABIC'                  => 'LATIN1',
			'ARABIC7'                 => 'ARMSCII8',
			'ARMSCII8'                => 'ARMSCII8',
			'ASCII'                   => 'ASCII',
			'ASMO708'                 => 'LATIN7',
			'ASMO449'                 => 'LATIN7',
			'BALTIC'                  => 'CP850',
			'BIG5'                    => 'BIG5',
			'BIGFIVE'                 => 'BIG5',
			'BIG5HKSCS'               => 'BIG5',
			'BINARY'                  => 'BINARY',
			'CPAR'                    => 'CP850',
			'CPGR'                    => 'CP850',
			'CPHU'                    => 'CP850',
			'CP037'                   => 'CP850',
			'CP038'                   => 'CP850',
			'CP273'                   => 'CP850',
			'CP274'                   => 'CP850',
			'CP275'                   => 'CP850',
			'CP278'                   => 'CP850',
			'CP280'                   => 'CP850',
			'CP281'                   => 'CP850',
			'CP282'                   => 'CP850',
			'CP284'                   => 'CP850',
			'CP285'                   => 'CP850',
			'CP290'                   => 'CP850',
			'CP297'                   => 'CP850',
			'CP367'                   => 'CP850',
			'CP420'                   => 'CP850',
			'CP423'                   => 'CP850',
			'CP424'                   => 'CP850',
			'CP437'                   => 'CP850',
			'CP500'                   => 'CP850',
			'CP737'                   => 'CP850',
			'CP775'                   => 'CP850',
			'CP803'                   => 'CP850',
			'CP813'                   => 'CP850',
			'CP819'                   => 'CP850',
			'CP850'                   => 'CP850',
			'CP851'                   => 'CP850',
			'CP852'                   => 'CP852',
			'CP855'                   => 'CP852',
			'CP856'                   => 'CP852',
			'CP857'                   => 'CP852',
			'CP860'                   => 'CP852',
			'CP861'                   => 'CP852',
			'CP862'                   => 'CP852',
			'CP863'                   => 'CP852',
			'CP864'                   => 'CP852',
			'CP865'                   => 'CP852',
			'CP866'                   => 'CP866',
			'CP866NAV'                => 'CP866',
			'CP868'                   => 'CP866',
			'CP869'                   => 'CP866',
			'CP870'                   => 'CP866',
			'CP871'                   => 'CP866',
			'CP874'                   => 'CP866',
			'CP875'                   => 'CP866',
			'CP880'                   => 'CP866',
			'CP891'                   => 'CP866',
			'CP901'                   => 'CP866',
			'CP902'                   => 'CP866',
			'CP903'                   => 'CP866',
			'CP904'                   => 'CP866',
			'CP905'                   => 'CP866',
			'CP912'                   => 'CP866',
			'CP915'                   => 'CP866',
			'CP916'                   => 'CP866',
			'CP918'                   => 'CP866',
			'CP920'                   => 'CP866',
			'CP921'                   => 'CP866',
			'CP922'                   => 'CP866',
			'CP930'                   => 'CP866',
			'CP932'                   => 'CP932',
			'CP933'                   => 'CP932',
			'CP935'                   => 'CP932',
			'CP936'                   => 'CP932',
			'CP937'                   => 'CP932',
			'CP939'                   => 'CP932',
			'CP949'                   => 'CP932',
			'CP950'                   => 'CP932',
			'CP1004'                  => 'CP932',
			'CP1008'                  => 'CP932',
			'CP1025'                  => 'CP932',
			'CP1026'                  => 'CP932',
			'CP1046'                  => 'CP932',
			'CP1047'                  => 'CP932',
			'CP1070'                  => 'CP932',
			'CP1079'                  => 'CP932',
			'CP1081'                  => 'CP932',
			'CP1084'                  => 'CP932',
			'CP1089'                  => 'CP932',
			'CP1097'                  => 'CP932',
			'CP1112'                  => 'CP932',
			'CP1122'                  => 'CP932',
			'CP1123'                  => 'CP932',
			'CP1124'                  => 'CP932',
			'CP1125'                  => 'CP932',
			'CP1129'                  => 'CP932',
			'CP1130'                  => 'CP932',
			'CP1132'                  => 'CP932',
			'CP1133'                  => 'CP932',
			'CP1137'                  => 'CP932',
			'CP1140'                  => 'CP932',
			'CP1141'                  => 'CP932',
			'CP1142'                  => 'CP932',
			'CP1143'                  => 'CP932',
			'CP1144'                  => 'CP932',
			'CP1145'                  => 'CP932',
			'CP1146'                  => 'CP932',
			'CP1147'                  => 'CP932',
			'CP1148'                  => 'CP932',
			'CP1149'                  => 'CP932',
			'CP1153'                  => 'CP932',
			'CP1154'                  => 'CP932',
			'CP1155'                  => 'CP932',
			'CP1156'                  => 'CP932',
			'CP1157'                  => 'CP932',
			'CP1158'                  => 'CP932',
			'CP1160'                  => 'CP932',
			'CP1161'                  => 'CP932',
			'CP1162'                  => 'CP932',
			'CP1163'                  => 'CP932',
			'CP1164'                  => 'CP932',
			'CP1166'                  => 'CP932',
			'CP1167'                  => 'CP932',
			'CP1250'                  => 'CP1250',
			'CP1251'                  => 'CP1251',
			'CP1252'                  => 'CP1251',
			'CP1253'                  => 'CP1251',
			'CP1254'                  => 'CP1251',
			'CP1255'                  => 'CP1251',
			'CP1256'                  => 'CP1256',
			'CP1257'                  => 'CP1257',
			'CP1258'                  => 'CP1257',
			'CP1282'                  => 'CP1257',
			'CP1361'                  => 'CP1257',
			'CP1364'                  => 'CP1257',
			'CP1371'                  => 'CP1257',
			'CP1388'                  => 'CP1257',
			'CP1390'                  => 'CP1257',
			'CP1399'                  => 'CP1257',
			'CP4517'                  => 'CP1257',
			'CP4899'                  => 'CP1257',
			'CP4909'                  => 'CP1257',
			'CP4971'                  => 'CP1257',
			'CP5347'                  => 'CP1257',
			'CP9030'                  => 'CP1257',
			'CP9066'                  => 'CP1257',
			'CP9448'                  => 'CP1257',
			'CP10007'                 => 'CP1257',
			'CP12712'                 => 'CP1257',
			'CP16804'                 => 'CP1257',
			'CPIBM861'                => 'CP1257',
			'CSIBM037'                => 'CP850',
			'CSIBM038'                => 'CP850',
			'CSIBM273'                => 'CP850',
			'CSIBM274'                => 'CP850',
			'CSIBM275'                => 'CP850',
			'CSIBM277'                => 'CP850',
			'CSIBM278'                => 'CP850',
			'CSIBM280'                => 'CP850',
			'CSIBM281'                => 'CP850',
			'CSIBM284'                => 'CP850',
			'CSIBM285'                => 'CP850',
			'CSIBM290'                => 'CP850',
			'CSIBM297'                => 'CP850',
			'CSIBM420'                => 'CP850',
			'CSIBM423'                => 'CP850',
			'CSIBM424'                => 'CP850',
			'CSIBM500'                => 'CP850',
			'CSIBM803'                => 'CP850',
			'CSIBM851'                => 'CP850',
			'CSIBM855'                => 'CP852',
			'CSIBM856'                => 'CP852',
			'CSIBM857'                => 'CP852',
			'CSIBM860'                => 'CP852',
			'CSIBM863'                => 'CP852',
			'CSIBM864'                => 'CP852',
			'CSIBM865'                => 'CP852',
			'CSIBM866'                => 'CP866',
			'CSIBM868'                => 'CP932',
			'CSIBM869'                => 'CP932',
			'CSIBM870'                => 'CP932',
			'CSIBM871'                => 'CP932',
			'CSIBM880'                => 'CP932',
			'CSIBM891'                => 'CP932',
			'CSIBM901'                => 'CP932',
			'CSIBM902'                => 'CP932',
			'CSIBM903'                => 'CP932',
			'CSIBM904'                => 'CP932',
			'CSIBM905'                => 'CP932',
			'CSIBM918'                => 'CP932',
			'CSIBM921'                => 'CP932',
			'CSIBM922'                => 'CP932',
			'CSIBM930'                => 'CP932',
			'CSIBM932'                => 'CP932',
			'CSIBM933'                => 'CP1250',
			'CSIBM935'                => 'CP1250',
			'CSIBM937'                => 'CP1250',
			'CSIBM939'                => 'CP1250',
			'CSIBM943'                => 'CP1250',
			'CSIBM1008'               => 'CP1250',
			'CSIBM1025'               => 'CP1250',
			'CSIBM1026'               => 'CP1250',
			'CSIBM1097'               => 'CP1250',
			'CSIBM1112'               => 'CP1250',
			'CSIBM1122'               => 'CP1250',
			'CSIBM1123'               => 'CP1250',
			'CSIBM1124'               => 'CP1250',
			'CSIBM1129'               => 'CP1250',
			'CSIBM1130'               => 'CP1250',
			'CSIBM1132'               => 'CP1250',
			'CSIBM1133'               => 'CP1250',
			'CSIBM1137'               => 'CP1250',
			'CSIBM1140'               => 'CP1250',
			'CSIBM1141'               => 'CP1250',
			'CSIBM1142'               => 'CP1250',
			'CSIBM1143'               => 'CP1250',
			'CSIBM1144'               => 'CP1250',
			'CSIBM1145'               => 'CP1250',
			'CSIBM1146'               => 'CP1250',
			'CSIBM1147'               => 'CP1250',
			'CSIBM1148'               => 'CP1250',
			'CSIBM1149'               => 'CP1250',
			'CSIBM1153'               => 'CP1250',
			'CSIBM1154'               => 'CP1250',
			'CSIBM1155'               => 'CP1250',
			'CSIBM1156'               => 'CP1250',
			'CSIBM1157'               => 'CP1250',
			'CSIBM1158'               => 'CP1250',
			'CSIBM1160'               => 'CP1250',
			'CSIBM1161'               => 'CP1250',
			'CSIBM1163'               => 'CP1250',
			'CSIBM1164'               => 'CP1250',
			'CSIBM1166'               => 'CP1250',
			'CSIBM1167'               => 'CP1250',
			'CSIBM1364'               => 'CP1257',
			'CSIBM1371'               => 'CP1257',
			'CSIBM1388'               => 'CP1257',
			'CSIBM1390'               => 'CP1257',
			'CSIBM1399'               => 'CP1257',
			'CSIBM4517'               => 'CP1257',
			'CSIBM4899'               => 'CP1257',
			'CSIBM4909'               => 'CP1257',
			'CSIBM4971'               => 'CP1257',
			'CSIBM5347'               => 'CP1257',
			'CSIBM9030'               => 'CP1257',
			'CSIBM9066'               => 'CP1257',
			'CSIBM9448'               => 'CP1257',
			'CSIBM12712'              => 'CP1257',
			'CSIBM16804'              => 'CP1257',
			'DE'                      => 'DEC8',
			'DECMCS'                  => 'DEC8',
			'DEC'                     => 'DEC8',
			'DEC8'                    => 'DEC8',
			'EUCJISX0213'             => 'EUCJPMS',
			'EUCJPMS'                 => 'EUCJPMS',
			'EUCJP'                   => 'EUCJPMS',
			'EUCJPOPEN'               => 'EUCJPMS',
			'EUCJPWIN'                => 'EUCJPMS',
			'EUCKR'                   => 'EUCKR',
			'EUCTW'                   => 'EUCKR',
			'GB'                      => 'GB2312',
			'GB2312'                  => 'GB2312',
			'GB13000'                 => 'GB2312',
			'GB18030'                 => 'GB2312',
			'GB198880'                => 'GB2312',
			'GBK'                     => 'GBK',
			'GEOSTD8'                 => 'GEOSTD8',
			'GEORGIANACADEMY'         => 'GEOSTD8',
			'GEORGIANPS'              => 'GEOSTD8',
			'GREEK'                   => 'GREEK',
			'GREEK7'                  => 'GREEK',
			'GREEK8'                  => 'GREEK',
			'GREEK7OLD'               => 'GREEK',
			'GREEKCCITT'              => 'GREEK',
			'HEBREW'                  => 'HEBREW',
			'HP8'                     => 'HP8',
			'IBM803'                  => 'CP850',
			'IBM856'                  => 'CP866',
			'IBM901'                  => 'CP932',
			'IBM902'                  => 'CP932',
			'IBM921'                  => 'CP932',
			'IBM922'                  => 'CP932',
			'IBM930'                  => 'CP932',
			'IBM932'                  => 'CP932',
			'IBM933'                  => 'CP1250',
			'IBM935'                  => 'CP1250',
			'IBM937'                  => 'CP1250',
			'IBM939'                  => 'CP1250',
			'IBM943'                  => 'CP1250',
			'IBM1008'                 => 'CP1250',
			'IBM1025'                 => 'CP1250',
			'IBM1046'                 => 'CP1250',
			'IBM1047'                 => 'CP1250',
			'IBM1097'                 => 'CP1250',
			'IBM1112'                 => 'CP1250',
			'IBM1122'                 => 'CP1250',
			'IBM1123'                 => 'CP1250',
			'IBM1124'                 => 'CP1250',
			'IBM1129'                 => 'CP1250',
			'IBM1130'                 => 'CP1250',
			'IBM1132'                 => 'CP1250',
			'IBM1133'                 => 'CP1250',
			'IBM1137'                 => 'CP1250',
			'IBM1140'                 => 'CP1250',
			'IBM1141'                 => 'CP1250',
			'IBM1142'                 => 'CP1250',
			'IBM1143'                 => 'CP1250',
			'IBM1144'                 => 'CP1250',
			'IBM1145'                 => 'CP1250',
			'IBM1146'                 => 'CP1250',
			'IBM1147'                 => 'CP1250',
			'IBM1148'                 => 'CP1250',
			'IBM1149'                 => 'CP1250',
			'IBM1153'                 => 'CP1250',
			'IBM1154'                 => 'CP1250',
			'IBM1155'                 => 'CP1250',
			'IBM1156'                 => 'CP1250',
			'IBM1157'                 => 'CP1250',
			'IBM1158'                 => 'CP1250',
			'IBM1160'                 => 'CP1250',
			'IBM1161'                 => 'CP1250',
			'IBM1162'                 => 'CP1250',
			'IBM1163'                 => 'CP1250',
			'IBM1164'                 => 'CP1250',
			'IBM1166'                 => 'CP1250',
			'IBM1167'                 => 'CP1250',
			'IBM1364'                 => 'CP1257',
			'IBM1371'                 => 'CP1257',
			'IBM1388'                 => 'CP1257',
			'IBM1390'                 => 'CP1257',
			'IBM1399'                 => 'CP1257',
			'IBM4517'                 => 'CP1257',
			'IBM4899'                 => 'CP1257',
			'IBM4909'                 => 'CP1257',
			'IBM4971'                 => 'CP1257',
			'IBM5347'                 => 'CP1257',
			'IBM9030'                 => 'CP1257',
			'IBM9066'                 => 'CP1257',
			'IBM9448'                 => 'CP1257',
			'IBM12712'                => 'CP1257',
			'IBM16804'                => 'CP850',
			'IBM037'                  => 'CP850',
			'IBM038'                  => 'CP850',
			'IBM256'                  => 'CP850',
			'IBM273'                  => 'CP850',
			'IBM274'                  => 'CP850',
			'IBM275'                  => 'CP850',
			'IBM277'                  => 'CP850',
			'IBM278'                  => 'CP850',
			'IBM280'                  => 'CP850',
			'IBM281'                  => 'CP850',
			'IBM284'                  => 'CP850',
			'IBM285'                  => 'CP850',
			'IBM290'                  => 'CP850',
			'IBM297'                  => 'CP850',
			'IBM367'                  => 'CP850',
			'IBM420'                  => 'CP850',
			'IBM423'                  => 'CP850',
			'IBM424'                  => 'CP850',
			'IBM437'                  => 'CP850',
			'IBM500'                  => 'CP850',
			'IBM775'                  => 'CP850',
			'IBM813'                  => 'CP850',
			'IBM819'                  => 'CP850',
			'IBM848'                  => 'CP850',
			'IBM850'                  => 'CP850',
			'IBM851'                  => 'CP852',
			'IBM852'                  => 'CP852',
			'IBM855'                  => 'CP866',
			'IBM857'                  => 'CP932',
			'IBM860'                  => 'CP932',
			'IBM861'                  => 'CP932',
			'IBM862'                  => 'CP932',
			'IBM863'                  => 'CP932',
			'IBM864'                  => 'CP932',
			'IBM865'                  => 'CP932',
			'IBM866'                  => 'CP932',
			'IBM866NAV'               => 'CP932',
			'IBM868'                  => 'CP932',
			'IBM869'                  => 'CP932',
			'IBM870'                  => 'CP932',
			'IBM871'                  => 'CP932',
			'IBM874'                  => 'CP932',
			'IBM875'                  => 'CP932',
			'IBM880'                  => 'CP932',
			'IBM891'                  => 'CP932',
			'IBM903'                  => 'CP932',
			'IBM904'                  => 'CP932',
			'IBM905'                  => 'CP932',
			'IBM912'                  => 'CP932',
			'IBM915'                  => 'CP932',
			'IBM916'                  => 'CP932',
			'IBM918'                  => 'CP932',
			'IBM920'                  => 'CP932',
			'IBM1004'                 => 'CP1250',
			'IBM1026'                 => 'CP1250',
			'IBM1089'                 => 'CP1250',
			'ISO2022CNEXT'            => 'LATIN1',
			'ISO2022CN'               => 'LATIN1',
			'ISO2022JP2'              => 'LATIN1',
			'ISO2022JP3'              => 'LATIN1',
			'ISO2022JP'               => 'LATIN1',
			'ISO2022KR'               => 'LATIN1',
			'ISO88591'                => 'LATIN1',
			'ISO88592'                => 'LATIN2',
			'ISO88593'                => 'LATIN5',
			'ISO88594'                => 'LATIN5',
			'ISO88595'                => 'LATIN5',
			'ISO88596'                => 'LATIN7',
			'ISO88597'                => 'LATIN7',
			'ISO88598'                => 'LATIN7',
			'ISO88599'                => 'LATIN5',
			'ISO88599E'               => 'LATIN5',
			'ISO885910'               => 'LATIN7',
			'ISO885911'               => 'LATIN7',
			'ISO885913'               => 'LATIN7',
			'ISO885914'               => 'LATIN7',
			'ISO885915'               => 'LATIN7',
			'ISO885916'               => 'LATIN7',
			'ISO10646UCS2'            => 'UCS2',
			'ISO10646'                => 'UCS2',
			'ISO10646UCS2'            => 'UCS2',
			'ISO10646UCS4'            => 'UCS2',
			'ISO10646UTF8'            => 'UTF8',
			'KEYBCS2'                 => 'KEYBCS2',
			'KOI7'                    => 'KOI8R',
			'KOI8'                    => 'KOI8R',
			'KOI8R'                   => 'KOI8R',
			'KOI8RU'                  => 'KOI8R',
			'KOI8T'                   => 'KOI8R',
			'KOI8U'                   => 'KOI8U',
			'L1'                      => 'LATIN1',
			'L2'                      => 'LATIN2',
			'L3'                      => 'LATIN2',
			'L4'                      => 'LATIN2',
			'L5'                      => 'LATIN5',
			'L6'                      => 'LATIN5',
			'L7'                      => 'LATIN7',
			'L8'                      => 'LATIN7',
			'L10'                     => 'LATIN7',
			'LATINGREEK1'             => 'GREEK',
			'LATINGREEK'              => 'GREEK',
			'LATIN1'                  => 'LATIN1',
			'LATIN2'                  => 'LATIN2',
			'LATIN3'                  => 'LATIN5',
			'LATIN4'                  => 'LATIN5',
			'LATIN5'                  => 'LATIN5',
			'LATIN6'                  => 'LATIN7',
			'LATIN7'                  => 'LATIN7',
			'LATIN8'                  => 'LATIN7',
			'LATIN9'                  => 'LATIN7',
			'LATIN10'                 => 'LATIN7',
			'MACCE'                   => 'MACCE',
			'MACCENTRALEUROPE'        => 'MACCE',
			'MACCYRILLIC'             => 'MACCE',
			'MACROMAN'                => 'MACROMAN',
			'SJIS'                    => 'SJIS',
			'SJISOPEN'                => 'SJIS',
			'SJISWIN'                 => 'SJIS',
			'SWE7'                    => 'SWE7',
			'TIS620'                  => 'TIS620',
			'TIS6200'                 => 'TIS620',
			'TIS62025291'             => 'TIS620',
			'TIS62025330'             => 'TIS620',
			'UCS2'                    => 'UCS2',
			'UCS2BE'                  => 'UCS2',
			'UCS2LE'                  => 'UCS2',
			'UCS4'                    => 'UCS2',
			'UCS4BE'                  => 'UCS2',
			'UCS4LE'                  => 'UCS2',
			'UHC'                     => 'UCS2',
			'UJIS'                    => 'UJIS',
			'USASCII'                 => 'ASCII',
			'US'                      => 'ASCII',
			'UTF7'                    => 'UTF8',
			'UTF8'                    => 'UTF8',
			'UTF8MB4'                 => 'UTF8MB4',
			'UTF16'                   => 'UTF16',
			'UTF16BE'                 => 'UTF16',
			'UTF16LE'                 => 'UTF16LE',
			'UTF32'                   => 'UTF32',
			'UTF32BE'                 => 'UTF32',
			'UTF32LE'                 => 'UTF32',

			'BRF'                     => 'ASCII',
			'BS4730'                  => 'ASCII',
			'CA'                      => 'ASCII',
			'CNBIG5'                  => 'ASCII',
			'CNGB'                    => 'ASCII',
			'CN'                      => 'ASCII',
			'CSA71'                   => 'ASCII',
			'CSA72'                   => 'ASCII',
			'CSASCII'                 => 'ASCII',
			'CSAT5001983'             => 'ASCII',
			'CSAT500'                 => 'ASCII',
			'CSAZ243419851'           => 'ASCII',
			'CSAZ243419852'           => 'ASCII',
			'CSDECMCS'                => 'ASCII',
			'CSEBCDICATDE'            => 'ASCII',
			'CSEBCDICATDEA'           => 'ASCII',
			'CSEBCDICCAFR'            => 'ASCII',
			'CSEBCDICDKNO'            => 'ASCII',
			'CSEBCDICDKNOA'           => 'ASCII',
			'CSEBCDICES'              => 'ASCII',
			'CSEBCDICESA'             => 'ASCII',
			'CSEBCDICESS'             => 'ASCII',
			'CSEBCDICFISE'            => 'ASCII',
			'CSEBCDICFISEA'           => 'ASCII',
			'CSEBCDICFR'              => 'ASCII',
			'CSEBCDICIT'              => 'ASCII',
			'CSEBCDICPT'              => 'ASCII',
			'CSEBCDICUK'              => 'ASCII',
			'CSEBCDICUS'              => 'ASCII',
			'CSEUCKR'                 => 'ASCII',
			'CSEUCPKDFMTJAPANESE'     => 'ASCII',
			'CSGB2312'                => 'ASCII',
			'CSHPROMAN8'              => 'ASCII',
			'CSIBM11621162'           => 'ASCII',
			'CSISO4UNITEDKINGDOM'     => 'ASCII',
			'CSISO10SWEDISH'          => 'ASCII',
			'CSISO11SWEDISHFORNAMES'  => 'ASCII',
			'CSISO14JISC6220RO'       => 'ASCII',
			'CSISO15ITALIAN'          => 'ASCII',
			'CSISO16PORTUGESE'        => 'ASCII',
			'CSISO17SPANISH'          => 'ASCII',
			'CSISO18GREEK7OLD'        => 'ASCII',
			'CSISO19LATINGREEK'       => 'ASCII',
			'CSISO21GERMAN'           => 'ASCII',
			'CSISO25FRENCH'           => 'ASCII',
			'CSISO27LATINGREEK1'      => 'ASCII',
			'CSISO49INIS'             => 'ASCII',
			'CSISO50INIS8'            => 'ASCII',
			'CSISO51INISCYRILLIC'     => 'ASCII',
			'CSISO58GB1988'           => 'ASCII',
			'CSISO60DANISHNORWEGIAN'  => 'ASCII',
			'CSISO60NORWEGIAN1'       => 'ASCII',
			'CSISO61NORWEGIAN2'       => 'ASCII',
			'CSISO69FRENCH'           => 'ASCII',
			'CSISO84PORTUGUESE2'      => 'ASCII',
			'CSISO85SPANISH2'         => 'ASCII',
			'CSISO86HUNGARIAN'        => 'ASCII',
			'CSISO88GREEK7'           => 'ASCII',
			'CSISO89ASMO449'          => 'ASCII',
			'CSISO90'                 => 'ASCII',
			'CSISO92JISC62991984B'    => 'ASCII',
			'CSISO99NAPLPS'           => 'ASCII',
			'CSISO103T618BIT'         => 'ASCII',
			'CSISO111ECMACYRILLIC'    => 'ASCII',
			'CSISO121CANADIAN1'       => 'ASCII',
			'CSISO122CANADIAN2'       => 'ASCII',
			'CSISO139CSN369103'       => 'ASCII',
			'CSISO141JUSIB1002'       => 'ASCII',
			'CSISO143IECP271'         => 'ASCII',
			'CSISO150'                => 'ASCII',
			'CSISO150GREEKCCITT'      => 'ASCII',
			'CSISO151CUBA'            => 'ASCII',
			'CSISO153GOST1976874'     => 'ASCII',
			'CSISO646DANISH'          => 'ASCII',
			'CSISO2022CN'             => 'ASCII',
			'CSISO2022JP'             => 'ASCII',
			'CSISO2022JP2'            => 'ASCII',
			'CSISO2022KR'             => 'ASCII',
			'CSISO2033'               => 'ASCII',
			'CSISO5427CYRILLIC'       => 'ASCII',
			'CSISO5427CYRILLIC1981'   => 'ASCII',
			'CSISO5428GREEK'          => 'ASCII',
			'CSISO10367BOX'           => 'ASCII',
			'CSISOLATIN1'             => 'ASCII',
			'CSISOLATIN2'             => 'ASCII',
			'CSISOLATIN3'             => 'ASCII',
			'CSISOLATIN4'             => 'ASCII',
			'CSISOLATIN5'             => 'ASCII',
			'CSISOLATIN6'             => 'ASCII',
			'CSISOLATINARABIC'        => 'ASCII',
			'CSISOLATINCYRILLIC'      => 'ASCII',
			'CSISOLATINGREEK'         => 'ASCII',
			'CSISOLATINHEBREW'        => 'ASCII',
			'CSKOI8R'                 => 'ASCII',
			'CSKSC5636'               => 'ASCII',
			'CSMACINTOSH'             => 'ASCII',
			'CSNATSDANO'              => 'ASCII',
			'CSNATSSEFI'              => 'ASCII',
			'CSN369103'               => 'ASCII',
			'CSPC8CODEPAGE437'        => 'ASCII',
			'CSPC775BALTIC'           => 'ASCII',
			'CSPC850MULTILINGUAL'     => 'ASCII',
			'CSPC862LATINHEBREW'      => 'ASCII',
			'CSPCP852'                => 'ASCII',
			'CSSHIFTJIS'              => 'ASCII',
			'CSUCS4'                  => 'ASCII',
			'CSUNICODE'               => 'ASCII',
			'CSWINDOWS31J'            => 'ASCII',
			'CUBA'                    => 'ASCII',
			'CWI2'                    => 'ASCII',
			'CWI'                     => 'ASCII',
			'CYRILLIC'                => 'ASCII',
			'DIN66003'                => 'ASCII',
			'DK'                      => 'ASCII',
			'DS2089'                  => 'ASCII',
			'E13B'                    => 'ASCII',
			'EBCDICATDEA'             => 'ASCII',
			'EBCDICATDE'              => 'ASCII',
			'EBCDICBE'                => 'ASCII',
			'EBCDICBR'                => 'ASCII',
			'EBCDICCAFR'              => 'ASCII',
			'EBCDICCPAR1'             => 'ASCII',
			'EBCDICCPAR2'             => 'ASCII',
			'EBCDICCPBE'              => 'ASCII',
			'EBCDICCPCA'              => 'ASCII',
			'EBCDICCPCH'              => 'ASCII',
			'EBCDICCPDK'              => 'ASCII',
			'EBCDICCPES'              => 'ASCII',
			'EBCDICCPFI'              => 'ASCII',
			'EBCDICCPFR'              => 'ASCII',
			'EBCDICCPGB'              => 'ASCII',
			'EBCDICCPGR'              => 'ASCII',
			'EBCDICCPHE'              => 'ASCII',
			'EBCDICCPIS'              => 'ASCII',
			'EBCDICCPIT'              => 'ASCII',
			'EBCDICCPNL'              => 'ASCII',
			'EBCDICCPNO'              => 'ASCII',
			'EBCDICCPROECE'           => 'ASCII',
			'EBCDICCPSE'              => 'ASCII',
			'EBCDICCPTR'              => 'ASCII',
			'EBCDICCPUS'              => 'ASCII',
			'EBCDICCPWT'              => 'ASCII',
			'EBCDICCPYU'              => 'ASCII',
			'EBCDICCYRILLIC'          => 'ASCII',
			'EBCDICDKNOA'             => 'ASCII',
			'EBCDICDKNO'              => 'ASCII',
			'EBCDICESA'               => 'ASCII',
			'EBCDICESS'               => 'ASCII',
			'EBCDICES'                => 'ASCII',
			'EBCDICFISEA'             => 'ASCII',
			'EBCDICFISE'              => 'ASCII',
			'EBCDICFR'                => 'ASCII',
			'EBCDICGREEK'             => 'ASCII',
			'EBCDICINT'               => 'ASCII',
			'EBCDICINT1'              => 'ASCII',
			'EBCDICISFRISS'           => 'ASCII',
			'EBCDICIT'                => 'ASCII',
			'EBCDICJPE'               => 'ASCII',
			'EBCDICJPKANA'            => 'ASCII',
			'EBCDICPT'                => 'ASCII',
			'EBCDICUK'                => 'ASCII',
			'EBCDICUS'                => 'ASCII',
			'ECMA114'                 => 'ASCII',
			'ECMA118'                 => 'ASCII',
			'ECMA128'                 => 'ASCII',
			'ECMACYRILLIC'            => 'ASCII',
			'ELOT928'                 => 'ASCII',
			'ES'                      => 'ASCII',
			'ES2'                     => 'ASCII',
			'EUCCN'                   => 'ASCII',
			'FI'                      => 'ASCII',
			'FR'                      => 'ASCII',
			'GOST1976874'             => 'ASCII',
			'GOST19768'               => 'ASCII',
			'HPGREEK8'                => 'ASCII',
			'HPROMAN8'                => 'ASCII',
			'HPROMAN9'                => 'ASCII',
			'HPTHAI8'                 => 'ASCII',
			'HPTURKISH8'              => 'ASCII',
			'HU'                      => 'ASCII',
			'IECP271'                 => 'ASCII',
			'INIS8'                   => 'ASCII',
			'INISCYRILLIC'            => 'ASCII',
			'INIS'                    => 'ASCII',
			'ISIRI3342'               => 'ASCII',
			'ISOCELTIC'               => 'ASCII',
			'ISOIR4'                  => 'ASCII',
			'ISOIR6'                  => 'ASCII',
			'ISOIR81'                 => 'ASCII',
			'ISOIR91'                 => 'ASCII',
			'ISOIR10'                 => 'ASCII',
			'ISOIR11'                 => 'ASCII',
			'ISOIR14'                 => 'ASCII',
			'ISOIR15'                 => 'ASCII',
			'ISOIR16'                 => 'ASCII',
			'ISOIR17'                 => 'ASCII',
			'ISOIR18'                 => 'ASCII',
			'ISOIR19'                 => 'ASCII',
			'ISOIR21'                 => 'ASCII',
			'ISOIR25'                 => 'ASCII',
			'ISOIR27'                 => 'ASCII',
			'ISOIR37'                 => 'ASCII',
			'ISOIR49'                 => 'ASCII',
			'ISOIR50'                 => 'ASCII',
			'ISOIR51'                 => 'ASCII',
			'ISOIR54'                 => 'ASCII',
			'ISOIR55'                 => 'ASCII',
			'ISOIR57'                 => 'ASCII',
			'ISOIR60'                 => 'ASCII',
			'ISOIR61'                 => 'ASCII',
			'ISOIR69'                 => 'ASCII',
			'ISOIR84'                 => 'ASCII',
			'ISOIR85'                 => 'ASCII',
			'ISOIR86'                 => 'ASCII',
			'ISOIR88'                 => 'ASCII',
			'ISOIR89'                 => 'ASCII',
			'ISOIR90'                 => 'ASCII',
			'ISOIR92'                 => 'ASCII',
			'ISOIR98'                 => 'ASCII',
			'ISOIR99'                 => 'ASCII',
			'ISOIR100'                => 'ASCII',
			'ISOIR101'                => 'ASCII',
			'ISOIR103'                => 'ASCII',
			'ISOIR109'                => 'ASCII',
			'ISOIR110'                => 'ASCII',
			'ISOIR111'                => 'ASCII',
			'ISOIR121'                => 'ASCII',
			'ISOIR122'                => 'ASCII',
			'ISOIR126'                => 'ASCII',
			'ISOIR127'                => 'ASCII',
			'ISOIR138'                => 'ASCII',
			'ISOIR139'                => 'ASCII',
			'ISOIR141'                => 'ASCII',
			'ISOIR143'                => 'ASCII',
			'ISOIR144'                => 'ASCII',
			'ISOIR148'                => 'ASCII',
			'ISOIR150'                => 'ASCII',
			'ISOIR151'                => 'ASCII',
			'ISOIR153'                => 'ASCII',
			'ISOIR155'                => 'ASCII',
			'ISOIR156'                => 'ASCII',
			'ISOIR157'                => 'ASCII',
			'ISOIR166'                => 'ASCII',
			'ISOIR179'                => 'ASCII',
			'ISOIR193'                => 'ASCII',
			'ISOIR197'                => 'ASCII',
			'ISOIR199'                => 'ASCII',
			'ISOIR203'                => 'ASCII',
			'ISOIR209'                => 'ASCII',
			'ISOIR226'                => 'ASCII',
			'ISOTR115481'             => 'ASCII',
			'ISO646CA'                => 'ASCII',
			'ISO646CA2'               => 'ASCII',
			'ISO646CN'                => 'ASCII',
			'ISO646CU'                => 'ASCII',
			'ISO646DE'                => 'ASCII',
			'ISO646DK'                => 'ASCII',
			'ISO646ES'                => 'ASCII',
			'ISO646ES2'               => 'ASCII',
			'ISO646FI'                => 'ASCII',
			'ISO646FR'                => 'ASCII',
			'ISO646FR1'               => 'ASCII',
			'ISO646GB'                => 'ASCII',
			'ISO646HU'                => 'ASCII',
			'ISO646IT'                => 'ASCII',
			'ISO646JPOCRB'            => 'ASCII',
			'ISO646JP'                => 'ASCII',
			'ISO646KR'                => 'ASCII',
			'ISO646NO'                => 'ASCII',
			'ISO646NO2'               => 'ASCII',
			'ISO646PT'                => 'ASCII',
			'ISO646PT2'               => 'ASCII',
			'ISO646SE'                => 'ASCII',
			'ISO646SE2'               => 'ASCII',
			'ISO646US'                => 'ASCII',
			'ISO646YU'                => 'ASCII',
			'ISO6937'                 => 'ASCII',
			'ISO115481'               => 'ASCII',
			'ISO646IRV1991'           => 'ASCII',
			'ISO20331983'             => 'ASCII',
			'ISO2033'                 => 'ASCII',
			'ISO5427EXT'              => 'ASCII',
			'ISO5427'                 => 'ASCII',
			'ISO54271981'             => 'ASCII',
			'ISO5428'                 => 'ASCII',
			'ISO54281980'             => 'ASCII',
			'ISO69372'                => 'ASCII',
			'ISO693721983'            => 'ASCII',
			'ISO69371992'             => 'ASCII',
			'ISO885911987'            => 'ASCII',
			'ISO885921987'            => 'ASCII',
			'ISO885931988'            => 'ASCII',
			'ISO885941988'            => 'ASCII',
			'ISO885951988'            => 'ASCII',
			'ISO885961987'            => 'ASCII',
			'ISO885971987'            => 'ASCII',
			'ISO885972003'            => 'ASCII',
			'ISO885981988'            => 'ASCII',
			'ISO885991989'            => 'ASCII',
			'ISO8859101992'           => 'ASCII',
			'ISO8859141998'           => 'ASCII',
			'ISO8859151998'           => 'ASCII',
			'ISO8859162001'           => 'ASCII',
			'ISO9036'                 => 'ASCII',
			'ISO10367BOX'             => 'ASCII',
			'IT'                      => 'ASCII',
			'JISC62201969RO'          => 'ASCII',
			'JISC62291984B'           => 'ASCII',
			'JOHAB'                   => 'ASCII',
			'JPOCRB'                  => 'ASCII',
			'JP'                      => 'ASCII',
			'JS'                      => 'ASCII',
			'JUSIB1002'               => 'ASCII',
			'KSC5636'                 => 'ASCII',
			'MACIS'                   => 'ASCII',
			'MACSAMI'                 => 'ASCII',
			'MACUK'                   => 'ASCII',
			'MAC'                     => 'ASCII',
			'MACINTOSH'               => 'ASCII',
			'MACUKRAINIAN'            => 'ASCII',
			'MIK'                     => 'ASCII',
			'MSANSI'                  => 'ASCII',
			'MSARAB'                  => 'ASCII',
			'MSCYRL'                  => 'ASCII',
			'MSEE'                    => 'ASCII',
			'MSGREEK'                 => 'ASCII',
			'MSHEBR'                  => 'ASCII',
			'MSMACCYRILLIC'           => 'ASCII',
			'MSTURK'                  => 'ASCII',
			'MS932'                   => 'ASCII',
			'MS936'                   => 'ASCII',
			'MSCP949'                 => 'ASCII',
			'MSCP1361'                => 'ASCII',
			'MSZ77953'                => 'ASCII',
			'MSKANJI'                 => 'ASCII',
			'NAPLPS'                  => 'ASCII',
			'NATSDANO'                => 'ASCII',
			'NATSSEFI'                => 'ASCII',
			'NCNC0010'                => 'ASCII',
			'NCNC001081'              => 'ASCII',
			'NFZ62010'                => 'ASCII',
			'NFZ620101973'            => 'ASCII',
			'NO'                      => 'ASCII',
			'NO2'                     => 'ASCII',
			'NS45511'                 => 'ASCII',
			'NS45512'                 => 'ASCII',
			'OS2LATIN1'               => 'ASCII',
			'OSF00010001'             => 'ASCII',
			'OSF00010002'             => 'ASCII',
			'OSF00010003'             => 'ASCII',
			'OSF00010004'             => 'ASCII',
			'OSF00010005'             => 'ASCII',
			'OSF00010006'             => 'ASCII',
			'OSF00010007'             => 'ASCII',
			'OSF00010008'             => 'ASCII',
			'OSF00010009'             => 'ASCII',
			'OSF0001000A'             => 'ASCII',
			'OSF00010020'             => 'ASCII',
			'OSF00010100'             => 'ASCII',
			'OSF00010101'             => 'ASCII',
			'OSF00010102'             => 'ASCII',
			'OSF00010104'             => 'ASCII',
			'OSF00010105'             => 'ASCII',
			'OSF00010106'             => 'ASCII',
			'OSF00030010'             => 'ASCII',
			'OSF0004000A'             => 'ASCII',
			'OSF0005000A'             => 'ASCII',
			'OSF05010001'             => 'ASCII',
			'OSF100201A4'             => 'ASCII',
			'OSF100201A8'             => 'ASCII',
			'OSF100201B5'             => 'ASCII',
			'OSF100201F4'             => 'ASCII',
			'OSF100203B5'             => 'ASCII',
			'OSF1002011C'             => 'ASCII',
			'OSF1002011D'             => 'ASCII',
			'OSF1002035D'             => 'ASCII',
			'OSF1002035E'             => 'ASCII',
			'OSF1002035F'             => 'ASCII',
			'OSF1002036B'             => 'ASCII',
			'OSF1002037B'             => 'ASCII',
			'OSF10010001'             => 'ASCII',
			'OSF10010004'             => 'ASCII',
			'OSF10010006'             => 'ASCII',
			'OSF10020025'             => 'ASCII',
			'OSF10020111'             => 'ASCII',
			'OSF10020115'             => 'ASCII',
			'OSF10020116'             => 'ASCII',
			'OSF10020118'             => 'ASCII',
			'OSF10020122'             => 'ASCII',
			'OSF10020129'             => 'ASCII',
			'OSF10020352'             => 'ASCII',
			'OSF10020354'             => 'ASCII',
			'OSF10020357'             => 'ASCII',
			'OSF10020359'             => 'ASCII',
			'OSF10020360'             => 'ASCII',
			'OSF10020364'             => 'ASCII',
			'OSF10020365'             => 'ASCII',
			'OSF10020366'             => 'ASCII',
			'OSF10020367'             => 'ASCII',
			'OSF10020370'             => 'ASCII',
			'OSF10020387'             => 'ASCII',
			'OSF10020388'             => 'ASCII',
			'OSF10020396'             => 'ASCII',
			'OSF10020402'             => 'ASCII',
			'OSF10020417'             => 'ASCII',
			'PT'                      => 'ASCII',
			'PT2'                     => 'ASCII',
			'PT154'                   => 'ASCII',
			'R8'                      => 'ASCII',
			'R9'                      => 'ASCII',
			'RK1048'                  => 'ASCII',
			'ROMAN8'                  => 'ASCII',
			'ROMAN9'                  => 'ASCII',
			'RUSCII'                  => 'ASCII',
			'SE'                      => 'ASCII',
			'SE2'                     => 'ASCII',
			'SEN850200B'              => 'ASCII',
			'SEN850200C'              => 'ASCII',
			'SHIFTJIS'                => 'ASCII',
			'SHIFTJISX0213'           => 'ASCII',
			'SS636127'                => 'ASCII',
			'STRK10482002'            => 'ASCII',
			'STSEV35888'              => 'ASCII',
			'T618BIT'                 => 'ASCII',
			'T61'                     => 'ASCII',
			'TCVN5712'                => 'ASCII',
			'TCVN'                    => 'ASCII',
			'TCVN57121'               => 'ASCII',
			'TCVN571211993'           => 'ASCII',
			'THAI8'                   => 'ASCII',
			'TS5881'                  => 'ASCII',
			'TSCII'                   => 'ASCII',
			'TURKISH8'                => 'ASCII',
			'UK'                      => 'ASCII',
			'UNICODE'                 => 'ASCII',
			'UNICODEBIG'              => 'ASCII',
			'UNICODELITTLE'           => 'ASCII',
			'VISCII'                  => 'ASCII',
			'WCHART'                  => 'ASCII',
			'WINSAMI2'                => 'ASCII',
			'WINBALTRIM'              => 'ASCII',
			'WINDOWS31J'              => 'ASCII',
			'WINDOWS874'              => 'ASCII',
			'WINDOWS936'              => 'ASCII',
			'WINDOWS1250'             => 'ASCII',
			'WINDOWS1251'             => 'ASCII',
			'WINDOWS1252'             => 'ASCII',
			'WINDOWS1253'             => 'ASCII',
			'WINDOWS1254'             => 'ASCII',
			'WINDOWS1255'             => 'ASCII',
			'WINDOWS1256'             => 'ASCII',
			'WINDOWS1257'             => 'ASCII',
			'WINDOWS1258'             => 'ASCII',
			'WS2'                     => 'ASCII',
			'YU'                      => 'ASCII',
		];

		$val = preg_replace('/[^0-9a-z]/i', '', $val);
		return $this->enum('charset', $val, $arr);
	}
	public function set_encoding($val) {
		return $this->set_charset($val);
	}
	public function set_textDelimiter($val) {
		$this->protect['enclose'] = $val;
		return $this;
	}
	public function set_head($val) {
		$this->protect['head'] = (bool)$val;
		return $this;
	}
	public function set_delimiter($val) {
		if (strlen($val) > 1) {
			if ($val[0] == '{') $val = @json_decode($val);
			else {
				$j = preg_split('/\s*[,;]\s*/', trim($val));
				$val = [];
				foreach ($j as $k => $v) if (preg_match('/^(?:([^:]*)\s*:\s*)?(\d+)$/', $v, $r)) {
					$key = $r[1] == '' ? $k : $r[1];
					$val[$key] = $r[2] + 0;
				}
			}
		}
		$this->protect['delimiter'] = $val;
		return $this;
	}
	public function set_fieldSeparator($val) {
		static $arr = [
			'TAB' => "\t",
			'Space' => ' ',
			'Comma' => ',',
			'SemiColon' => ';',
			'Custom' => null,
			'Fixed' => null,
		];
		if (array_key_exists($val, $arr)) $val = $arr[$val];
		return $this->set_delimiter($val);
	}
	public function set_fieldSeparatorCustom($val) {
		if ($val == '') return $this;
		return $this->set_delimiter($val);
	}
	public function set_nullCode($val) {
		$this->protect['nullCode'] = $val;
		return $this;
	}
	public function set_skipLines($val) {
		$this->protect['skipLines'] = $val;
		return $this;
	}
	public function set_conn($val = null) {
		if ($val = '' || is_string($val)) $val = Conn::dsn($val);
		if (is_object($val)) $this->protect['conn'] = $val;
		return $this;
	}
	public function get_conn() {
		if (!is_object($this->protect['conn'])) $this->set_conn();
		return $this->protect['conn'];
	}
	public function set_fields($val) {
		if (is_string($val)) $val = json_decode($val);
		if (is_array($val)) $this->protect['fields'] = $val;
		return $this;
	}
	public function set_f($val) {
		return $this->set_fields($val);
	}

	protected function pr($text) {
		if ($text != '') print "-- $text\n";
		return $this;
	}
	protected function erro($arr, $pre = 'ERRO: ') {
		if (is_array($arr)) $arr=print_r($arr,true);
		print "$pre$arr\n";
		return $this;
	}
	protected function fatalError($arr) {
		$this->erro($arr, 'FATAL ERRO: ');
		exit;
	}
	protected function query($sql, $info = null) {
		if ($sql) {
			if (is_array($sql)) {
				$tam = count($sql);
				foreach ($sql as $k => $v) $this->query($v, $info . ($info ? ' ' : '') . "($k/$tam)");
			} else {
				$this->pr($info);
				print "$sql\n";
				//$conn->query($sql,false);
			}
		}
		return $this;
	}
	protected function enum($name, $val, $arr) {
		if (!is_null($val)) {
			if (is_bool($val)) {
				if ($val) array_shift($arr);
				$val = reset($arr);
			} elseif (is_numeric($val) || is_string($val)) {
				$val = strtoupper($val);
				if (array_key_exists($val, $arr)) $val = $arr[$val];
				else {
					$k = array_search($val, $arr);
					if ($k === false) {
						$this->erro("Valor/Chave não achado de $name=$val");
						$this->erro($arr,'');
						return $this;
					}
				}
			} else {
				$this->erro("Tipo de valor não reconhecido $name");
				return $this;
			}
		}
		$this->protect[$name] = $val;
		return $this;
	}
	protected function makeTmpFile() {
		$file = $this->file;
		if ($this->deleteFile) return $file;

		$this->file = $newFile = tempnam($this->tmpDir, __CLASS__ . '_');
		`/bin/cp -f '{$file}' '{$newFile}'`;
		$this->deleteFile = true;
		return $newFile;
	}
	protected function build_field($name, $config, $nullCode, &$fieldsUpdate, &$loadDataFields, &$loadDataSet) {
		$fieldName = $name;
		$campo = '@field_' . count($fieldsUpdate);

		$config = (array)$config;
		$fieldsUpdate[] = "`$fieldName`=VALUES(`$fieldName`)";
		$loadDataFields[] = "$campo";
		$loadDataSet[] = "`$fieldName`=$campo";
		return $this;
	}
	protected function build_head_delimiter() {
		$headFile = $this->headFile;
		if ($headFile == '') return [];

		if (($erCutCommentHead = $this->erCutCommentHead)) $headFile = preg_replace($erCutCommentHead, '', $headFile);

		$aEr = $this->er;
		$erCut = $this->erCut;
		$out = [];
		$headFileOld = '';
		while ($headFile && $headFile != $headFileOld) {
			$headFileOld = $headFile;
			foreach ($aEr as $er) if (preg_match($er, $headFile, $ret)) {
				$out[] = $ret['field'];
				$headFile = preg_replace([$er, $erCut], '', $headFile);
				break;
			}
		}

		return $out;
	}

	protected function build_part_head(&$sql = []) {
		if (!($file = $this->file)) $this->fatalError('Não existe nome de arquivo');
		if (!($table = $this->table)) $this->fatalError('Não existe nome de tabela');

		$sql = ['LOAD DATA'];
		if (($v = $this->priority)) $sql[] = self::TAB . $v;
		$sql[] = self::TAB . 'LOCAL INFILE \'<FILE_NAME>\'';
		if (($v = $this->method)) $sql[] = self::TAB . ($v == 'UPDATE' ? 'IGNORE' : $v);
		$sql[] = self::TAB . 'INTO TABLE <TABLE_NAME>';
		//[PARTITION (partition_name [, partition_name] ...)]
		if (($v = $this->charset)) $sql[] = self::TAB . 'CHARACTER SET ' . $v;
	}
	protected function build_part_lines(&$sql) {
		($t = $this->line_terminator) || ($t = "\n");
		$s = $this->line_start;
		$sql[] = self::TAB . 'LINES';
		$sql[] = self::TAB . self::TAB . 'TERMINATED BY \'' . escapeString($t) . '\'';
		if ($s) $sql[] = self::TAB . self::TAB . 'STARTING BY \'' . escapeString($s) . '\'';

		$numHead = (int)$this->head;
		$skipLn = (int)$this->skipLines;
		if ($numHead) {
			$numHead += $skipLn;
			$this->readonly['headFile'] = preg_replace('/\s+$/', '', `sed -n '{$numHead}p' '{$this->file}'`);
		} else {
			$numHead = $skipLn;
			$this->readonly['headFile'] = null;
		}

		if (($sed = $this->sed)) {
			$file = $this->makeTmpFile();
			`sed -ri '{$sed}' '{$file}'`;
		}
		if ($this->lenFile > $this->maxLenFile) {
			if ($numHead) { //delete lines file
				$file = $this->makeTmpFile();
				`sed -ri '1,{$numHead}d' '{$file}'`;
			} else $file = $this->file;

			//split file
			`split -l 500 -a 8 "{$file}" "{$file}."`;
			if ($this->deleteFile) `rm "{$file}" -f`;
			$this->deleteFile = true;
			$dir = dirname($file);
			$this->protect['file'] = preg_replace('/^/', $dir . '/', preg_grep('/^' . preg_quote(basename($file . '.'), '/') . '/', scandir($dir)));
		} elseif ($numHead) $sql[] = self::TAB . 'IGNORE ' . $numHead;
		return $this;
	}
	protected function build_part_fields_delimiter(&$sql) {
		($d = $this->delimiter) || ($d = "\t");
		$e = $this->enclose;
		($n = $this->escape) || ($n = '\\');
		$o = $this->enclose_optional;

		$sql[] = self::TAB . 'FIELDS';
		$d = escapeString($d);
		$sql[] = self::TAB . self::TAB . 'TERMINATED BY \'' . $d . '\'';
		$er = ["/^(?<field>[^$d]*)(?=$d)/", "/^(?<field>[^$d]*)\$/"];

		if ($e) {
			$ee = escapeString($e);
			$sql[] = self::TAB . self::TAB . ($o ? 'OPTIONALLY ' : '') . 'ENCLOSED BY \'' . $ee . '\'';
			if (!$o) $er = [];
			if ($d) array_unshift($er, "/^$e(?<field>[^$e]*)$e(?=$d)/");
			array_unshift($er, "/^$e(?<field>[^$e]*)$e\$/");
		}
		$sql[] = self::TAB . self::TAB . 'ESCAPED BY \'' . escapeString($n) . '\'';
		$this->readonly['er'] = $er;
		$this->readonly['erCut'] = "/^$d/";

		return $this;
	}
	protected function build_part_fields_len(&$sql) { //TODO: desenvolver

		$d = $this->delimiter;
		$e = $this->enclose;
		$n = $this->escape;
		if ($d || $e || $n) {
			$sql[] = self::TAB . 'FIELDS';
			if ($d) $sql[] = self::TAB . self::TAB . 'TERMINATED BY \'' . escapeString($d) . '\'';
			if ($e) $sql[] = self::TAB . self::TAB . ($this->enclose_optional ? 'OPTIONALLY ' : '') . 'ENCLOSED BY \'' . escapeString($e) . '\'';
			if ($n) $sql[] = self::TAB . self::TAB . 'ESCAPED BY \'' . escapeString($n) . '\'';
		}
	}
	protected function build_part_set_delimiter(&$sql) { //TODO: desenvolver
		$fieldsUpdate = $loadDataFields = $loadDataSet = [];
		$nullCode = escapeString($this->nullCode);
		$headFile = $this->build_head_delimiter();
		$fields = $this->fields;


		//print_r($fields);
		if ($headFile) {
			foreach ($headFile as $k => $field) {
				if (array_key_exists($field, $fields)) $config = $fields[$field];
				elseif (array_key_exists($k, $fields)) $config = $fields[$k];
				else $config = [];
				$this->build_field($field, $config, $nullCode, $fieldsUpdate, $loadDataFields, $loadDataSet);
			}
		} else {
			foreach ($fields as $k => $config) {
				$this->build_field('Campo' . $k, $config, $nullCode, $fieldsUpdate, $loadDataFields, $loadDataSet);
			}
		}
		//print_r($headFile);
		//print_r($this->readonly);

		/*
		'nullCode'=>'\\N',//NOTE: Null code 
		'head'=>true,//NOTE: true tem cabeçalho (delimiter=array|string), false|array não tem cabeçalho (delimiter=array associativo)
		*/

		$sql[] = "\t(" . implode(', ', $loadDataFields) . ')';
		$sql[] = "\tSET\n\t\t" . implode(",\n\t\t", $loadDataSet);
		$this->build_part_set_end($fieldsUpdate);
		return $this;
		/*
			[(col_name_or_user_var
				[, col_name_or_user_var] ...)]
			[SET col_name={expr | DEFAULT}
				[, col_name={expr | DEFAULT}] ...]
		*/
	}
	protected function build_part_set_len(&$sql) { //TODO: desenvolver
		$head = $this->head;
		//$this->readonly['headFile'];
		/*
		'nullCode'=>'\\N',//NOTE: Null code 
		'head'=>true,//NOTE: true tem cabeçalho (delimiter=array|string), false|array não tem cabeçalho (delimiter=array associativo)
		*/
		/*
			[(col_name_or_user_var
				[, col_name_or_user_var] ...)]
			[SET col_name={expr | DEFAULT}
				[, col_name={expr | DEFAULT}] ...]
		*/
		return $this;
	}
	protected function build_part_set_end($fieldsUpdate) {
		$fieldsUpdate = implode(",\n\t", $fieldsUpdate);
		if (($v = $this->method) == 'UPDATE') {
			$table = $this->protect['table'];
			$tmpTable = preg_replace('/`?$/', '_tmp_' . uniqid(), $table);
			$this->readonly['sqlIni'] = [
				"DROP TABLE IF EXISTS $tmpTable;",
				"CREATE TEMPORARY TABLE $tmpTable LIKE $table;",
			];
			$this->readonly['sqlFim'] = [
				"INSERT $table \nSELECT * FROM $tmpTable \nON DUPLICATE KEY UPDATE \n\t{$fieldsUpdate};",
				"DROP TABLE IF EXISTS $tmpTable;",
			];
			$this->protect['table'] = $tmpTable;
		}
		return $this;
	}

	protected function build_delimiter() {
		$this->build_part_head($sql);
		$this->build_part_fields_delimiter($sql);
		$this->build_part_lines($sql);
		$this->build_part_set_delimiter($sql);
		return $sql;
	}
	protected function build_len() {
		$this->build_part_head($sql);
		$this->build_part_fields_len($sql);
		$this->build_part_lines($sql);
		$this->build_part_set_len($sql);
		return $sql;
	}
}
