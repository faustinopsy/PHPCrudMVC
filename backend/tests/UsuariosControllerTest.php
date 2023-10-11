<?php

use App\Controller\UsuariosController;
use App\Model\Usuarios;
use PHPUnit\Framework\TestCase;

class UsuariosControllerTest extends TestCase
{
    protected $userController;
    protected $user;
    protected function setUp(): void
    {
        $this->user = new Usuarios();
        $this->userController = new UsuariosController($this->user);
    }
    public function testInsert()
    {
        $this->user->setId(2);
        $this->user->setNome("Test User");
        $this->user->setEmail("test@example.com");
        $this->user->setSenha("Test1234");
        $this->assertTrue($this->userController->inserir());
        $result = $this->userController->getLastInsertId();
        $this->assertNotEmpty($result);
    }
    public function testSelect()
    {
        $email = "updatedxxx@example.com";
        $result = $this->userController->buscarTodos();
        $this->assertNotEmpty($result);
        $this->assertEquals($email, $result[0]['email'] ?? null);
    }

    public function testUpdate()
    {
        $this->user->setId(2);
        $this->user->setNome("Usuario atualizado");
        $this->user->setEmail("updatedxxx@example.com");
        $this->user->setSenha("Updated1234");
    }

    public function testDelete()
    {
        $this->assertTrue($this->userController->excluir(1));
    }
}
