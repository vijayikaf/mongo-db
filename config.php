<?php
define('MONGO_PORT', 27017);
define('MONGO_SERVER', 'localhost');
define('DATABASE', 'mongotest');

include('lib/MongoDbClass.php');

function __autoload($class_name) {
    include $class_name . '.php';
}
?>