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
if(count($params) != 0 && $params[0] == "thumb" && !empty($params[1]) && !empty($params[2])){
	// Thumbnailer
	getThumb(decodeUrl($params[1]),$params[2]);
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
	header('WWW-Authenticate: Basic realm="'.translate("Protected gallery").'"');
	header('HTTP/1.0 401 Unauthorized');
	header("Location: /");
}

header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");

// Print gallery
if(count($params) != 0 && $params[0] == "gallery" && !empty($params[1])) {
	if(isGalleryProtected($params[1])) {
		if(!isPrivilegedTo($params[1])) {
			if(!isSet($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_PW'] != getGalleryPassword($params[1])) {
				header('WWW-Authenticate: Basic realm="'.translate("Protected gallery").'"');
				header('HTTP/1.0 401 Unauthorized');
				$template->wrongPassword = TRUE;
			} else {
				if(isSet($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == getGalleryPassword($params[1])) {
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
