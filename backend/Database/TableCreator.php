<?php
namespace App\Database;

use App\Database\Connection;
use Exception;
use PDOException;
use PDO;
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
