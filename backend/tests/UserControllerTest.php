<?php

use App\Controller\UserController;
use App\Model\Usuarios;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    protected $userController;
    protected $user;

    protected function setUp(): void
    {
        $this->user = new Usuarios();
        $this->userController = new UserController($this->user);
    }

    public function testInsert()
    {
        $this->user->setNome("Test User");
        $this->user->setEmail("test@example.com");
        $this->user->setSenha("Test1234");

        $this->assertTrue($this->userController->inserir());
    }

    public function testSelectByEmail()
    {
        $email = "test@example.com";

        $result = $this->userController->buscarEmail($email);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($email, $result[0]['email'] ?? null);
    }

    public function testUpdate()
    {
        $this->user->setNome("Usuario atualizado");
        $this->user->setEmail("updatedxxx@example.com");
        $this->user->setSenha("Updated1234");

        $this->assertTrue($this->userController->atualizarEmail("test@example.com"));
        
        $updatedUser = $this->userController->buscarEmail("updatedxxx@example.com");
        $this->assertEquals("Usuario atualizado", $updatedUser[0]['nome'] ?? null);
    }

    public function testDelete()
    {
        $this->assertTrue($this->userController->excluir(1));
        $deletedUser = $this->userController->buscarEmail("updated@example.com");
        $this->assertEmpty($deletedUser);
    }
}
