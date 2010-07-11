<?php

function getGalleries() {
	global $dir;
	$dirs = array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir($dir."/".$file)) {
				$dirs[] = $file;
			}
		}
		closedir($handle);
	}
	return $dirs;
}

function getGalleryName($gallery) {
	global $dir;
	if(file_exists($dir.'/'.$gallery.'/_name.txt')) {
		return file_get_contents($dir.'/'.$gallery.'/_name.txt');
	} else {
		return $gallery;
	}
}
function numberOfItems($gallery) {
	return count(getGalleryImages($gallery));
}

function getGalleryImages($gallery) {
	global $dir;
	$images = array();
	if ($handle = opendir($dir.'/'.$gallery)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "_name.txt" && $file != "_info.txt" && is_file($dir.'/'.$gallery.'/'.$file)) {
				$images[] = $file;
			}
		}
		closedir($handle);
	}
	return $images;
}

function getGalleryInfo($gallery) {
	global $dir;
	if(file_exists($dir.'/'.$gallery.'/_info.txt')) {
		return file_get_contents($dir.'/'.$gallery.'/_info.txt');
	} else {
		return NULL;
	}
}

function getThumb($gallery,$image) {
	global $cache;
	global $dir;
	$cacheImage = $cache.'/'.$gallery.'/'.$image;
	if(!file_exists($cacheImage)){
		$fullImage = $dir.'/'.$gallery.'/'.$image;
		$width = Environment::getConfig("gallery")->width;
		$height= Environment::getConfig("gallery")->height;
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

function paginator($gallery,$count,$page) {
	// Create template
	$template = new Template();
	$template->setFile('./inc/tpl/paginator.phtml');
	$template->registerFilter($filter = new LatteFilter);
	$filter->handler->macros['toLang'] = '<?php echo translate(%%); ?>';
	$template->registerHelper('url', 'encodeUrl');
	$template->registerHelper('translate', 'translate');
	// Prepare data
	$perPage = Environment::getConfig('gallery')->perPage;
	$pages = ceil($count/$perPage);

	$template->pages = $pages;
	$template->currentPage = $page;
	$template->currentGallery = $gallery;

	// Render paginator
	$template->render();
}

function translate($s){
	global $messages;
	if(count($messages) == 0){
		return $s;
	} else {
		if(isSet($messages[$s])) {
			return $messages[$s];
		} else {
			return $s;
		}
	}
}

function encodeUrl($s) {
	return rawurlencode(htmlentities($s));
}

function decodeUrl($s){
	return html_entity_decode(urldecode($s));
}