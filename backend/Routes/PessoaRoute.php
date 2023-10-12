<?php

namespace App\Pessoa;
require "../../vendor/autoload.php";

use App\Controller\PessoaController;
use App\Model\Pessoa;

$pessoa = new Pessoa();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: * ' );
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');

$body = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? $_GET['id'] : '';
switch($_SERVER["REQUEST_METHOD"]){
    case "POST";
        $pessoa->setNome($body['nome']);
        $pessoa->setIdade($body['idade']);
        $pessoa->setAltura($body['altura']);
        
        $pessoaController = new PessoaController($pessoa);
        $resultado = $pessoaController->inserir();
        echo json_encode(['status' => $resultado]);
    break;
    case "GET";
        $pessoaController = new PessoaController($pessoa);
        if(!isset($_GET['id'])){
            $resultado = $pessoaController->buscarTodos();
            if(!$resultado){
                echo json_encode(["status" => false, "Pessoa" => $resultado,"mensagem"=>"nenhum resultado encontrado"]);
                exit;
            }else{
                echo json_encode(["status" => true, "Pessoa" => $resultado]);
                exit;
            }
        }else{
            $resultado = $pessoaController->buscarId($id);
            if(!$resultado){
                echo json_encode(["status" => false, "Pessoa" => $resultado,"mensagem"=>"nenhum resultado encontrado"]);
                exit;
            }else{
                echo json_encode(["status" => true, "Pessoa" => $resultado[0]]);
                exit;
            }
        }
    break;
    case "PUT";
        $pessoa->setNome($body['nome']);
        $pessoa->setIdade($body['idade']);
        $pessoa->setAltura($body['altura']);
        
        $pessoaController = new PessoaController($pessoa);
        $resultado = $pessoaController->atualizarId(intval($_GET['id']));
        echo json_encode(['status' => $resultado]);
    break;
    case "DELETE";
        $pessoaController = new PessoaController($pessoa);
        $resultado = $pessoaController->excluir(intval($_GET['id']));
        echo json_encode(['status' => $resultado]);
    break;  
}