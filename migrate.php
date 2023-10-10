<?php
require_once 'vendor/autoload.php'; 


use App\Database\TableCreator;
use App\Model\Usuarios;

$table = new TableCreator();
$user = new Usuarios();
if($table->createTableFromModel($user)){
    echo "Tabela(S) criadas com sucesso";
}else{
    echo "Erro ao criar tabelas";
}

