<?php
namespace App\Database;

use App\Database\Connection;
use Exception;
use PDO;
use ReflectionProperty;
class Crud extends Connection{
    public function __construct() {
        parent::__construct();
    }
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
    public function insert($object) {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        $table=$reflectionClass->getShortName();
        $data = [];
        foreach ($properties as $property) {
            $property->setAccessible(true); 
            if ($property->getName() === 'id') { 
                continue;
            }
            $data[$property->getName()] = $property->getValue($object);
           
        }
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }
    public function select($object, $conditions = []) {
        $reflectionClass = new \ReflectionClass($object);
        $table=$reflectionClass->getShortName();
        $query = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $conditionsStr = implode(" AND ", array_map(function($item) {
            return "$item = :$item";
            }, array_keys($conditions)));
            $query .= " WHERE $conditionsStr";
        }
        $stmt = $this->conn->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function update($object, $conditions) {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        $table = $reflectionClass->getShortName();
        $data = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if ($property->getName() === 'id') { 
                continue;
            }
            $data[$property->getName()] = $property->getValue($object);
        }
        $dataStr = implode(", ", array_map(function($item) {
            return "$item = :$item";
        }, array_keys($data)));
        $conditionsStr = implode(" AND ", array_map(function($item) {
            return "$item = :condition_$item";
        }, array_keys($conditions)));
        $query = "UPDATE $table SET $dataStr WHERE $conditionsStr";
        $stmt = $this->conn->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":condition_$key", $value);
        }
        return $stmt->execute();
    }
    public function delete($object, $conditions) {
        $reflectionClass = new \ReflectionClass($object);
        $table = $reflectionClass->getShortName();
        $conditionsStr = implode(" AND ", array_map(function($item) {
            return "$item = :$item";
        }, array_keys($conditions)));
        $query = "DELETE FROM $table WHERE $conditionsStr";
        $stmt = $this->conn->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }
}
