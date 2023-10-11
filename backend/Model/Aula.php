<?php

namespace App\Model;

class Aula {
    private int $id;
    private string $nome;
    private string $data;

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

    public function getData() {
        return $this->data;
    }

    public function setData(string $data) {
        $this->data = $data;
        return $this;
    }
}
