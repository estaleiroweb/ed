<?php
$quotes=ini_get("magic_quotes_gpc");
if (!isset($_SERVER['HTTP_REFERER'])) exit;
$urlRef=parse_url($_SERVER['HTTP_REFERER']);
if (!isset($urlRef['host']) || ($urlRef['host']!=$_SERVER['SERVER_ADDR'] && $urlRef['host']!=$_SERVER['SERVER_NAME'])) exit;
