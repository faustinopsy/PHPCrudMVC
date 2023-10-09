<?php

use App\Database\Connection;
use App\Model\Usuarios;
use PHPUnit\Framework\TestCase;

class ConectTest extends TestCase
{
    protected $conection;
   
    protected function setUp(): void
    {
        $this->conection = new Connection();
    }

    public function testConect()
    {
         $this->assertEmpty($this->conection->connect());
    }

}
