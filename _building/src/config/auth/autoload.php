--force
if (!session_id()) session_start();
//session_cache_limiter('private');
if(!@$_SESSION['__autoload'] || !@$_SESSION['__autoload']->thisFile) die('Erro do autoloader');
require_once $_SESSION['__autoload']->thisFile;
