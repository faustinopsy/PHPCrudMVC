<?php

use App\Controller\AulaController;
use App\Model\Aula;
use PHPUnit\Framework\TestCase;

class AulaControllerTest extends TestCase {
    protected $controller;
    protected $model;

    protected function setUp(): void {
        $this->model = new Aula();
        $this->controller = new AulaController($this->model);
    }

    public function testInsert() {
        $reflection = new ReflectionClass($this->model);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $property) {
            $propName = $property->getName();
            $setterMethod = 'set' . ucfirst($propName);
            
            if (method_exists($this->model, $setterMethod)) {
                $type = $property->getType()->getName();
                
                switch($type) {
                    case 'int':
                        $testValue = 1;
                        break;
                    case 'string':
                        $testValue = 'TestValue';
                        break;
                    case 'DateTime':
                        $testValue = new \DateTime();
                        break;
                    default:
                        $testValue = 'TestValue';
                }
                
                $this->model->$setterMethod($testValue); 
            }
        }
        

        $this->assertTrue($this->controller->inserir());

       $lastInsertId = $this->controller->getLastInsertId();
        $this->assertIsNumeric($lastInsertId);
    }

    public function testSelectAll() {
        $result = $this->controller->buscarTodos();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testSelectById() {
        $id = 1;

        $result = $this->controller->buscarId($id);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testUpdate() {
        $id = 1;
        $newData = [
            'prop1' => 'NewValue1',
            'prop2' => 'NewValue2',
        ];
        $this->assertTrue($this->controller->atualizarId($id));
    }

    public function testDelete() {
        $id = 1;

        $this->assertTrue($this->controller->excluir($id));
    }
}
