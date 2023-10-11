<?php
namespace App\Model;

class Usuarios {
    private int $id;
    private string $nome;
    private string $email;
    private string $senha;

    public function __construct() {}
    public function getId()
    {
        return $this->id;
    }
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }
    public function getNome()
    {
        return $this->nome;
    }
    public function setNome($nome): self
    {
        $this->nome = $nome;

        return $this;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }
    public function getSenha()
    {
        return $this->senha;
    }
    public function setSenha($senha): self
    {
        $this->senha = password_hash($senha, PASSWORD_DEFAULT);

        return $this;
    }
}
