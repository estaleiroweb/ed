<?php
require_once 'common.php';

print '<div class="container"><ul class="nav nav-tabs">';
$links=array(
	'Users'=>'users.php',
	'Files'=>'files.php',
	'GroupUsers'=>'group_users.php',
	'GroupFiles'=>'group_files.php',
	'Permitions'=>'permitions.php',
	'Domains'=>'domain.php',
	'Posts'=>'posts.php',
	'Logs'=>'logs.php',
);
$s=Secure::$obj;
$dirName=dirname($__autoload->url);
$baseName=basename($__autoload->url);
if($baseName=='users_edit.php') $baseName='users.php';
foreach($links as $k=>$v) if($s->permitionFile($dirName.'/'.$v)){
	print '<li role="presentation"'.($baseName==$v?' class="active"':'').'><a href="'.$v.'">'.$k.'</a></li>';
}
print '</ul></div>';