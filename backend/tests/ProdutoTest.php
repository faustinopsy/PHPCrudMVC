<?php

use PHPUnit\Framework\TestCase;
use App\Model\Produto;
class ProdutoTest extends TestCase
{
    public function testSetAndGetId()
    {
        $produto = new Produto();
        $id = 1;
        $produto->setId($id);
        $this->assertEquals($id, $produto->getId());
    }

    public function testSetAndGetNome()
    {
        $produto = new Produto();
        $produto->setId(1);
        $nome = 'Produto Exemplo';
        $produto->setNome($nome);
        $this->assertEquals($nome, $produto->getNome());
    }

    public function testSetAndGetPreco()
    {
        $produto = new Produto();
        $produto->setId(1);
        $preco = 19.99;
        $produto->setPreco($preco);
        $this->assertEquals($preco, $produto->getPreco());
    }

    public function testSetAndGetQuantidade()
    {
        $produto = new Produto();
        $produto->setId(1);
        $nome = 'Produto Exemplo';
        $produto->setNome($nome);
        $preco = 19.99;
        $produto->setPreco($preco);
        $quantidade = 10;
        $produto->setQuantidade($quantidade);
        $this->assertEquals($quantidade, $produto->getQuantidade());
    }
}
