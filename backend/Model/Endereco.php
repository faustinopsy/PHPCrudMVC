<?php

namespace App\Model;
class Endereco{
private int $id;
private string $cep;
private string $rua;
private string $bairro;
private string $cidade;
private string $uf;
private int $userid;
public function __construct() {}
/**
 * Get the value of cep
 */
public function getCep()
{
return $this->cep;
}

/**
 * Set the value of cep
 */
public function setCep($cep): self
{
$this->cep = $cep;

return $this;
}

/**
 * Get the value of rua
 */
public function getRua()
{
return $this->rua;
}

/**
 * Set the value of rua
 */
public function setRua($rua): self
{
$this->rua = $rua;

return $this;
}

/**
 * Get the value of bairro
 */
public function getBairro()
{
return $this->bairro;
}

/**
 * Set the value of bairro
 */
public function setBairro($bairro): self
{
$this->bairro = $bairro;

return $this;
}

/**
 * Get the value of cidade
 */
public function getCidade()
{
return $this->cidade;
}

/**
 * Set the value of cidade
 */
public function setCidade($cidade): self
{
$this->cidade = $cidade;

return $this;
}

/**
 * Get the value of uf
 */
public function getUf()
{
return $this->uf;
}

/**
 * Set the value of uf
 */
public function setUf($uf): self
{
$this->uf = $uf;

return $this;
}

/**
 * Get the value of iduser
 */
public function getIduser()
{
return $this->userid;
}

/**
 * Set the value of iduser
 */
public function setIduser($userid): self
{
$this->userid = $userid;

return $this;
}
}