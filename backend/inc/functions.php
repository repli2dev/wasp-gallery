<?php
use Nette\Image;

require_once(__DIR__ . '/Nette.phar');

function getError($type) {
	return array(
		'error' => (int) $type
	);
}

function exitWithJSON($output) {
	header('Content-type: application/json; charset=utf-8');
	echo \Nette\Utils\Json::encode($output);
	exit;
}

function sanitizeInput($input) {
	$input = str_replace('..', '', $input);
	return $input;
}

function getGalleries($dir) {
	if(!file_exists($dir) || !is_dir($dir)) {
		return array();
	}
	$dirs = array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir($dir."/".$file)) {
				$item = array();
				// Try to get name and info
				if(file_exists($dir.'/'.$file.'/_info.txt')) {
					$item['info'] = file_get_contents($dir.'/'.$file.'/_info.txt');
				}
				if(file_exists($dir.'/'.$file.'/_name.txt')) {
					$item['name'] = file_get_contents($dir.'/'.$file.'/_name.txt');
				}
				$dirs[$file] = $item;
			}
		}
		closedir($handle);
	}
	uksort($dirs, 'strcasecmp');
	return $dirs;
}

function getGalleryImages($path) {
	if(!file_exists($path) || !is_dir($path)) {
		return array();
	}
	$images = array();
	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "_name.txt" && $file != "_info.txt" && $file != "_pass.txt" && is_file($path . '/' . $file)) {
				$images[] = $file;
			}
		}
		closedir($handle);
	}
	usort($images, 'strcasecmp');
	return $images;
}

function isGalleryProtected($path) {
	if(getGalleryPassword($path) != FALSE) {
		return TRUE;
	} else {
		return FALSE;
	}
}
function getGalleryPassword($path) {
	if(file_exists($path.'/_pass.txt')) {
		return trim(file_get_contents($path.'/_pass.txt'));
	} else {
		return FALSE;
	}
}

function isPrivilegedTo($path) {
	if(isSet($_SESSION[$path]) && $_SESSION[$path] == TRUE) {
		return TRUE;
	} else {
		return FALSE;
	}
}
function privilegeTo($path) {
	$_SESSION[$path] = TRUE;
}

function getThumb($gallery,$image, $path, $cache, $width, $height) {
	$cacheImage = $cache.'/'.$gallery.'/'.$image;
	if(!file_exists($cacheImage)){
		$fullImage = $path.'/'.$gallery.'/'.$image;
		$thumb = Image::fromFile($fullImage);
		$thumb->resize($width, $height);
		if(!file_exists($cache.'/'.$gallery)) {
			mkdir($cache.'/'.$gallery);
			chmod($cache.'/'.$gallery,0777);
		}
		$thumb->save($cacheImage,90);
	} else {
		$thumb = Image::fromFile($cacheImage);
	}
	$thumb->send(Image::JPEG,90);
}

function getFull($gallery,$image, $path) {
	$fullImage = $path.'/'.$gallery.'/'.$image;
	header('Content-Type: '.mime_content_type($fullImage) );
	if(file_exists($fullImage)){
		$f = fopen($fullImage, 'rb');
		if($f === FALSE) {
			return FALSE;
		}
		while(!feof($f)) {
			print fread($f,1024*1024);
			// Needed for big files
			ob_flush();
			flush();
		}
		fclose($f);
		return TRUE;
	} else {
		return FALSE;
	}
}