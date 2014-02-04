<?php
// Load Nette
require_once(__DIR__ . '/inc/Nette.phar');
require_once(__DIR__ . '/inc/functions.php');

// Configure application
$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(__DIR__ . '/inc/temp/');

// Create Dependency Injection container
$configurator->setTempDirectory(__DIR__ . '/inc/temp');
$configurator->addConfig(__DIR__ . '/inc/config.neon');
$container = $configurator->createContainer();

// Populate settings
$parameters = $container->getParameters();
$gallerySettings = $parameters['gallery'];

// Start session
$session = $container->getService('session');
$session->start();

//------------------------------------------------------------------------------
// Routing the API requests
//------------------------------------------------------------------------------
$httpRequest = $container->getService('httpRequest');
$request = $httpRequest->getQuery('request');

// Get language template
if($request == 'language') {
	$language = $httpRequest->getQuery('language');
	$language = sanitizeInput($language);
	$path = __DIR__ . '/inc/lang/' . $language. '.php';
	if(file_exists($path)) {
		include_once($path);
		exitWithJSON($messages);
	}
	exitWithJSON(getError(1));
}

// Get all galleries
if($request == 'galleries'){
	$path = __DIR__ . '/../' . $gallerySettings['dir'];
	exitWithJSON(getGalleries($path));
}

// Get items from galleries
if($request == 'images'){
	$gallery = $httpRequest->getQuery('gallery');
	$gallery = sanitizeInput($gallery);
	if(empty($gallery)) {
		exitWithJSON(getError(2));
	}
	$path = __DIR__ . '/../' . $gallerySettings['dir'] . '/' . $gallery;
	if(isGalleryProtected($path) && !isPrivilegedTo($path)) {
		exitWithJSON(getError(3));
	} else {
		exitWithJSON(getGalleryImages($path));
	}

}

// Authorize for gallery
if($request == 'authorize') {
	$gallery = $httpRequest->getQuery('gallery');
	$gallery = sanitizeInput($gallery);
	$password = $httpRequest->getQuery('password');
	if(empty($gallery) || empty($password)) {
		exitWithJSON(getError(4));
	}
	$path = __DIR__ . '/../' . $gallerySettings['dir'] . '/' . $gallery;
	if($password == getGalleryPassword($path)) {
		privilegeTo($path);
		exitWithJSON(array('success'));
	}
	exitWithJSON(getError(5));
}

// Logout
if($request == 'logout') {
	$session->destroy();
	exitWithJSON(array('success'));
}

// Get thumb picture
if($request == 'full') {
	$gallery = $httpRequest->getQuery('gallery');
	$gallery = sanitizeInput($gallery);
	$image = $httpRequest->getQuery('image');
	$image = sanitizeInput($image);
	$path = __DIR__ . '/../' . $gallerySettings['dir'] . '/' . $gallery;
	if(empty($image) || empty($gallery)) {
		exitWithJSON(getError(6));
	}
	if(!isGalleryProtected($path) || isPrivilegedTo($path)) {
		if(!getFull($gallery, $image, __DIR__ . '/../' . $gallerySettings['dir'])) {
			header('HTTP/1.1 403 Forbidden');
		}
	} else {
		header('HTTP/1.1 403 Forbidden');
	}
	exit;
}

// Full image (due permission check)
if($request == 'thumb') {
	$gallery = $httpRequest->getQuery('gallery');
	$gallery = sanitizeInput($gallery);
	$image = $httpRequest->getQuery('image');
	$image = sanitizeInput($image);
	$path = __DIR__ . '/../' . $gallerySettings['dir'] . '/' . $gallery;
	if(empty($image) || empty($gallery)) {
		exitWithJSON(getError(6));
	}
	if(!isGalleryProtected($path) || isPrivilegedTo($path)) {
		getThumb(
			$gallery,
			$image,
			__DIR__ . '/../' . $gallerySettings['dir'],
			__DIR__ . '/../' . $gallerySettings['cache'],
			$gallerySettings['width'],
			$gallerySettings['height']);
	} else {
		header('HTTP/1.1 403 Forbidden');
	}
	exit;
}