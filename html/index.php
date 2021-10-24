<?php
print '<pre>';
print_r([1111]);exit;

use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\Ext\Bootstrap;
use EstaleiroWeb\ED\IO\Vault;

require '../vendor/autoload.php';


$sql =[
	0=>"create or replace table tb_Types (
			Col1 TINYINT,
			Col2 SMALLINT,
			Col3 MEDIUMINT,
			Col4 INT,
			Col5 BIGINT,
			Col6 DECIMAL(10,2),
			Col7 FLOAT,
			Col8 DOUBLE,
			Col9 BIT,
			Col10 BINARY,
			Col11 BLOB,
			Col12 CHAR,
			Col13 ENUM('Y','N'),
			Col15 JSON,
			Col16 MEDIUMBLOB,
			Col17 MEDIUMTEXT,
			Col18 LONGBLOB,
			Col19 LONGTEXT,
			Col21 TEXT,
			Col22 TINYBLOB,
			Col23 TINYTEXT,
			Col24 VARBINARY(100),
			Col25 VARCHAR(100),
			Col26 SET('A','B','C'),
			Col28 DATE,
			Col29 TIME,
			Col30 DATETIME,
			Col31 TIMESTAMP,
			Col32 YEAR,
			Col33 POINT,
			Col34 LINESTRING,
			Col35 POLYGON,
			Col36 MULTIPOINT,
			Col37 MULTILINESTRING,
			Col38 MULTIPOLYGON,
			Col39 GEOMETRYCOLLECTION,
			Col40 GEOMETRY
		)
	",
	1=>"set @a=1;
		select * from test.tb_test t
		-- select * from information_schema.TABLES t
		-- show databases;
	",
	2=>'select * from tb_Types t',
	3=> "select * from information_schema.TABLES t",
	4=>'((SELECT
			SQL_SMALL_RESULT
			`d`.`idDevice` AS `idDevice`,
			`d`.`idDevice_Type` AS `idDevice_Type`,
			`d`.`idModel` AS `idModel`,
			`i`.`idDevice_Interface` AS `idDevice_Interface`,
			`a`.`idDevice_Auth` AS `idDevice_Auth`,
			`c`.`idDevice_Connection` AS `idDevice_Connection`,
			`c`.`idDevice_ConnectionTunnel` AS `idDevice_ConnectionTunnel`,
			`c`.`AppProtocol` AS `AppProtocol`,
			`p`.`Protocol` AS `Protocol`,
			`d`.`Device` AS `Device`,
			`d`.`Descr` AS `Descr`,
			`c`.`Default_Connection` AS `Default_Connection`,
			`i`.`Ip` AS `Ip`,
			ifnull(`c`.`Port`, `c`.`AppProtocol`) AS `Port`,
			`c`.`TransportProtocol` AS `TransportProtocol`,
			`a`.`User` AS `User`,
			`db_System`.`fn_Sys_decode`(`a`.`Passwd`) AS `Passwd`,
			`a`.`Parameters` AS `Parameters`,
			`c`.`Connection_Type_Other` AS `Connection_Type_Other`,
			`c`.`Version` AS `Version`,
			"Algo\"Estranho\"" as nada
		FROM ((((`tb_Devices` `d`
		LEFT JOIN `tb_Devices_Interfaces` `i` ON (((`d`.`idDevice` = `i`.`idDevice`) AND (`i`.`Default_Interface` <> 0))))
		LEFT JOIN `tb_Devices_Connections` `c` ON ((`d`.`idDevice` = `c`.`idDevice`)))
		LEFT JOIN `tb_cbo_AppProtocols` `p` ON ((`p`.`Port` = `c`.`AppProtocol`)))
		LEFT JOIN `tb_Devices_Auths` `a` ON (((`d`.`idDevice` = `a`.`idDevice`) AND (`c`.`AppProtocol` = `a`.`AppProtocol`) AND (`a`.`Default_auth` <> 0))))
		WHERE (`d`.`Enable` <> 0)
		ORDER BY `c`.`Default_Connection` DESC))
	',
	4=>'tb_Devices',
];

print "<pre>";
new Bootstrap('4.0.0');

if (0) {
	$conn = new PDO('mysql:host=localhost;dbname=test', 'admin', 'Suc355o$');
	$conn->query($sql[0]);
	$res = $conn->query($sql[1]);
	foreach ($res as $k => $line) _::show($line);
	exit;
} else {
	$conn = Conn::dsn();

	//_::show([$conn]);exit;
	//_::show($conn->detail($sql[2]));
	$conn->query($sql[0]);

	$res = $conn->query($sql[2]);
	//$res->fetch_assoc()
	//_::show($conn->info());
	//_::show("$res");
	$x=$res->fetch_fieldsTable();
	_::showTable($x);

	_::show($res->count());
	_::showTable($res);
	//foreach ($res as $k => $line) _::show($line);

	$res = $conn->query($sql[3]);
	_::show($res->count());
	_::showTable($res);

	exit;
}

$dsn = [
	'protocol' => 'mysql',
	'host' => 'localhost',
	'dbname' => 'test',
	'user' => 'admin',
	'passwd' => 'Suc355o$',
	'options' => null,
];

$x = new Vault;
//$x->add($dsn);
print_r($x(''));
