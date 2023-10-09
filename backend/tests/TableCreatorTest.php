<?php

use App\Database\TableCreator;
use App\Database\Connection;
use App\Model\Usuarios;
use PHPUnit\Framework\TestCase;

class TableCreatorTest extends TestCase
{
    protected $tableCreator;
    protected $connection;
    protected $user;

    protected function setUp(): void
    {
        $this->connection = new Connection();
        $this->tableCreator = new TableCreator();
        $this->user = new Usuarios();
    }

    public function testCreateTableFromModel()
    {
        $this->assertTrue($this->tableCreator->createTableFromModel( $this->user));
    }
    
}
