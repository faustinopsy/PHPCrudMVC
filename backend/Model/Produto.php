<?php
namespace App\Model;
class Produto {
    private int $id;
    private string $nome;
    private float $preco;
    private int $quantidade;

    public function __construct() {}

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNome() {
        return $this->nome;
    }
    public function setNome($nome) {
        $this->nome = $nome;
    }
    public function getPreco() {
        return $this->nome;
    }
    public function setPreco($preco) {
        $this->preco = $preco;
    }
    public function setQuantidade($quantidade) {
        $this->quantidade = $quantidade;
    }
    public function getQuantidade() {
        return $this->quantidade;
    }

    

   
}
