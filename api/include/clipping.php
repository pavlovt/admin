<?
/**
 * Clipping API
 */

$app->get('/hellow/{name}', function($name) use($app) { 
    return 'Hello '.$app->escape($name); 
});