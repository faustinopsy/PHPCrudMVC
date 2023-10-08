<?php
require_once 'vendor/autoload.php'; 

use App\Controller\UserController;
use App\Database\Connection;
use App\Model\Usuarios;

$dbConnection = new Connection();

$user = new Usuarios();
$id=19;
$user->setNome("John xx");
$user->setEmail("13xxxn@example.com");
$user->setSenha("John1234x");
$userRepo = new UserController($user);
$userRepo->inserir();
$userRepo->excluir($id);

$retrievedUser = $userRepo->buscarEmail("13xxxn@example.com");

var_dump($retrievedUser);