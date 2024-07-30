<?php
if (!session_id()) session_start();
if(!@$_SESSION['__autoload']['file']) die('Erro de sessão');
require_once $_SESSION['__autoload']['file'];
