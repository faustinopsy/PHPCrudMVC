<?php

namespace App\Pessoa;
require "../../vendor/autoload.php";

use App\Controller\PessoaController;
use App\Model\Pessoa;

$pessoa = new Pessoa();

$body = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? $_GET['id'] : '';
switch($_SERVER["REQUEST_METHOD"]){
    case "POST";
        $pessoa->setNome($body['nome']);
        $pessoa->setIdade($body['idade']);
        
        $pessoaController = new PessoaController($pessoa);
        $resultado = $pessoaController->inserir();
        echo json_encode(['status' => $resultado]);
    break;
    case "GET";
        $pessoaController = new PessoaController($pessoa);
        if(!isset($_GET['id'])){
            $resultado = $pessoaController->buscarTodos();
            echo json_encode(["status" => !empty($resultado), "Pessoa" => $resultado[0]]);
        }else{
            $resultado = $pessoaController->buscarId($id);
            echo json_encode(["status" => !empty($resultado), "Pessoa" => $resultado[0]]);
        }
    break;
    case "PUT";
        $pessoa->setNome($body['nome']);
        $pessoa->setIdade($body['idade']);
        
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