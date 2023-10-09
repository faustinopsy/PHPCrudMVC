<?php

use App\Model\Usuarios;
use PHPUnit\Framework\TestCase;

class UsuariosTest extends TestCase
{
    protected $usuario;

    protected function setUp(): void
    {
        $this->usuario = new Usuarios();
    }

    public function testSetAndGetId()
    {
        $id = 1;
        $this->usuario->setId($id);
        $this->assertEquals($id, $this->usuario->getId());
    }

    public function testSetAndGetNome()
    {
        $nome = "Test User";
        $this->usuario->setNome($nome);
        $this->assertEquals($nome, $this->usuario->getNome());
    }

    public function testSetAndGetEmail()
    {
        $email = "test@example.com";
        $this->usuario->setEmail($email);
        $this->assertEquals($email, $this->usuario->getEmail());
    }

    public function testSetAndGetSenha()
    {
        $senha = "Test1234";
        $this->usuario->setSenha($senha);
        $this->assertEquals(password_verify($senha,$this->usuario->getSenha()), $this->usuario->getSenha());
    }
}
