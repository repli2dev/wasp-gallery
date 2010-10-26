<?php
// Load Nette
require_once("./inc/Nette.php");
require_once("./inc/functions.php");

// Load config file
Environment::loadConfig('./inc/config.ini');

// Debugging?
$debug = Environment::getConfig("debug");

if($debug->enable){
	Debug::enable();
}

// Set temporary dir
Environment::setVariable('tempDir', dirname(__FILE__) . '/inc/temp');

// Parse incoming URL
if(isSet($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = trim($_SERVER['REQUEST_URI'],'/');
	$params = explode("/",decodeUrl($_SERVER['REQUEST_URI']));
} else {
	$params = array();
}

// Determine witch page is selected
if(isSet($params[2]) && $params[2] == 'page' && isSet($params[3]) && is_numeric($params[3])){
	$page = (int) $params[3];
} else {
	$page = 1;
}

// Init session
session_start();

// Load lang template
$lang = Environment::getConfig("variable")->lang;
if(file_exists("./inc/lang/".$lang.".php")){
	include_once("./inc/lang/".$lang.".php");
}

// Initiaze gallery settings
$dir = Environment::getConfig("gallery")->dir;
$cache = Environment::getConfig("gallery")->cache;
$timeout= Environment::getConfig("gallery")->timeout;

// Content
// Thumbnailer
if(count($params) != 0 && $params[0] == "thumb" && !empty($params[1]) && !empty($params[2])){
	if(!isGalleryProtected($params[1]) || isPrivilegedTo($params[1])) {
		getThumb(decodeUrl($params[1]),$params[2]);
	} else {
		header("Location: /gallery/".$params[1]."/");
	}
	exit;
}

// Full image (due permission check)
if(count($params) != 0 && $params[0] == "full" && !empty($params[1]) && !empty($params[2]) && !empty($params[3])){
	if(!isGalleryProtected($params[2]) || isPrivilegedTo($params[2])) {
		if(!getFull(decodeUrl($params[2]),$params[3])) {
			header("Location: /gallery/".$params[2]."/");
		}
	} else {
		header("Location: /gallery/".$params[2]."/");
	}
	exit;
}

// Create template
$template = new Template();
// Fill template with shared data
$template->web = Environment::getConfig("web");
$template->menu = getGalleries();
$template->dir = $dir;
$template->cache = $cache;
$template->page = $page;
$template->timeout = $timeout;
// Register filters
$template->registerFilter($filter = new LatteFilter);
$filter->handler->macros['toLang'] = '<?php echo translate(%%); ?>';
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->registerHelper('escapeJs', 'Nette\Templates\TemplateHelpers::escapeJs');
$template->registerHelper('escapeCss', 'Nette\Templates\TemplateHelpers::escapeCss');
$template->registerHelper('truncate','Nette\String::truncate');
$template->registerHelper('url', 'encodeUrl');

// If GD is not present show warning
if(!function_exists("gd_info")) {
	$template->showGDWarning = TRUE;
}

// Logout
if(count($params != 0) && $params[0] == "logout") {
	session_destroy();
	header("Location: /");
}

// Print gallery
if(count($params) != 0 && $params[0] == "gallery" && !empty($params[1])) {
	if(isGalleryProtected($params[1])) {
		if(!isPrivilegedTo($params[1])) {
			if(!isSet($_POST['password']) || $_POST['password'] != getGalleryPassword($params[1])) {
				$template->wrongPassword = TRUE;
			} else {
				if(isSet($_POST['password']) && $_POST['password'] == getGalleryPassword($params[1])) {
					$_SESSION[$params[1]] = TRUE;
				} else {
					$template->wrongPassword = TRUE;
				}
			}
		}
	}
	$template->setFile('./inc/tpl/gallery.phtml');
	$template->currentGallery = $params[1];
	$template->galleryName = getGalleryName($params[1]);
	$template->galleryInfo = getGalleryInfo($params[1]);
	$template->numberOfImages = numberOfItems($params[1]);
	$template->perPage = Environment::getConfig('gallery')->perPage;
	$template->perRow = Environment::getConfig('gallery')->perRow;
	$template->images = getGalleryImages($params[1]);
} else {
	// Print default homepage
	$template->setFile('./inc/tpl/index.phtml');
	$template->currentGallery = NULL;
}

// Render template
$template->render();
