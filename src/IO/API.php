<?php
namespace EstaleiroWeb\ED\IO;

class API {
	protected $modes = ['json',];
	// https://www.php.net/manual/en/function.curl-setopt.php
	protected $methods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH',];
	protected $readonly = [
		'httpMessage' => null,
	];
	protected $protect = [
		'mode' => 'json',
		'method' => 'POST',
		'url' => null,
		'data' => null,
		'user' => null,
		'passwd' => null,
		'httpMessageOK' => 200, // HTTP Message: OK
		'httpMessageError' => 500, // HTTP Message: Internal Server Error
		'timeout' => null,
	];
	static public $errosCodeGrp=[
		1=>'Information',
		2=>'Successful',
		3=>'Redirection',
		4=>'Client Error',
		5=>'Server Error',
	];
	static public $errosCode=[
		100 => ['Continue', 'The server has received the request headers, and the client should proceed to send the request body'],
		101 => ['Switching Protocols', 'The requester has asked the server to switch protocols'],
		103 => ['Early Hints', 'Used with the Link header to allow the browser to start preloading resources while the server prepares a response'],
		200 => ['OK', 'The request is OK (this is the standard response for successful HTTP requests)'],
		201 => ['Created', 'The request has been fulfilled, and a new resource is created '],
		202 => ['Accepted', 'The request has been accepted for processing, but the processing has not been completed'],
		203 => ['Non-Authoritative Information', 'The request has been successfully processed, but is returning information that may be from another source'],
		204 => ['No Content', 'The request has been successfully processed, but is not returning any content'],
		205 => ['Reset Content', 'The request has been successfully processed, but is not returning any content, and requires that the requester reset the document view'],
		206 => ['Partial Content', 'The server is delivering only part of the resource due to a range header sent by the client'],
		300 => ['Multiple Choices', 'A link list. The user can select a link and go to that location. Maximum five addresses  '],
		301 => ['Moved Permanently', 'The requested page has moved to a new URL '],
		302 => ['Found', 'The requested page has moved temporarily to a new URL '],
		303 => ['See Other', 'The requested page can be found under a different URL'],
		304 => ['Not Modified', 'Indicates the requested page has not been modified since last requested'],
		307 => ['Temporary Redirect', 'The requested page has moved temporarily to a new URL'],
		308 => ['Permanent Redirect', 'The requested page has moved permanently to a new URL'],
		400 => ['Bad Request', 'The request cannot be fulfilled due to bad syntax'],
		401 => ['Unauthorized', 'The request was a legal request, but the server is refusing to respond to it. For use when authentication is possible but has failed or not yet been provided'],
		402 => ['Payment Required', 'Reserved for future use'],
		403 => ['Forbidden', 'The request was a legal request, but the server is refusing to respond to it'],
		404 => ['Not Found', 'The requested page could not be found but may be available again in the future'],
		405 => ['Method Not Allowed', 'A request was made of a page using a request method not supported by that page'],
		406 => ['Not Acceptable', 'The server can only generate a response that is not accepted by the client'],
		407 => ['Proxy Authentication Required', 'The client must first authenticate itself with the proxy'],
		408 => ['Request Timeout', 'The server timed out waiting for the request'],
		409 => ['Conflict', 'The request could not be completed because of a conflict in the request'],
		410 => ['Gone', 'The requested page is no longer available'],
		411 => ['Length Required', 'The "Content-Length" is not defined. The server will not accept the request without it '],
		412 => ['Precondition Failed', 'The precondition given in the request evaluated to false by the server'],
		413 => ['Request Too Large', 'The server will not accept the request, because the request entity is too large'],
		414 => ['Request-URI Too Long', 'The server will not accept the request, because the URI is too long. Occurs when you convert a POST request to a GET request with a long query information '],
		415 => ['Unsupported Media Type', 'The server will not accept the request, because the media type is not supported '],
		416 => ['Range Not Satisfiable', 'The client has asked for a portion of the file, but the server cannot supply that portion'],
		417 => ['Expectation Failed', 'The server cannot meet the requirements of the Expect request-header field'],
		500 => ['Internal Server Error', 'A generic error message, given when no more specific message is suitable'],
		501 => ['Not Implemented', 'The server either does not recognize the request method, or it lacks the ability to fulfill the request'],
		502 => ['Bad Gateway', 'The server was acting as a gateway or proxy and received an invalid response from the upstream server'],
		503 => ['Service Unavailable', 'The server is currently unavailable (overloaded or down)'],
		504 => ['Gateway Timeout', 'The server was acting as a gateway or proxy and did not receive a timely response from the upstream server'],
		505 => ['HTTP Version Not Supported', 'The server does not support the HTTP protocol version used in the request'],
		511 => ['Network Authentication Required', 'The client needs to authenticate to gain network access'],
	];

