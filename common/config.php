<?
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);


// permissions
$permissionGroupArray = array(
	"admin" => "Administrator",
	"manager" => "Manager",
	"user" => "User",
	"accountantManager" => "Accountant Manager",
	"accountant" => "Accountant"
);

ini_set("include_path", ini_get('include_path').":/var/www/gameMonger/:/var/www/gameMonger/frontEnd");

// variables for campaign attachements suppport
// always use / for directory separator even in windows!
$documentsDir = "/var/www/gameMonger/attachments/"; $verifyDirs[] = $documentsDir;
$documentsDirWww = "/gameMonger/attachments/";
$miAttachmentsDocumentsDir = "/tmp/";

// contain all db connections - every key is a new db object - $db, $dbFb etc.
$dbSettings = array(
  "db1" => array("host" => "192.168.0.5", "user" => "root1", "password" => "rootpass1", "dbName" => "mydb1"),
  "db2" => array("host" => "192.168.0.6", "user" => "root2", "password" => "rootpass2", "dbName" => "mydb2"),
);

define("host", "192.168.0.5");
define("basePath", "/var/www/wrsite/");
define("classPath", basePath."class/webresearch/");
define("includePath", basePath."frontEnd/include/base/");
define("webPath", "/wrsite/");
define("frontPath", webPath."frontEnd/");
define("includeWebPath", webPath."frontEnd/include/base/");
define("jsPath", webPath."js/");
define("index_page", frontPath."index.php");
define("login_page", frontPath."include/base/login.php");
define("pdfUrl", host.webPath."common/pdf/");
define("pdfPath", basePath."common/pdf/");
define("pdfFilePath", pdfPath."files/");

// used in ajax loaded pages
define('invalidParameters', "На страницата са подадени невалидни параметри");
define('noDirectAccessError', 'Забранен е директния достъп до тази страница');
define('notSubscribedError', "Не сте абонирани за тази услуга");
define('notSubscribedCategory', "Не сте абонирани за избраните категории");
define('connectionError', "Грешка при зареждане на страницата - моля опитайте по-късно");
define('noInfo', '<p style="margin-left:5px;">Няма налична информация за избрания период</p>');

define("articles_per_page", 20);

define("START_DATE", date("Y-m-d", strtotime("- 1 week")));
define("END_DATE", date("Y-m-d", strtotime("now")));

// internet media we are following in media crawler
define('followMedia', json_encode(array("24 часа" => "24chasa.bg", "Дарик" => "dariknews%article_id=", "Дир.бг" => "dnes.dir.bg", "Дневник" => "dnevnik.bg", "Днес" => "dnes.bg", "Вести" => "vesti.bg", "Труд" => "trud.bg")));

// available pages
define('pages', json_encode(array('index' => 'Index', 'clipping' => 'Clipping', 'wrindex' => 'Webresearch Index')));


// object relational mapping class
require_once(basePath."common/functions.php");
require_once(basePath."common/functions.site.php");
require_once(basePath."class/class.db.php");
require_once(basePath."class/class.dbWrapper.php");
require_once(basePath."class/class.inputfilter.php");
require_once(basePath."common/db.php");
require_once(basePath."class/validation.php");
require_once(basePath."class/class.baseTable.php");
require_once(basePath."common/underscore.php");
require_once(basePath.'class/coffeescript/coffeescript.php');
require_once(basePath.'class/debug/dd.php');
//require_once(basePath."common/session_handler.php");

session_start();