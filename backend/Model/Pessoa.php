<?php

namespace App\Model;

class Pessoa {
    private int $id;
    private string $nome;
    private int $idade;

    public function getId() {
        return $this->id;
    }

    public function setId(int $id) {
        $this->id = $id;
        return $this;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome(string $nome) {
        $this->nome = $nome;
        return $this;
    }

    public function getIdade() {
        return $this->idade;
    }

    public function setIdade(int $idade) {
        $this->idade = $idade;
        return $this;
    }
}
