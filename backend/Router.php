<?php

namespace App;

use App\Controller\UserController;
use App\Model\Usuarios;

class Router {
    private $requestMethod;
    private $uri;
    private $rotas;
    private $usercontroller;
    private $user;

    public function __construct($requestMethod, $uri) {
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->user = new Usuarios();
        $this->routes(); 
    }
    public function run() {
        $this->setHeaders();
        try {
            $ponte = $this->procuraPonte();
           
            if ($ponte) {
                echo json_encode($ponte());
            } else {
                $this->notFound();
            }
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function setHeaders() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: * ' );
        header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }
    private function notFound() {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['error' => 'Página não encontrada']);
    }
    private function handleError(\Exception $e) {
        error_log($e->getMessage());
        if ($e instanceof NotFoundException) {
            echo json_encode(['error' => 'Recurso não encontrado'], 404);
        } elseif ($e instanceof ValidationException) {
            echo json_encode(['error' => 'Dados inválidos', 'errors' => $e->getErrors()], 422);
        } else {
            echo json_encode(['error' => 'Erro interno do servidor'], 500);
        }
    }
    private function routes() {
        $this->rotas = [
            'GET' => [
                '/backend/usuario' => function() {
                    $this->usercontroller = new UserController($this->user);
                    $usuarios= $this->usercontroller->buscarTodos();
                    if(!$usuarios){
                        $data = [
                            'status' => false,
                            'mensagem' => "nenhuma usuario encontrado",
                            'descricao' => "",
                            'usuarios' => ""
                        ];
                        return json_decode(json_encode($data,true));

                    }
                    $data = [
                        'status' => true,
                        'mensagem' => "Usuários recuperados com sucesso",
                        'descricao' => "",
                        'usuarios' => $usuarios
                    ];
                    return json_decode(json_encode($data,true));
                    
                },
                '/backend/usuario/{id}' => function($id) {
                    $this->usercontroller = new UserController($this->user);
                    $usuarios= $this->usercontroller->buscarid($id);
                    if(!$usuarios){
                        $data = [
                            'status' => false,
                            'mensagem' => "nenhuma usuario encontrado",
                            'descricao' => "",
                            'usuarios' => ""
                        ];
                        return json_decode(json_encode($data,true));
                    }
                    $data = [
                        'status' => true,
                        'mensagem' => "Usuários recuperados com sucesso",
                        'descricao' => "",
                        'usuarios' => $usuarios
                    ];
                    return json_decode(json_encode($data,true));
                },
            ],
            'POST' => [
                '/backend/usuario' => function () {
                    $body = json_decode(file_get_contents('php://input'), true);
                    $this->user->setNome($body['nome']);
                    $this->user->setEmail($body['email']);
                    $this->user->setSenha($body['senha']);
                    $this->usercontroller = new UserController($this->user);
                    $usuario = $this->usercontroller->inserir();
                    if(!$usuario){
                        $data = [
                            'status' => false,
                            'mensagem' => "Usuário já existe",
                            'descricao' => "",
                            'usuario' => ""
                        ];
                        return json_decode(json_encode($data,true));
                    }
                    $data = [
                        'status' => true,
                        'mensagem' => "Usuário criado com sucesso",
                        'descricao' => "",
                        'usuario' => $usuario
                    ];
                    return json_decode(json_encode($data,true));
                }
            ],
            'PUT' => [
                '/backend/usuario/{id}' => function($id) { 
                    $data = json_decode(file_get_contents("php://input"), true);
                    $this->user->setNome($data['nome']);
                    $this->user->setEmail($data['email']);
                    $this->user->setSenha($data['senha']);
                    $this->usercontroller = new UserController($this->user);
                    $result= $this->usercontroller->atualizarId($id);
                    if($result){
                        return json_decode(json_encode($data,true));
                    }else{
                        return json_decode(json_encode($data,true));
                    }
                }
            ],
            'DELETE' => [
                '/backend/usuario/{id}' => function($id) {
                    $this->usercontroller = new UserController($this->user);
                    $success= $this->usercontroller->excluir($id);
                    if ($success) {
                        $data = [
                            'status' => true,
                            'mensagem' => "deletado com sucesso",
                            'descricao' => "registro com ID $id foi deletado"
                        ];
                    } else {
                        $data = [
                            'status' => false,
                            'mensagem' => "Erro ao deletar",
                            'descricao' => "Ocorreu um problema ao tentar deletar o ID $id"
                        ];
                    }
                    return json_decode(json_encode($data,true));
                },
            ],
            'OPTIONS' => [
                '/backend/usuarios' => function() {
                    header('HTTP/1.1 200 OK');
                    return;
                }
            ]
        ];
    }
    
    private function procuraPonte() {
        foreach ($this->rotas[$this->requestMethod] as $route => $ponte) {
            $routePattern = preg_replace('/\{.*\}/', '([^/]+)', $route);
            if (preg_match("@^$routePattern$@", $this->uri, $matches)) {
                array_shift($matches);
                return function() use ($ponte, $matches) {
                    return call_user_func_array($ponte, $matches);
                };
            }
        }

        return false;
    }

}