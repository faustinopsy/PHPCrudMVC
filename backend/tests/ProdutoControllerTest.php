<?php

use App\Controller\ProdutoController;
use App\Model\Produto;
use PHPUnit\Framework\TestCase;

class ProdutoControllerTest extends TestCase
{
    protected $produtoController;
    protected $produto;

    protected function setUp(): void
    {
        $this->produto = new Produto();
        $this->produtoController = new ProdutoController($this->produto);
    }

    public function testInsert()
    {
        $this->produto->setId(1);
        $this->produto->setNome("Produto Teste");
        $this->produto->setPreco(100.0);
        $this->produto->setQuantidade(10);

        $this->assertTrue($this->produtoController->inserir());
        $result = $this->produtoController->getLastInsertId();
        $this->assertNotEmpty($result);
    }

    

    public function testSelect()
    {
        $nome = "Produto Teste";

        $result = $this->produtoController->buscarTodos();
        $this->assertNotEmpty($result);
        $this->assertEquals($nome, $result[0]['nome'] ?? null);
    }

    public function testUpdate()
    {
        $this->produto->setId(1);
        $this->produto->setNome("Produto Atualizado");
        $this->produto->setPreco(110.0);
        $this->produto->setQuantidade(15);

        $this->assertTrue($this->produtoController->atualizarId(1));
        
    }

    public function testDelete()
    {
        $this->assertTrue($this->produtoController->excluir(1));
    }
}
