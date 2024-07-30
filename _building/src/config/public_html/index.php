<?php
require_once '../autoload.php';
$_SESSION=array(); //Limpa toda a sess�o

{
	$o=OutHtml::singleton();
	//$l=LayoutAdmin::singleton();
	//__autoload();
	veboseDefaultStyle();
	verbose(); //OR String Full FileName
}
if(0){
	$conn=Conn::dsn('localhost');
	{
		$res=$conn->query('call test.pc_c');
		$res->close();
		verbose($res2->res);
		verbose($conn->fastLine('SELECT * FROM test.currency1 LIMIT 1')['Code']);
	}
	{
		$res1=$conn->query('set @dasdas="11111111111111111"');
		$res2=$conn->query('
		SELECT @dasdas;
		SELECT * FROM test.currency1 LIMIT 2;
		SELECT @i:=1;
		SELECT * FROM test.currency1 LIMIT 2;
		');
		do{
			verbose($res2->res);
			verbose($res2->fetch_assoc_all());
		}while($conn->next());
	}
	//show(__autoload());
	//verbose('pppppp');
	//verbose(gettype($conn));
	//while($line=$res2->fetch_array()) verbose($line);
	//for($i=0;$i<4;$i++) error('Nivel '+$i,$i,($GLOBALS['__autoload']['errorLevels'])[$i]);
}
{
$s=Secure::singleton();
//show($s);

//print "passei!";
//show($l->Auth);
//show(@$_SESSION['__auth']);
}