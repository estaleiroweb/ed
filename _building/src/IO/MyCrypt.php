<?php
if(!defined('MCRYPT_RIJNDAEL_128')) define('MCRYPT_RIJNDAEL_128','rijndael-128',true);
if(!defined('MCRYPT_RIJNDAEL_192')) define('MCRYPT_RIJNDAEL_192','rijndael-192',true);
if(!defined('MCRYPT_RIJNDAEL_256')) define('MCRYPT_RIJNDAEL_256','rijndael-256',true);
if(!defined('MCRYPT_MODE_CBC')) define('MCRYPT_MODE_CBC','cbc',true);
if(!defined('MCRYPT_MODE_ECB')) define('MCRYPT_MODE_ECB','ecb',true);

class MyCrypt{
	private $protect=array(
		'iv'=>"1234567890",
		'key'=>'1-lfo942@#AB.;/.)\49498c.,*$23#)',
		'cipher'=>MCRYPT_RIJNDAEL_128,  // MCRYPT_RIJNDAEL_128 | MCRYPT_RIJNDAEL_192 | MCRYPT_RIJNDAEL_256
		'mode'=>MCRYPT_MODE_CBC, // MCRYPT_MODE_CBC | MCRYPT_MODE_ECB
		'pathcipher'=>'',
		'pathmode'=>'',
	);
	protected $iv_size,$key_size,$fullIv,$fullKey,$td;

	public function __construct($iv=false,$key=false,$cipher=false,$mode=false){
		$this->iv=$iv;
		$this->key=$key;
		$this->mode=$mode;
		$this->cipher=$cipher;
		$this->changedIv();
		$this->changedKey();
		$this->initCrypt();
	}
	public function __get($nm){
		if($this->protect[$nm]) return $this->protect[$nm];
	}
	public function __set($nm,$val){
		if (!$val || !isset($this->protect[$nm])) return;
		$this->protect[$nm]=$val;
		if ($nm=='iv') $this->changedIv();
		elseif ($nm=='key') $this->changedKey();
		elseif ($nm=='cipher' || $nm=='mode') $this->initCrypt();
	}
	private function changedIv(){
		$this->iv_size=mcrypt_get_iv_size($this->protect['cipher'],$this->protect['mode']);
		$this->fullIv=substr(str_pad($this->protect['iv'],$this->iv_size,'0'),0,$this->iv_size);
	}
	private function changedKey(){
		$this->key_size=mcrypt_get_key_size($this->protect['cipher'],$this->protect['mode']);
		$this->fullKey=substr(str_pad($this->protect['key'],$this->key_size,'0'),0,$this->key_size);
	}
	private function initCrypt(){
		if ($this->td) mcrypt_module_close($this->td);
		$this->td=mcrypt_module_open($this->protect['cipher'], $this->protect['pathcipher'], $this->protect['mode'], $this->protect['pathmode']);
	}
	public function crypt($text='',$iv=false,$key=false){
		$this->iv=$iv;
		$this->key=$key;
		mcrypt_generic_init($this->td, $this->fullKey, $this->fullIv);
		$crypttext=base64_encode(mcrypt_generic($this->td, $text));
		mcrypt_generic_deinit($this->td);
		return $crypttext;
	}
	public function decrypt($text='',$iv=false,$key=false){
		$this->iv=$iv;
		$this->key=$key;
		mcrypt_generic_init($this->td, $this->fullKey, $this->fullIv);
		$decrypttext=mdecrypt_generic($this->td, base64_decode($text));
		mcrypt_generic_deinit($this->td);
		return $decrypttext;
	}
}
