<?php
# Arquivo: thumb.php
# Autor: Helbert Fernandes
($x=@$_GET['x']) || ($x=80);
($y=@$_GET['y']) || ($y=80);

# Pega onde está a imagem
$root=@$_GET['root'];
(is_file($file=($root.$_GET['img']))) || ($file=realpath('./'.$_GET['img'])) || ($file=realpath('./'.$root.$_GET['img']));
$parts_path=pathinfo($file);
$ext=(!$file || !file_exists($file))?'':strtolower(@$parts_path['extension']);

# Carrega a imagem
$funcoes=array(
	'gif' =>array('create'=>'imagecreatefromgif',   'show'=>'imagegif', 'content'=>'image/gif',),
	'jpg' =>array('create'=>'imagecreatefromjpeg',  'show'=>'imagejpeg','content'=>'image/jpeg',),
	'png' =>array('create'=>'imagecreatefrompng',   'show'=>'imagepng', 'content'=>'image/png',),
	'wbmp' =>array('create'=>'imagecreatefromwbmp', 'show'=>'imagewbmp','content'=>'image/bmp',),
	'bmp' =>array('create'=>'ImageCreateFromBMP',   'show'=>'imagepng', 'content'=>'image/png',),
	'xbm' =>array('create'=>'imagecreatefromxbm',   'show'=>'imagepng', 'content'=>'image/png',),
	'xpm' =>array('create'=>'imagecreatefromxpm',   'show'=>'imagejpeg','content'=>'image/jpeg',),
	'gd'  =>array('create'=>'imagecreatefromgd',    'show'=>'imagegd',  'content'=>'imagegd',),
	'gd2' =>array('create'=>'imagecreatefromgd2',   'show'=>'imagegd2', 'content'=>'imagegd',),
	//'str'=>array('create'=>'imagecreatefromstring','show'=>'imagepng', 'content'=>'image/png',),
);

if($ext) do {
	$cmd=@$funcoes[$ext];
	if($cmd) unset($funcoes[$ext]);
	else $cmd=array_shift($funcoes);
	$fn=$cmd['create'];
	$img=$fn($file);
} while($funcoes && !$img);
if(!$img) {
	$img=imagecreate(160, 120);
	imagecolorallocate($img, 204, 204, 204);
	$c = imagecolorallocate($img, 153, 153, 153);
	$c1 = imagecolorallocate($img, 0, 0, 0);
	imageline($img, 0, 0, 160, 120, $c);
	imageline($img, 160, 0, 0, 120, $c);
	imagestring($img, 5, 60, 20, "ERRO:", $c1);
	imagestring($img, 5, 65, 50, "Sem", $c1);
	imagestring($img, 5, 50, 70, "Imagem", $c1);
	$cmd=array('create'=>'imagecreatefromjpeg','show'=>'imagejpeg','content'=>'image/jpeg',);
}
header('Content-type: '.$cmd['content']);

#Refaz a escala da imagem
// Pega o tamanho da imagem e proporção de resize
$width = imagesx($img);
$height = imagesy($img);
$scale = min($x/$width, $y/$height);
if ($scale < 1) {
	$new_width = floor($scale * $width);
	$new_height = floor($scale * $height);
	// Cria uma imagem temporária
	$tmp_img = imagecreatetruecolor($new_width, $new_height);
	// Copia e resize a imagem velha na nova
	imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	imagedestroy($img);
	$img = $tmp_img;
}

#Mostra a imagem
$fn=$cmd['show'];
$fn($img);

function ImageCreateFromBMP($filename) {
	//Ouverture du fichier en mode binaire
	if (! $f1 = fopen($filename,"rb")) return false;

	//1 : Chargement des ent?tes FICHIER
	$FILE = unpack('vfile_type/Vfile_size/Vreserved/Vbitmap_offset', fread($f1,14));
	if ($FILE['file_type'] != 19778) return false;

	//2 : Chargement des ent?tes BMP
	$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel/Vcompression/Vsize_bitmap/Vhoriz_resolution/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
	$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
	if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
	$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
	$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
	$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
	$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
	$BMP['decal'] = 4-(4*$BMP['decal']);
	if ($BMP['decal'] == 4) $BMP['decal'] = 0;

	//3 : Chargement des couleurs de la palette
	$PALETTE = array();
	if ($BMP['colors'] < 16777216) $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));

	//4 : Cr?ation de l'image
	$IMG = fread($f1,$BMP['size_bitmap']);
	$VIDE = chr(0);

	$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
	$P = 0;
	$Y = $BMP['height']-1;
	while ($Y >= 0) {
		$X=0;
		while ($X < $BMP['width']) {
			if ($BMP['bits_per_pixel'] == 24) $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
			elseif ($BMP['bits_per_pixel'] == 16) { 
				$COLOR = unpack("n",substr($IMG,$P,2));
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			elseif ($BMP['bits_per_pixel'] == 8) {
				$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			elseif ($BMP['bits_per_pixel'] == 4) {
				$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
				if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; 
				else $COLOR[1] = ($COLOR[1] & 0x0F);
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			elseif ($BMP['bits_per_pixel'] == 1) {
				$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
				if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
				elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
				elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
				elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
				elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
				elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
				elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
				elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			}
			else return false;
			imagesetpixel($res,$X,$Y,$COLOR[1]);
			$X++;
			$P += $BMP['bytes_per_pixel'];
		}
		$Y--;
		$P+=$BMP['decal'];
	}

	//Fermeture du fichier
	fclose($f1);

	return $res;
}