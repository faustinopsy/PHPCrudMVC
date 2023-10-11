<?php

namespace App\Usuario;
require "../../vendor/autoload.php";

use App\Controller\UsuariosController;
use App\Model\Usuarios;

$user = new Usuarios();

$body = json_decode(file_get_contents('php://input'), true);
$id=isset($_GET['id'])?$_GET['id']:'';
switch($_SERVER["REQUEST_METHOD"]){
    case "POST";
        $user->setNome($body['nome']);
        $user->setEmail($body['email']);
        $user->setSenha($body['senha']);
        $users = new UsuariosController($user);
        $resultado = $users->inserir();
        echo json_encode(['status'=>$resultado]);
    break;
    case "GET";
        if(!isset($_GET['id'])){
            $users = new UsuariosController($user);
            $resultado= $users->buscarTodos();
            if(!empty($resultado)){
                echo json_encode(["status"=>true,"usuarios"=>$resultado]);
            }else{
                echo json_encode(["status"=>false]);
            }
            
        }else{
            $users = new UsuariosController($user);
            $resultado = $users->buscarId($id);
            if(!empty($resultado)){
                echo json_encode(["status"=>true,"usuario"=>$resultado[0]]);
            }else{
                echo json_encode(["status"=>false]);
            }
            
        }
       
    break;
    case "PUT";
        $user->setNome($body['nome']);
        $user->setEmail($body['email']);
        $user->setSenha($body['senha']);
        $users = new UsuariosController($user);
        $resultado = $users->atualizarId(intval($_GET['id']));
        echo json_encode(['status'=>$resultado]);
    break;
    case "DELETE";
        $users = new UsuariosController($user);
        $resultado = $users->excluir(intval($_GET['id']));
        echo json_encode(['status'=>$resultado]);
    break;  
}