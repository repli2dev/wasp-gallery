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

// Load lang template
$lang = Environment::getConfig("variable")->lang;
if(file_exists("./inc/lang/".$lang.".php")){
	include_once("./inc/lang/".$lang.".php");
}

// Initiaze gallery settings
$dir = Environment::getConfig("gallery")->dir;
$cache = Environment::getConfig("gallery")->cache;
$timeout= Environment::getConfig("gallery")->timeout;

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

// Content
if(count($params) != 0 && $params[0] == "thumb" && !empty($params[1]) && !empty($params[2])){
	getThumb(decodeUrl($params[1]),$params[2]);
} else
if(count($params) != 0 && $params[0] == "gallery" && !empty($params[1])) {
	$template->setFile('./inc/tpl/gallery.phtml');
	$template->currentGallery = $params[1];
	$template->galleryName = getGalleryName($params[1]);
	$template->galleryInfo = getGalleryInfo($params[1]);
	$template->numberOfImages = numberOfItems($params[1]);
	$template->perPage = Environment::getConfig('gallery')->perPage;
	$template->perRow = Environment::getConfig('gallery')->perRow;
	$template->images = getGalleryImages($params[1]);
} else {
	$template->setFile('./inc/tpl/index.phtml');
	$template->currentGallery = NULL;
}

// Render template
$template->render();