	public function __construct($url = null, $data = null, $method = null, $mode = null) {
		$this->mode = $mode;
		$this->method = $method;
		$this->url = $url;
		$this->data = $data;
	}
	public function __get($name) {
		if (method_exists($this, $fn = 'get' . $name)) return $this->$fn();
		if (key_exists($name, $this->readonly)) return $this->readonly[$name];
		if (key_exists($name, $this->protect)) return $this->protect[$name];
	}
	public function __set($name, $val) {
		if (method_exists($this, $fn = 'set' . $name)) $this->$fn($val);
		elseif (!key_exists($name, $this->readonly) && key_exists($name, $this->protect)) $this->protect[$name] = $val;
	}

	public function setMode($val) {
		$val = strtolower($val);
		if (!in_array($val, $this->modes)) return;
		$this->protect['mode'] = $val;
	}
	public function setMethod($val) {
		$val = strtoupper($val);
		if (!in_array($val, $this->methods)) return;
		$this->protect['method'] = $val;
	}
	static function htmlError($code){
		http_response_code($code);
		exit;
	}

	public function receive() {
		$merge = [];
		if ($_REQUEST) $merge[] = $_REQUEST;
		if (($data = file_get_contents('php://input')) != '') $merge[] = $this->decodeData($data);
		if (key_exists($k = 'QUERY_STRING', $_SERVER) && $_SERVER[$k]) {
			$merge[] = $this->decodeData(preg_replace('/^.*?\?/', '', $_SERVER[$k]));
		}
		$out = [];
		foreach ($merge as $a) {
			if (is_null($a)) continue;
			if (is_string($a) || is_object($a)) {
				if ($a) $out[] = $a;
			} elseif (is_array($a)) $out = array_merge($out, $a);
		}
		return $out;
	}
	public function request($data = null) {
		if ($data) $this->data = $data;
		return call_user_func([$this, __FUNCTION__ . '_' . $this->mode]);
	}
	public function response($data = null) {
		if ($data) $this->data = $data;
		return call_user_func([$this, __FUNCTION__ . '_' . $this->mode]);
	}
	protected function request_json() {
		$timeout = $this->timeout;

		$curl = curl_init();
		call_user_func([$this, 'send_' . $this->method], $curl, $this->url, $this->data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if ($timeout) curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		// curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);

		// curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1  );
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		// $userAgent = 'Mozilla/5.0 (X11; Fedora;Linux x86; rv:60.0) Gecko/20100101 Firefox/60.0';
		// curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);

		// $headers = [
		// 	"Accept: application/json",
		// 	"Content-Type: application/json",
		// 	"Authorization: Bearer ".$authToken,
		// 	"cache-control: no-cache",
		// 	"x-api-key: whateveriyouneedinyourheader",
		// 	'Content-Length: '.strlen($data),
		// ];
		// curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		// Optional Authentication:
		if ($user = $this->user) {
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, "$user:$this->passwd");
		}

		//for debug only!
		// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		// curl_setopt($curl, CURLOPT_VERBOSE, true);

		$result = curl_exec($curl);
		$this->readonly['httpMessage'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		return $result;
	}
	protected function response_json() {
		$json = json_encode($this->data, JSON_PRETTY_PRINT);
		ob_clean();
		header_remove();
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json; charset=utf-8');
		if ($json === false) {
			// Avoid echo of empty string (which is invalid JSON), and
			// JSONify the error message instead:
			$json = json_encode(["jsonError" => json_last_error_msg()]);
			if ($json === false) {
				// This should not happen, but we go all the way now:
				$json = '{"jsonError":"unknown"}';
			}

			http_response_code($this->httpMessageError);
		} else {
			http_response_code($this->httpMessageOK);
		}
		print $json;
		exit;
	}
	protected function send($curl, $url) {
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
	}
	// solicita a representação de um recurso específico. Requisições utilizando o método GET devem retornar apenas dados
	protected function send_GET($curl, $url, $data) {
		$p = parse_url($url);
		$sep = key_exists('query', $p) ? '&' : '?';
		if (is_object($data)) $data = (array)$data;
		if (is_array($data)) $data = http_build_query($data);
		if (key_exists('fragment', $p)) {
			$url = preg_replace('/#.*?$/', '', $url) . $sep . $data . '#' . $p['fragment'];
		} else {
			$url .= $sep . $data;
		}
		$this->send($curl, $url);
	}
	// solicita uma resposta de forma idêntica ao método GET, porém sem conter o corpo da resposta
	protected function send_HEAD($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
	}
	// utilizado para submeter uma entidade a um recurso específico, frequentemente causando uma mudança no estado do recurso ou efeitos colaterais no servidor
	protected function send_POST($curl, $url, $data) {
		$this->send($curl, $url);
		if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	// substitui todas as atuais representações do recurso de destino pela carga de dados da requisição
	protected function send_PUT($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
		$this->send($curl, $url);
		if (!$data) return;
		if (is_object($data)) $data = (array)$data;
		if (is_array($data)) $data = json_encode($data, JSON_PRETTY_PRINT);
		// if (is_array($data)) $data = http_build_query($data);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	// remove um recurso específico
	protected function send_DELETE($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
	}
	// estabelece um túnel para o servidor identificado pelo recurso de destino
	protected function send_CONNECT($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
	}
	// usado para descrever as opções de comunicação com o recurso de destino
	protected function send_OPTIONS($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
	}
	// executa um teste de chamada loop-back junto com o caminho para o recurso de destino
	protected function send_TRACE($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
	}
	// utilizado para aplicar modificações parciais em um recurso
	protected function send_PATCH($curl, $url, $data) {
		return $this->send_GET($curl, $url, $data);
	}
	protected function decodeData($data) {
		$json = json_decode($data);
		if (json_last_error() === JSON_ERROR_NONE) return $json;
		@parse_str($data, $query);
		if ($query) return $query;
		$query = @unserialize($data);
		if ($query) return $query;
		return $data;
	}
}

/*
	class proxy { // 

		static $server;
		static $client;

		static function headers($str) { // Parses HTTP headers into an array
			$tmp = preg_split("'\r?\n'",$str);
			$output = [];
			$output[] = explode(' ',array_shift($tmp));
			$post = ($output[0][0] == 'POST' ? true : false);

				foreach($tmp as $i => $header) {
					if($post && !trim($header)) {
						$output['POST'] = $tmp[$i+1];
						break;
					}
					else {
						$l = explode(':',$header,2);
						$output[$l[0]] = $l[0].': '.ltrim($l[1]);
					}
				}
			return $output;
		}

		public function output($curl,$data) {
			socket_write(proxy::$client,$data);
			return strlen($data);
		}
	}




	$ip = "127.0.0.1";
	$port = 50000;

	proxy::$server = socket_create(AF_INET,SOCK_STREAM, SOL_TCP);
	socket_set_option(proxy::$server,SOL_SOCKET,SO_REUSEADDR,1);
	socket_bind(proxy::$server,$ip,50000);
	socket_getsockname(proxy::$server,$ip,$port);
	socket_listen(proxy::$server);

	while(proxy::$client = socket_accept(proxy::$server)) {

		$input = socket_read(proxy::$client,4096);
		preg_match("'^([^\s]+)\s([^\s]+)\s([^\r\n]+)'ims",$input,$request);
		$headers = proxy::headers($input);

			echo $input,"\n\n";
				if(preg_match("'^CONNECT '",$input)) { // HTTPS
					// Tell the client we can deal with this
					socket_write(proxy::$client,"HTTP/1.1 200 Connection Established\r\n\r\n");
					// Client sends binary data here (SSLv3, TLS handshake, Client hello?)
					// socket_read(proxy::$client,4096);
				}
				else { // HTTP

							$input = preg_replace("'^([^\s]+)\s([a-z]+://)?[a-z0-9\.\-]+'","\\1 ",$input);
							$curl = curl_init($request[2]);
							curl_setopt($curl,CURLOPT_HEADER,1);
							curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
							curl_setopt($curl,CURLOPT_TIMEOUT,15);
							curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
							curl_setopt($curl,CURLOPT_NOPROGRESS,1);
							curl_setopt($curl,CURLOPT_VERBOSE,1);
							curl_setopt($curl,CURLOPT_AUTOREFERER,true);
							curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
							curl_setopt($curl,CURLOPT_WRITEFUNCTION, array("proxy","output"));
							curl_exec($curl);
							curl_close($curl);
				}
		socket_close(proxy::$client);
	}
	socket_close(proxy::$server);


*/
