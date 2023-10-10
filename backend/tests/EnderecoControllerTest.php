<?php

use App\Controller\EnderecoController;
use App\Model\Endereco;
use PHPUnit\Framework\TestCase;

class EnderecoControllerTest extends TestCase
{
    protected $enderecoController;
    protected $endereco;

    protected function setUp(): void
    {
        $this->endereco = new Endereco();
        $this->enderecoController = new EnderecoController($this->endereco);
    }

    public function testInserir()
    {
        $this->endereco->setId(1);
        $this->endereco->setCep("12345-678");
        $this->endereco->setRua("Rua Teste");
        $this->endereco->setBairro("Bairro Teste");
        $this->endereco->setCidade("Cidade Teste");
        $this->endereco->setUf("UF");
        $this->endereco->setIduser(1);

        $this->assertTrue($this->enderecoController->inserir());
    }

    public function testBuscarTodos()
    {
        $result = $this->enderecoController->buscarTodos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testAtualizarId()
    {
        $this->endereco->setId(1);
        $this->endereco->setCep("12345-678");
        $this->endereco->setRua("Rua Teste");
        $this->endereco->setBairro("Bairro Teste");
        $this->endereco->setCidade("Cidade Teste");
        $this->endereco->setUf("UF");
        $this->endereco->setIduser(1);

        $this->assertTrue($this->enderecoController->atualizarId(2));
        
    }

    public function testExcluir()
    {
        $this->assertTrue($this->enderecoController->excluir(2));
        
    }
}
