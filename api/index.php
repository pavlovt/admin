<?
require_once 'silex.phar';
require_once '../common/config.php';
//require_once 'checkLogin.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->get('/hello/{name}', function($name) use($app) { 
    return 'Hello '.$app->escape($name); 
});

$app->get('/db/{table}', function($table) use($app) { 
    $db = dbWrapper::getDb('db');
    return json_encode($db->query("DESC {$table}")->getAll());
}); 

require_once 'include/clipping.php';

$app->run();