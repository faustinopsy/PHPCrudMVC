<?php
require_once 'vendor/autoload.php'; 

use App\Database\TableCreator;
use App\Model\Endereco;
use App\Model\Produto;
use App\Model\Usuarios;

$table = new TableCreator();
$user = new Usuarios();
$endereco = new Endereco();
$produto = new Produto();
if($table->createTableFromModel($user)){
    echo "Tabela 1 criada com sucesso";
}else{
    echo "Erro ao criar tabelas";
}
if($table->createTableFromModel($endereco)){
    echo "Tabela 2 criada com sucesso";
}else{
    echo "Erro ao criar tabelas";
}
if($table->createTableFromModel($produto)){
    echo "Tabela 3 criada com sucesso";
}else{
    echo "Erro ao criar tabelas";
}

