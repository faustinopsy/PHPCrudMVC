<?php

namespace App\Aula;
require "../../vendor/autoload.php";

use App\Controller\AulaController;
use App\Model\Aula;

$aula = new Aula();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: * ' );
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');

$body = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? $_GET['id'] : '';
switch($_SERVER["REQUEST_METHOD"]){
    case "POST";
        $aula->setNome($body['nome']);
        $aula->setData($body['data']);
        
        $aulaController = new AulaController($aula);
        $resultado = $aulaController->inserir();
        echo json_encode(['status' => $resultado]);
    break;
    case "GET";
        $aulaController = new AulaController($aula);
        if(!isset($_GET['id'])){
            $resultado = $aulaController->buscarTodos();
            if(!$resultado){
                echo json_encode(["status" => false, "Aula" => $resultado,"mensagem"=>"nenhum resultado encontrado"]);
                exit;
            }else{
                echo json_encode(["status" => true, "Aula" => $resultado]);
                exit;
            }
        }else{
            $resultado = $aulaController->buscarId($id);
            if(!$resultado){
                echo json_encode(["status" => false, "Aula" => $resultado,"mensagem"=>"nenhum resultado encontrado"]);
                exit;
            }else{
                echo json_encode(["status" => true, "Aula" => $resultado[0]]);
                exit;
            }
        }
    break;
    case "PUT";
        $aula->setNome($body['nome']);
        $aula->setData($body['data']);
        
        $aulaController = new AulaController($aula);
        $resultado = $aulaController->atualizarId(intval($_GET['id']));
        echo json_encode(['status' => $resultado]);
    break;
    case "DELETE";
        $aulaController = new AulaController($aula);
        $resultado = $aulaController->excluir(intval($_GET['id']));
        echo json_encode(['status' => $resultado]);
    break;  
}