<?php
namespace App\Database;

use App\Database\Connection;
use Exception;
use PDOException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class TableCreator extends Connection{

    public function __construct() {
        parent::__construct();
    }

    private function mapPhpTypeToSqlType($type) {
        switch ($type) {
            case 'int':
                return "INT";
            case 'float':
                return "FLOAT";
            case 'DateTime':
                return "DATETIME";
            case 'string':
                return "VARCHAR(255)";
            case 'bool':
                return "BOOLEAN";
            default:
                throw new Exception("Tipo PHP não mapeado: $type");
        }
    }
    
    public function createTableFromModel($model) {
        try{
            $reflection = new ReflectionClass($model);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            $columns = [];
            $columnNames = [];  
            foreach ($properties as $property) {
                $columnName = $property->getName();
                $type = $property->getType()->getName();
                if (!$type) {
                    continue; 
                }
                $sqlType = $this->mapPhpTypeToSqlType($type);
                $columns[] = "{$columnName} {$sqlType}";
                $columnNames[] = $columnName;
            }
    
        $tableName = str_replace('App','',str_replace('Model','',str_replace('\\','',$reflection->getName())));

        $columnsSql = implode(', ', $columns);
        $createTableSql = "CREATE TABLE IF NOT EXISTS {$tableName} (".str_replace('id INT,','id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,',$columnsSql).")";
        $stmt = $this->conn->prepare($createTableSql);
        $stmt->execute();
    
        $placeholders = array_map(function($colName) { return ":{$colName}"; }, $columnNames);

        $this->createInsertProcedure($tableName, $columnNames, $columns);
        $this->createUpdateProcedure($tableName, $columns);
        $this->createDeleteProcedure($tableName);
        $this->createSelectAllProcedure($tableName);
        $this->createSelectByIdProcedure($tableName);
        return true;
    } catch (ReflectionException $e) {
        echo "Erro de Reflexão: " . $e->getMessage();
    } catch (PDOException $e) {
        echo "Erro de Banco de Dados: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
    return false;
    }
    public function createController($model){
        $reflection = new ReflectionClass($model);
        $className= $reflection->getShortName();
        $controllerTemplate = <<<EOT
            <?php

            namespace App\Controller;

            use App\Database\Crud;

            class {{className}}Controller extends Crud{
                protected \$table;
                public function __construct(\$classe) {
                    parent::__construct();
                    \$this->table = \$classe;
                }
                public function inserir() {
                    return \$this->insert(\$this->table);
                }
                public function buscarTodos() {
                    return \$this->select(\$this->table,[]);
                 }
                 public function buscarId(\$id) {
                    return \$this->select(\$this->table,['id' => \$id]);
                 }
                 public function atualizarId(\$id) {
                    return \$this->update(\$this->table ,['id' => \$id]);
                 }  
                public function excluir(\$id) {
                    return \$this->delete(\$this->table ,['id'=>\$id]);
                }
            }
            EOT;

            $generatedController = str_replace(
                ['{{className}}'],
                [$className],
                $controllerTemplate
            );
            file_put_contents('backend/Controller/'.$className.'Controller.php', $generatedController);
    }
    public function createTests($model){
        $reflection = new ReflectionClass($model);
        $className = $reflection->getShortName();
        
        $controllerTestTemplate = <<<EOT
            <?php

            use App\Controller\{{className}}Controller;
            use App\Model\{{className}};
            use PHPUnit\Framework\TestCase;
            
            class {{className}}ControllerTest extends TestCase {
                protected \$controller;
                protected \$model;
            
                protected function setUp(): void {
                    \$this->model = new {{className}}();
                    \$this->controller = new {{className}}Controller(\$this->model);
                }
            
                public function testInsert() {
                    \$reflection = new ReflectionClass(\$this->model);
                    \$properties = \$reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            
                    foreach (\$properties as \$property) {
                        \$propName = \$property->getName();
                        \$setterMethod = 'set' . ucfirst(\$propName);
                        
                        if (method_exists(\$this->model, \$setterMethod)) {
                            \$type = \$property->getType()->getName();
                            
                            switch(\$type) {
                                case 'int':
                                    \$testValue = 1;
                                    break;
                                case 'string':
                                    \$testValue = 'TestValue';
                                    break;
                                case 'DateTime':
                                    \$testValue = new \DateTime();
                                    break;
                                default:
                                    \$testValue = 'TestValue';
                            }
                            
                            \$this->model->\$setterMethod(\$testValue); 
                        }
                    }
                    
            
                    \$this->assertTrue(\$this->controller->inserir());
            
                   \$lastInsertId = \$this->controller->getLastInsertId();
                    \$this->assertIsNumeric(\$lastInsertId);
                }
            
                public function testSelectAll() {
                    \$result = \$this->controller->buscarTodos();
                    
                    \$this->assertIsArray(\$result);
                    \$this->assertNotEmpty(\$result);
                }
            
                public function testSelectById() {
                    \$id = 1;
            
                    \$result = \$this->controller->buscarId(\$id);
                    
                    \$this->assertIsArray(\$result);
                    \$this->assertNotEmpty(\$result);
                }
            
                public function testUpdate() {
                    \$id = 1;
                    \$newData = [
                        'prop1' => 'NewValue1',
                        'prop2' => 'NewValue2',
                    ];
                    \$this->assertTrue(\$this->controller->atualizarId(\$id));
                }
            
                public function testDelete() {
                    \$id = 1;
    
                    \$this->assertTrue(\$this->controller->excluir(\$id));
                }
            }
            
            EOT;
        
        $generatedControllerTest = str_replace(
            ['{{className}}'],
            [$className],
            $controllerTestTemplate
        );
        file_put_contents('backend/tests/'.$className.'ControllerTest.php', $generatedControllerTest);
        

        $modelTestTemplate = <<<EOT
            <?php
    
            use App\Model\{{className}};
            use PHPUnit\Framework\TestCase;
    
            class {{className}}Test extends TestCase {
                public function testSetAndGet() {
                    \$model = new {{className}}();
                   
                }
    
            }
            EOT;
        
        $generatedModelTest = str_replace(
            ['{{className}}'],
            [$className],
            $modelTestTemplate
        );
        file_put_contents('backend/tests/'.$className.'Test.php', $generatedModelTest);
    }
    
    
    private function createInsertProcedure($tableName, $columnNames, $placeholders) {
        $columnNamesWithoutId = array_filter($columnNames, function($colName) {
             return $colName ; 
            });
        $columnsStr = implode(', ', $columnNamesWithoutId);
        $placeholdersStr = implode(', p_', $placeholders);
        $dropProcedureSQL = "DROP PROCEDURE IF EXISTS Insert{$tableName}";
        $this->conn->exec($dropProcedureSQL);
    
        $sql = "
            CREATE PROCEDURE Insert{$tableName}(".str_replace('id INT,','',str_replace(',',', IN',$placeholdersStr)).")
            BEGIN
                INSERT INTO {$tableName} (".str_replace('id,','',$columnsStr).") VALUES (".str_replace('id INT,','',str_replace('INT','',str_replace('VARCHAR(255)','',str_replace('DATETIME','',str_replace('FLOAT','',str_replace('BOOLEAN','',$placeholdersStr)))))).");
            END;
        ";
        $this->conn->exec($sql);
    }
    
    private function createUpdateProcedure($tableName, $columns) {
        $columnsWithoutId = array_filter($columns, function($col) { return !str_starts_with($col, "id"); });
        $updateStatements = array_map(function($col) { 
            $colName = explode(' ', $col)[0];
            return "{$colName} = p_{$colName}"; 
        }, $columnsWithoutId);
        $updateStr = implode(', ', $updateStatements);
        $dropProcedureSQL = "DROP PROCEDURE IF EXISTS Update{$tableName}";
        $this->conn->exec($dropProcedureSQL);
        $params = implode(', ', array_map(function($col) {
            return "p_{$col}";
        }, $columnsWithoutId));
        $sql = "
            CREATE PROCEDURE Update{$tableName}(IN id INT, {$params})
            BEGIN
                UPDATE {$tableName} SET {$updateStr} WHERE id = id;
            END;
        ";
        $this->conn->exec($sql);
    }
    
    
    private function createDeleteProcedure($tableName) {
        $dropProcedureSQL = "DROP PROCEDURE IF EXISTS Delete{$tableName}";
        $this->conn->exec($dropProcedureSQL);
        $sql = "
            CREATE PROCEDURE Delete{$tableName}(IN idx INT)
            BEGIN
                DELETE FROM {$tableName} WHERE id = idx;
            END;
        ";
    
        $this->conn->exec($sql);
    }
    
    private function createSelectAllProcedure($tableName) {
        $dropProcedureSQL = "DROP PROCEDURE IF EXISTS SelectAll{$tableName}";
        $this->conn->exec($dropProcedureSQL);
        $sql = "
            CREATE PROCEDURE SelectAll{$tableName}()
            BEGIN
                SELECT * FROM {$tableName};
            END;
        ";
    
        $this->conn->exec($sql);
    }
    
    private function createSelectByIdProcedure($tableName) {
        $dropProcedureSQL = "DROP PROCEDURE IF EXISTS SelectById{$tableName}";
        $this->conn->exec($dropProcedureSQL);
        $sql = "
            CREATE PROCEDURE SelectById{$tableName}(IN idx INT)
            BEGIN
                SELECT * FROM {$tableName} WHERE id = idx;
            END;
        ";
    
        $this->conn->exec($sql);
    }
}
