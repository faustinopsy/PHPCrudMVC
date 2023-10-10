<?php

use PHPUnit\Framework\TestCase;
use App\Model\Endereco;

class EnderecoTest extends TestCase
{
    public function testSetAndGetCep()
    {
        $endereco = new Endereco();
        $cep = '12345-678';
        $endereco->setCep($cep);
        $this->assertEquals($cep, $endereco->getCep());
    }

    public function testSetAndGetRua()
    {
        $endereco = new Endereco();
        $rua = 'Rua Exemplo';
        $endereco->setRua($rua);
        $this->assertEquals($rua, $endereco->getRua());
    }

    public function testSetAndGetBairro()
    {
        $endereco = new Endereco();
        $bairro = 'Bairro Exemplo';
        $endereco->setBairro($bairro);
        $this->assertEquals($bairro, $endereco->getBairro());
    }

    public function testSetAndGetCidade()
    {
        $endereco = new Endereco();
        $cidade = 'Cidade Exemplo';
        $endereco->setCidade($cidade);
        $this->assertEquals($cidade, $endereco->getCidade());
    }

    public function testSetAndGetUf()
    {
        $endereco = new Endereco();
        $uf = 'UF';
        $endereco->setUf($uf);
        $this->assertEquals($uf, $endereco->getUf());
    }

    public function testSetAndGetIduser()
    {
        $endereco = new Endereco();
        $userid = 1;
        $endereco->setIduser($userid);
        $this->assertEquals($userid, $endereco->getIduser());
    }
}
