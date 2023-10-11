<?php

namespace App\Model;

class Endereco {
    private int $id;
    private string $cep;
    private string $rua;
    private string $bairro;
    private string $cidade;
    private string $uf;
    private int $iduser;

    public function getId() {
        return $this->id;
    }

    public function setId(int $id) {
        $this->id = $id;
        return $this;
    }

    public function getCep() {
        return $this->cep;
    }

    public function setCep(string $cep) {
        $this->cep = $cep;
        return $this;
    }

    public function getRua() {
        return $this->rua;
    }

    public function setRua(string $rua) {
        $this->rua = $rua;
        return $this;
    }

    public function getBairro() {
        return $this->bairro;
    }

    public function setBairro(string $bairro) {
        $this->bairro = $bairro;
        return $this;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function setCidade(string $cidade) {
        $this->cidade = $cidade;
        return $this;
    }

    public function getUf() {
        return $this->uf;
    }

    public function setUf(string $uf) {
        $this->uf = $uf;
        return $this;
    }

    public function getIduser() {
        return $this->iduser;
    }

    public function setIduser(int $iduser) {
        $this->iduser = $iduser;
        return $this;
    }
}
