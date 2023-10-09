<?php
require_once 'vendor/autoload.php'; 


use App\Database\TableCreator;
use App\Model\Usuarios;

$table = new TableCreator();
$user = new Usuarios();
$table->createTableFromModel($user);

