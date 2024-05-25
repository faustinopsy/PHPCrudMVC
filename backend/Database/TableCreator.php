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
    
    public function createRoute($model){
        $reflection = new ReflectionClass($model);
        $className = $reflection->getShortName();
        $lowerClassName = lcfirst($className);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
        $settersCodeForPostAndPut = "";
        foreach ($properties as $property) {
            $propName = $property->getName();
            if ($propName !== 'id') { 
                $camelCasePropName = ucfirst($propName);
                $settersCodeForPostAndPut .= "\${$lowerClassName}->set$camelCasePropName(\$body['{$propName}']);\n        ";
            }
        }
        $routeTemplate = <<<EOT
            <?php
    
            namespace App\\{$className};
            require "../../vendor/autoload.php";
    
            use App\Controller\\{$className}Controller;
            use App\Model\\{$className};
    
            \${$lowerClassName} = new {$className}();
    
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: * ' );
            header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Cache-Control: no-cache, no-store, must-revalidate');

            \$body = json_decode(file_get_contents('php://input'), true);
            \$id = isset(\$_GET['id']) ? \$_GET['id'] : '';
            switch(\$_SERVER["REQUEST_METHOD"]){
                case "POST";
                    $settersCodeForPostAndPut
                    \${$lowerClassName}Controller = new {$className}Controller(\${$lowerClassName});
                    \$resultado = \${$lowerClassName}Controller->inserir();
                    echo json_encode(['status' => \$resultado]);
                break;
                case "GET";
                    \${$lowerClassName}Controller = new {$className}Controller(\${$lowerClassName});
                    if(!isset(\$_GET['id'])){
                        \$resultado = \${$lowerClassName}Controller->buscarTodos();
                        if(!\$resultado){
                            echo json_encode(["status" => false, "{$className}" => \$resultado,"mensagem"=>"nenhum resultado encontrado"]);
                            exit;
                        }else{
                            echo json_encode(["status" => true, "{$className}" => \$resultado]);
                            exit;
                        }
                    }else{
                        \$resultado = \${$lowerClassName}Controller->buscarId(\$id);
                        if(!\$resultado){
                            echo json_encode(["status" => false, "{$className}" => \$resultado,"mensagem"=>"nenhum resultado encontrado"]);
                            exit;
                        }else{
                            echo json_encode(["status" => true, "{$className}" => \$resultado[0]]);
                            exit;
                        }
                    }
                break;
                case "PUT";
                    $settersCodeForPostAndPut
                    \${$lowerClassName}Controller = new {$className}Controller(\${$lowerClassName});
                    \$resultado = \${$lowerClassName}Controller->atualizarId(intval(\$_GET['id']));
                    echo json_encode(['status' => \$resultado]);
                break;
                case "DELETE";
                    \${$lowerClassName}Controller = new {$className}Controller(\${$lowerClassName});
                    \$resultado = \${$lowerClassName}Controller->excluir(intval(\$_GET['id']));
                    echo json_encode(['status' => \$resultado]);
                break;  
            }
            EOT;
    
        file_put_contents('backend/Routes/'.$className.'Route.php', $routeTemplate);
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
    public function createJsClasses($className, $properties) {
        $jsDir = "js";
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    
        $jsCreateTemplate = "class Create{$className} {\n";
            $jsCreateTemplate .= "    constructor() {\n";
            foreach ($properties as $property) {
                $jsCreateTemplate .= "        this.{$property['name']} = null;\n";
            }
            $jsCreateTemplate .= "    }\n\n";
            $jsCreateTemplate .= "    async create() {\n";
            $jsCreateTemplate .= "      const {$className} = {};\n";
            $jsCreateTemplate .= "      const form = document.forms['$className-form'];\n";
            $jsCreateTemplate .= "      for (let i = 0; i < form.elements.length; i++) {\n";
            $jsCreateTemplate .= "          const element = form.elements[i];\n";
            $jsCreateTemplate .= "          if (element.name) {\n";
            $jsCreateTemplate .= "              {$className}[element.name] = element.value;\n";
            $jsCreateTemplate .= "          }\n";
            $jsCreateTemplate .= "      }\n";
            $jsCreateTemplate .= "      try {\n";
            $jsCreateTemplate .= "          const response = await  fetch('/backend/Routes/{$className}Route.php', { \n";
            $jsCreateTemplate .= "          method: 'POST',\n";
            $jsCreateTemplate .= "          headers: {\n";
            $jsCreateTemplate .= "              'Content-Type': 'application/json'\n";
            $jsCreateTemplate .= "          },\n";
            $jsCreateTemplate .= "          body: JSON.stringify({$className})\n";
            $jsCreateTemplate .= "      });\n";
            $jsCreateTemplate .= "      if (!response.ok) {\n";
            $jsCreateTemplate .= "          switch(response.status) {\n";
            $jsCreateTemplate .= "              case 403:\n";
            $jsCreateTemplate .= "                  throw new Error('Acesso proibido.');\n";
            $jsCreateTemplate .= "              case 404:\n";
            $jsCreateTemplate .= "                  throw new Error('Recurso não encontrado.');\n";
            $jsCreateTemplate .= "              default:\n";
            $jsCreateTemplate .= "                  throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');\n";
            $jsCreateTemplate .= "          }\n";
            $jsCreateTemplate .= "       }\n";
            $jsCreateTemplate .= "          const data = await response.json();\n";
            $jsCreateTemplate .= "              Swal.fire(\n";
            $jsCreateTemplate .= "                  '{$className} criado com sucesso',\n";
            $jsCreateTemplate .= "                  '',\n";
            $jsCreateTemplate .= "              'success'\n";
            $jsCreateTemplate .= "               )\n";
            $jsCreateTemplate .= "      for (let i = 0; i < form.elements.length; i++) {\n";
            $jsCreateTemplate .= "          const element = form.elements[i];\n";
            $jsCreateTemplate .= "          if (element.name) {\n";
            $jsCreateTemplate .= "              element.value='';\n";
            $jsCreateTemplate .= "          }\n";
            $jsCreateTemplate .= "      }\n"; 
            $jsCreateTemplate .= "      } catch (error) {\n"; 
            $jsCreateTemplate .= "              Swal.fire(\n";
            $jsCreateTemplate .= "              error.message,\n";
            $jsCreateTemplate .= "              '',\n";
            $jsCreateTemplate .= "              'info'\n";
            $jsCreateTemplate .= "              )\n";
            $jsCreateTemplate .= "           }\n";
            $jsCreateTemplate .= "        }\n";
            $jsCreateTemplate .= "  }\n\n";
            $jsCreateTemplate .= "      document.forms['$className-form'].addEventListener('submit', function(e) {\n";
            $jsCreateTemplate .= "           e.preventDefault();\n";
            $jsCreateTemplate .= "          const $className = new Create{$className}();\n";
            $jsCreateTemplate .= "          $className.create();\n";
            $jsCreateTemplate .= "  });\n";
            file_put_contents("$jsDir/Create{$className}.js", $jsCreateTemplate);
            
    
        $jsFetchTemplate = "class Busca{$className}s {\n";
        $jsFetchTemplate .= "    async getAll() {\n";
        $jsFetchTemplate .= "           try {\n";
        $jsFetchTemplate .= "                   const response = await fetch('/backend/Routes/{$className}Route.php', {\n";
        $jsFetchTemplate .= "                   method: 'GET',\n";
        $jsFetchTemplate .= "                   headers: {\n";
        $jsFetchTemplate .= "                       'Content-Type': 'application/json'\n";
        $jsFetchTemplate .= "                   },\n";
        $jsFetchTemplate .= "               });\n";
        $jsFetchTemplate .= "           if (!response.ok) {\n";
        $jsFetchTemplate .= "                   switch(response.status) {\n";
        $jsFetchTemplate .= "                       case 503:\n";
        $jsFetchTemplate .= "                           throw new Error('Serviço indisponível.');\n";
        $jsFetchTemplate .= "                       default:\n";
        $jsFetchTemplate .= "                           throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');\n";
        $jsFetchTemplate .= "                       }   \n";
        $jsFetchTemplate .= "                   }\n";
        $jsFetchTemplate .= "               const data = await response.json();\n";
        $jsFetchTemplate .= "               if(data.status){\n";
        $jsFetchTemplate .= "                   this.display{$className}(data);\n";
        $jsFetchTemplate .= "               }else{\n";
        $jsFetchTemplate .= "                   Swal.fire(\n";
        $jsFetchTemplate .= "                       'nenhum dado retornado',\n";
        $jsFetchTemplate .= "                       '',\n";
        $jsFetchTemplate .= "                       'info'\n";
        $jsFetchTemplate .= "                   )\n";
        $jsFetchTemplate .= "               }\n";
        $jsFetchTemplate .= "             } catch (error) {\n";
        $jsFetchTemplate .= "                   alert('Erro na requisição: ' + error.mensagem);\n";
        $jsFetchTemplate .= "           }\n";
        $jsFetchTemplate .= "    }\n\n";
        $jsFetchTemplate .= "    display{$className}(data) {\n";
        $jsFetchTemplate .= "        const {$className} = data.{$className};\n";
        $jsFetchTemplate .= "        const {$className}Div = document.getElementById('Lista');\n";
        $jsFetchTemplate .= "        {$className}Div.innerHTML = '';\n";
        $jsFetchTemplate .= "        const list = document.createElement('ul');\n";
        $jsFetchTemplate .= "        {$className}.forEach(item => {\n";
        $jsFetchTemplate .= "            const listItem = document.createElement('li');\n";
        $jsFetchTemplate .= "            listItem.textContent = this.objectToString(item);\n";
        $jsFetchTemplate .= "            list.appendChild(listItem);\n";
        $jsFetchTemplate .= "        });\n";
        $jsFetchTemplate .= "        {$className}Div.appendChild(list);\n";
        $jsFetchTemplate .= "    }\n\n";
        $jsFetchTemplate .= "    objectToString(obj) {\n";
        $jsFetchTemplate .= "        return Object.keys(obj).map(function(key) {\n";
        $jsFetchTemplate .= "            return key + ': ' + obj[key];\n";
        $jsFetchTemplate .= "        }).join(' - ');\n";
        $jsFetchTemplate .= "    }\n";
 
        $jsFetchTemplate .= "}\n\n";
        $jsFetchTemplate .= "    const buscar = new Busca{$className}s();\n";
        $jsFetchTemplate .= "    buscar.getAll();\n";
        
        file_put_contents("$jsDir/Busca{$className}s.js", $jsFetchTemplate);

        $jsUpdateTemplate = "class Gerenciar{$className} {\n";
        $jsUpdateTemplate .= "    constructor() {\n";
        foreach ($properties as $property) {
            $jsUpdateTemplate .= "        this.{$property['name']} = null;\n";
        }
        $jsUpdateTemplate .= "    }\n\n";
        $jsUpdateTemplate .= "async search(id) {\n";
        $jsUpdateTemplate .= "\n";
        $jsUpdateTemplate .= "    try {\n";
        $jsUpdateTemplate .= "        const response = await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`, { \n";
        $jsUpdateTemplate .= "       method: 'GET',\n";
        $jsUpdateTemplate .= "    headers: {\n";
        $jsUpdateTemplate .= "            'Content-Type': 'application/json'\n";
        $jsUpdateTemplate .= "    },\n";
        $jsUpdateTemplate .= "});\n";
        $jsUpdateTemplate .= "   if (!response.ok) {\n";
        $jsUpdateTemplate .= "       throw new Error('Erro na atualização.');\n";
        $jsUpdateTemplate .= "   }\n";
        $jsUpdateTemplate .= "const data = await response.json();\n";
        $jsUpdateTemplate .= "if(data.status){\n";
        foreach ($properties as $property) {
            if ($property['name'] === 'id') {
                continue; 
            }
            $jsUpdateTemplate .= "  document.getElementById('{$property['name']}').value =data.{$className}.{$property['name']};\n";
        }
        $jsUpdateTemplate .= "  }else{\n";
        $jsUpdateTemplate .= "       Swal.fire(\n";
        $jsUpdateTemplate .= "        data.mensagem,\n";
        $jsUpdateTemplate .= "      '',\n";
        $jsUpdateTemplate .= "    'info'\n";
        $jsUpdateTemplate .= "  )\n";
        $jsUpdateTemplate .= " }\n";
        $jsUpdateTemplate .= " } catch (error) {\n";
        $jsUpdateTemplate .= "Swal.fire(\n";
        $jsUpdateTemplate .= "    error.message,\n";
        $jsUpdateTemplate .= "    '',\n";
        $jsUpdateTemplate .= "   'error'\n";
        $jsUpdateTemplate .= " );\n";
        $jsUpdateTemplate .= " }\n";
        $jsUpdateTemplate .= " }\n";
        $jsUpdateTemplate .= "    async update(id) {\n";
        $jsUpdateTemplate .= "      const {$className} = {};\n";
        $jsUpdateTemplate .= "      const form = document.forms['$className-form'];\n";
        $jsUpdateTemplate .= "      for (let i = 0; i < form.elements.length; i++) {\n";
        $jsUpdateTemplate .= "          const element = form.elements[i];\n";
        $jsUpdateTemplate .= "          if (element.name) {\n";
        $jsUpdateTemplate .= "              {$className}[element.name] = element.value;\n";
        $jsUpdateTemplate .= "          }\n";
        $jsUpdateTemplate .= "      }\n";
        $jsUpdateTemplate .= "      try {\n";
        $jsUpdateTemplate .= "          const response = await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`, { \n";
        $jsUpdateTemplate .= "          method: 'PUT',\n";
        $jsUpdateTemplate .= "          headers: {\n";
        $jsUpdateTemplate .= "              'Content-Type': 'application/json'\n";
        $jsUpdateTemplate .= "          },\n";
        $jsUpdateTemplate .= "          body: JSON.stringify({$className})\n";
        $jsUpdateTemplate .= "      });\n";
        $jsUpdateTemplate .= "      if (!response.ok) {\n";
        $jsUpdateTemplate .= "          throw new Error('Erro na atualização.');\n";
        $jsUpdateTemplate .= "      }\n";
        $jsUpdateTemplate .= "      Swal.fire(\n";
        $jsUpdateTemplate .= "          '{$className} atualizado com sucesso',\n";
        $jsUpdateTemplate .= "          '',\n";
        $jsUpdateTemplate .= "          'success'\n";
        $jsUpdateTemplate .= "      );\n";
        $jsUpdateTemplate .= "      } catch (error) {\n";
        $jsUpdateTemplate .= "          Swal.fire(\n";
        $jsUpdateTemplate .= "              error.message,\n";
        $jsUpdateTemplate .= "              '',\n";
        $jsUpdateTemplate .= "              'error'\n";
        $jsUpdateTemplate .= "          );\n";
        $jsUpdateTemplate .= "      }\n";
        $jsUpdateTemplate .= "    }\n";
        $jsUpdateTemplate .= "    async delete(id) {\n";
        $jsUpdateTemplate .= "      try {\n";
        $jsUpdateTemplate .= "          const response = await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`, { \n";
        $jsUpdateTemplate .= "          method: 'DELETE',\n";
        $jsUpdateTemplate .= "          headers: {\n";
        $jsUpdateTemplate .= "              'Content-Type': 'application/json'\n";
        $jsUpdateTemplate .= "          }\n";
        $jsUpdateTemplate .= "      });\n";
        $jsUpdateTemplate .= "      if (!response.ok) {\n";
        $jsUpdateTemplate .= "          throw new Error('Erro na exclusão.');\n";
        $jsUpdateTemplate .= "      }\n";
        $jsUpdateTemplate .= "      Swal.fire(\n";
        $jsUpdateTemplate .= "          '{$className} excluído com sucesso',\n";
        $jsUpdateTemplate .= "          '',\n";
        $jsUpdateTemplate .= "          'success'\n";
        $jsUpdateTemplate .= "      );\n";
        $jsUpdateTemplate .= " const form = document.forms['$className-form'];\n";
        $jsUpdateTemplate .= "      for (let i = 0; i < form.elements.length; i++) {\n";
        $jsUpdateTemplate .= "          const element = form.elements[i];\n";
        $jsUpdateTemplate .= "          if (element.name) {\n";
        $jsUpdateTemplate .= "              element.value='';\n";
        $jsUpdateTemplate .= "          }\n";
        $jsUpdateTemplate .= "      }\n";
        $jsUpdateTemplate .= "      } catch (error) {\n";
        $jsUpdateTemplate .= "          Swal.fire(\n";
        $jsUpdateTemplate .= "              error.message,\n";
        $jsUpdateTemplate .= "              '',\n";
        $jsUpdateTemplate .= "              'error'\n";
        $jsUpdateTemplate .= "          );\n";
        $jsUpdateTemplate .= "      }\n";
        $jsUpdateTemplate .= "    }\n";

        $jsUpdateTemplate .= "}\n\n";
        $jsUpdateTemplate .= "document.getElementById('search-button').addEventListener('click', function(e) {\n";
        $jsUpdateTemplate .= "    e.preventDefault();\n";
        $jsUpdateTemplate .= "    const id = document.getElementById('id$className').value;\n";
        $jsUpdateTemplate .= "    const {$className} = new Gerenciar{$className}();\n";
        $jsUpdateTemplate .= "    {$className}.search(id);\n";
        $jsUpdateTemplate .= "});\n";
        $jsUpdateTemplate .= "document.getElementById('update-button').addEventListener('click', function(e) {\n";
        $jsUpdateTemplate .= "    e.preventDefault();\n";
        $jsUpdateTemplate .= "    const id = document.getElementById('id$className').value;\n";
        $jsUpdateTemplate .= "    const {$className} = new Gerenciar{$className}();\n";
        $jsUpdateTemplate .= "    {$className}.update(id);\n";
        $jsUpdateTemplate .= "});\n";
        $jsUpdateTemplate .= "document.getElementById('delete-button').addEventListener('click', function(e) {\n";
        $jsUpdateTemplate .= "    e.preventDefault();\n";
        $jsUpdateTemplate .= "    const id = document.getElementById('id$className').value;\n";
        $jsUpdateTemplate .= "    const {$className} = new Gerenciar{$className}();\n";
        $jsUpdateTemplate .= "    {$className}.delete(id);\n";
        $jsUpdateTemplate .= "});\n";
    
        file_put_contents("$jsDir/Gerenciar{$className}.js", $jsUpdateTemplate);

        
    }
   
    public function createHtmlForm($className, $properties) {
        $htmlTemplate = "<!DOCTYPE html>\n";
        $htmlTemplate .= "<html lang=\"en\">\n";
        $htmlTemplate .= "<head>\n";
        $htmlTemplate .= "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n";
        $htmlTemplate .= "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"/>\n";
        $htmlTemplate .= "  <title>Starter Template - Materialize</title>\n";
        $htmlTemplate .= "  <!-- CSS  -->\n";
        $htmlTemplate .= "  <link href=\"css/materialize.css\" type=\"text/css\" rel=\"stylesheet\" media=\"screen,projection\"/>\n";
        $htmlTemplate .= "<link rel=\"stylesheet\" href=\"css/sweetalert2.min.css\">\n";
        $htmlTemplate .= " </head>\n";
        $htmlTemplate .= "<body>\n";
        $htmlTemplate .= "  <nav class=\"light-green lighten-1\" role=\"navigation\" >\n";
        $htmlTemplate .= "    <div class=\"nav-wrapper container\"><a id=\"logo-container\" href=\"#\" class=\"brand-logo\">Logo</a>\n";
        $htmlTemplate .= "      <ul class=\"right hide-on-med-and-down\">\n";
        $htmlTemplate .= "        <li><a href=\"cria$className.html\">Cadastrar</a></li>\n";
        $htmlTemplate .= "        <li><a href=\"todos$className.html\">Listar todos</a></li>\n";
        $htmlTemplate .= "        <li><a href=\"gerenciar$className.html\">Buscar</a></li>\n";
        $htmlTemplate .= "     </ul>\n";
        $htmlTemplate .= "\n";
        $htmlTemplate .= "      <ul id=\"nav-mobile\" class=\"sidenav\">\n";
        $htmlTemplate .= "        <li><a href=\"cria$className.html\">Cadastrar</a></li>\n";
        $htmlTemplate .= "       <li><a href=\"todos$className.html\">Listar todos</a></li>\n";
        $htmlTemplate .= "       <li><a href=\"gerenciar$className.html\">Buscar</a></li>\n";
        $htmlTemplate .= "      </ul>\n";
        $htmlTemplate .= "      <a href=\"#\" data-target=\"nav-mobile\" class=\"sidenav-trigger\"><i class=\"material-icons\">menu</i></a>\n";
        $htmlTemplate .= "    </div>\n";
        $htmlTemplate .= "  </nav>\n";
        $htmlTemplate .= "  <div class=\"section no-pad-bot\" id=\"index-banner\">\n";
        $htmlTemplate .= "    <div class=\"container\">\n";
        $htmlTemplate .= "<div class=\"card\">\n";
        $htmlTemplate .= "<a href=\"../\" class=\"waves-effect waves-light btn\">Voltar</a>\n";
        $htmlTemplate .= "    <form id=\"$className-form\">\n";
        foreach ($properties as $property) {
            if ($property['name'] === 'id') {
                continue; 
            }
            $htmlTemplate .= "            <div class=\"input-field col s6\">\n";
            $htmlTemplate .= "              <input type=\"text\" id=\"{$property['name']}\" name=\"{$property['name']}\"><br>\n";
            $htmlTemplate .= "              <label for=\"{$property['name']}\">{$property['name']}:</label>\n";
            $htmlTemplate .= "          </div>\n";
        }
        $htmlTemplate .= "        <input type=\"submit\" value=\"Submit\" class=\"waves-effect waves-light btn\">\n";
        $htmlTemplate .= "    </form>\n";
        $htmlTemplate .= "</div>\n";
        $htmlTemplate .= "</div>\n";
        $htmlTemplate .= "  <footer class=\"page-footer light-green\">\n";
        $htmlTemplate .= "    <div class=\"container\">\n";
        $htmlTemplate .= "      <div class=\"row\">\n";
        $htmlTemplate .= "        <div class=\"col l6 s12\">\n";
        $htmlTemplate .= "          <h5 class=\"white-text\">Company Bio</h5>\n";
        $htmlTemplate .= "          <p class=\"grey-text text-lighten-4\"></p>\n";
        $htmlTemplate .= "        </div>\n";
        $htmlTemplate .= "        <div class=\"col l3 s12\">\n";
        $htmlTemplate .= "          <h5 class=\"white-text\">Settings</h5>\n";
        $htmlTemplate .= "          <ul>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 1</a></li>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 2</a></li>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 3</a></li>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 4</a></li>\n";
        $htmlTemplate .= "          </ul>\n";
        $htmlTemplate .= "        </div>\n";
        $htmlTemplate .= "        <div class=\"col l3 s12\">\n";
        $htmlTemplate .= "          <h5 class=\"white-text\">Connect</h5>\n";
        $htmlTemplate .= "          <ul>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 1</a></li>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 2</a></li>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 3</a></li>\n";
        $htmlTemplate .= "            <li><a class=\"white-text\" href=\"#!\">Link 4</a></li>\n";
        $htmlTemplate .= "         </ul>\n";
        $htmlTemplate .= "        </div>\n";
        $htmlTemplate .= "      </div>\n";
        $htmlTemplate .= "   </div>\n";
        $htmlTemplate .= "    <div class=\"footer-copyright\">\n";
        $htmlTemplate .= "    </div>\n";
        $htmlTemplate .= "  </footer>\n";
        $htmlTemplate .= "  <!--  Scripts-->\n";
        $htmlTemplate .= "  <script src=\"js/jquery-2.1.1.min.js\"></script>\n";
        $htmlTemplate .= "  <script src=\"js/materialize.js\"></script>\n";
        $htmlTemplate .= "  <script src=\"js/init.js\"></script>\n";
        $htmlTemplate .= "    <script src=\"js/materialize.min.js\"></script>\n";
        $htmlTemplate .= "    <script src=\"js/sweetalert2.all.min.js\"></script>\n";
        $htmlTemplate .= "    <script src=\"js/Create$className.js\"></script>\n";
        $htmlTemplate .= "  </body>\n";
        $htmlTemplate .= "</html>\n";
    
        file_put_contents("cria$className.html", $htmlTemplate);

        $htmlTemplate2 = "<!DOCTYPE html>\n";
        $htmlTemplate2 .= "<html lang=\"en\">\n";
        $htmlTemplate2 .= "<head>\n";
        $htmlTemplate2 .= "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n";
        $htmlTemplate2 .= "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"/>\n";
        $htmlTemplate2 .= "  <title>Starter Template - Materialize</title>\n";
        $htmlTemplate2 .= "  <!-- CSS  -->\n";
        $htmlTemplate2 .= "  <link href=\"css/materialize.css\" type=\"text/css\" rel=\"stylesheet\" media=\"screen,projection\"/>\n";
        $htmlTemplate2 .= "<link rel=\"stylesheet\" href=\"css/sweetalert2.min.css\">\n";
        $htmlTemplate2 .= " </head>\n";
        $htmlTemplate2 .= "<body>\n";
        $htmlTemplate2 .= "  <nav class=\"light-green lighten-1\" role=\"navigation\" >\n";
        $htmlTemplate2 .= "    <div class=\"nav-wrapper container\"><a id=\"logo-container\" href=\"#\" class=\"brand-logo\">Logo</a>\n";
        $htmlTemplate2 .= "      <ul class=\"right hide-on-med-and-down\">\n";
        $htmlTemplate2 .= "        <li><a href=\"cria$className.html\">Cadastrar</a></li>\n";
        $htmlTemplate2 .= "        <li><a href=\"todos$className.html\">Listar todos</a></li>\n";
        $htmlTemplate2 .= "        <li><a href=\"gerenciar$className.html\">Buscar</a></li>\n";
        $htmlTemplate2 .= "     </ul>\n";
        $htmlTemplate2 .= "\n";
        $htmlTemplate2 .= "      <ul id=\"nav-mobile\" class=\"sidenav\">\n";
        $htmlTemplate2 .= "        <li><a href=\"cria$className.html\">Cadastrar</a></li>\n";
        $htmlTemplate2 .= "       <li><a href=\"todos$className.html\">Listar todos</a></li>\n";
        $htmlTemplate2 .= "       <li><a href=\"gerenciar$className.html\">Buscar</a></li>\n";
        $htmlTemplate2 .= "      </ul>\n";
        $htmlTemplate2 .= "      <a href=\"#\" data-target=\"nav-mobile\" class=\"sidenav-trigger\"><i class=\"material-icons\">menu</i></a>\n";
        $htmlTemplate2 .= "    </div>\n";
        $htmlTemplate2 .= "  </nav>\n";
        $htmlTemplate2 .= "  <div class=\"section no-pad-bot\" id=\"index-banner\">\n";
        $htmlTemplate2 .= "    <div class=\"container\">\n";
        $htmlTemplate2 .= "<div class=\"card\">\n";
        $htmlTemplate2 .= "<a href=\"../\" class=\"waves-effect waves-light btn\">Voltar</a>\n";
        $htmlTemplate2 .= "<div id=\"Lista\"></div>\n";
        $htmlTemplate2 .= "</div>\n";
        
        $htmlTemplate2 .= "</div>\n";
        $htmlTemplate2 .= "  <footer class=\"page-footer light-green\">\n";
        $htmlTemplate2 .= "    <div class=\"container\">\n";
        $htmlTemplate2 .= "      <div class=\"row\">\n";
        $htmlTemplate2 .= "        <div class=\"col l6 s12\">\n";
        $htmlTemplate2 .= "          <h5 class=\"white-text\">Company Bio</h5>\n";
        $htmlTemplate2 .= "          <p class=\"grey-text text-lighten-4\"></p>\n";
        $htmlTemplate2 .= "        </div>\n";
        $htmlTemplate2 .= "        <div class=\"col l3 s12\">\n";
        $htmlTemplate2 .= "          <h5 class=\"white-text\">Settings</h5>\n";
        $htmlTemplate2 .= "          <ul>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 1</a></li>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 2</a></li>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 3</a></li>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 4</a></li>\n";
        $htmlTemplate2 .= "          </ul>\n";
        $htmlTemplate2 .= "        </div>\n";
        $htmlTemplate2 .= "        <div class=\"col l3 s12\">\n";
        $htmlTemplate2 .= "          <h5 class=\"white-text\">Connect</h5>\n";
        $htmlTemplate2 .= "          <ul>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 1</a></li>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 2</a></li>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 3</a></li>\n";
        $htmlTemplate2 .= "            <li><a class=\"white-text\" href=\"#!\">Link 4</a></li>\n";
        $htmlTemplate2 .= "         </ul>\n";
        $htmlTemplate2 .= "        </div>\n";
        $htmlTemplate2 .= "      </div>\n";
        $htmlTemplate2 .= "   </div>\n";
        $htmlTemplate2 .= "    <div class=\"footer-copyright\">\n";
        $htmlTemplate2 .= "    </div>\n";
        $htmlTemplate2 .= "  </footer>\n";
        $htmlTemplate2 .= "  <!--  Scripts-->\n";
        $htmlTemplate2 .= "    <script src=\"js/jquery-2.1.1.min.js\"></script>\n";
        $htmlTemplate2 .= "    <script src=\"js/materialize.min.js\"></script>\n";
        $htmlTemplate2 .= "    <script src=\"js/init.js\"></script>\n";
        $htmlTemplate2 .= "    <script src=\"js/sweetalert2.all.min.js\"></script>\n";
        $htmlTemplate2 .= "    <script src=\"js/Busca{$className}s.js\"></script> \n";
        $htmlTemplate2 .= "  </body>\n";
        $htmlTemplate2 .= "</html>\n";
    
        file_put_contents("todos$className.html", $htmlTemplate2);

        $htmlTemplate3 = "<!DOCTYPE html>\n";
        $htmlTemplate3 .= "<html lang=\"en\">\n";
        $htmlTemplate3 .= "<head>\n";
        $htmlTemplate3 .= "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n";
        $htmlTemplate3 .= "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"/>\n";
        $htmlTemplate3 .= "  <title>Starter Template - Materialize</title>\n";
        $htmlTemplate3 .= "  <!-- CSS  -->\n";
        $htmlTemplate3 .= "  <link href=\"css/materialize.css\" type=\"text/css\" rel=\"stylesheet\" media=\"screen,projection\"/>\n";
        $htmlTemplate3 .= "<link rel=\"stylesheet\" href=\"css/sweetalert2.min.css\">\n";
        $htmlTemplate3 .= " </head>\n";
        $htmlTemplate3 .= "<body>\n";
        $htmlTemplate3 .= "  <nav class=\"light-green lighten-1\" role=\"navigation\" >\n";
        $htmlTemplate3 .= "    <div class=\"nav-wrapper container\"><a id=\"logo-container\" href=\"#\" class=\"brand-logo\">Logo</a>\n";
        $htmlTemplate3 .= "      <ul class=\"right hide-on-med-and-down\">\n";
        $htmlTemplate3 .= "        <li><a href=\"cria$className.html\">Cadastrar</a></li>\n";
        $htmlTemplate3 .= "        <li><a href=\"todos$className.html\">Listar todos</a></li>\n";
        $htmlTemplate3 .= "        <li><a href=\"gerenciar$className.html\">Buscar</a></li>\n";
        $htmlTemplate3 .= "     </ul>\n";
        $htmlTemplate3 .= "\n";
        $htmlTemplate3 .= "      <ul id=\"nav-mobile\" class=\"sidenav\">\n";
        $htmlTemplate3 .= "        <li><a href=\"cria$className.html\">Cadastrar</a></li>\n";
        $htmlTemplate3 .= "       <li><a href=\"todos$className.html\">Listar todos</a></li>\n";
        $htmlTemplate3 .= "       <li><a href=\"gerenciar$className.html\">Buscar</a></li>\n";
        $htmlTemplate3 .= "      </ul>\n";
        $htmlTemplate3 .= "      <a href=\"#\" data-target=\"nav-mobile\" class=\"sidenav-trigger\"><i class=\"material-icons\">menu</i></a>\n";
        $htmlTemplate3 .= "    </div>\n";
        $htmlTemplate3 .= "  </nav>\n";
        $htmlTemplate3 .= "  <div class=\"section no-pad-bot\" id=\"index-banner\">\n";
        $htmlTemplate3 .= "    <div class=\"container\">\n";
        $htmlTemplate3 .= "    <div class=\"card\">\n";
        $htmlTemplate3 .= "        <form id=\"$className-form\">\n";
        $htmlTemplate3 .= "            <a href=\"../\" class=\"waves-effect waves-light btn\">Voltar</a>\n";
        $htmlTemplate3 .= "            <h3>Buscar $className</h3>\n";
        $htmlTemplate3 .= "            <div class=\"input-field col s6\">\n";
        $htmlTemplate3 .= "            <input type=\"number\" id=\"id$className\" class=\"validate\">\n";
        $htmlTemplate3 .= "            <label for=\"id-to\">ID do $className:</label>\n";
        $htmlTemplate3 .= "            </div>\n";
        $htmlTemplate3 .= "            <button id=\"search-button\" class=\"waves-effect waves-light btn\">Buscar</button><br>\n";
        $htmlTemplate3 .= "            <h3>Gerenciar $className</h3>\n";
    
        foreach ($properties as $property) {
            if ($property['name'] === 'id') {
                continue; 
            }
            $htmlTemplate3 .= "            <div class=\"input-field col s6\">\n";
            $htmlTemplate3 .= "            <input type=\"text\" id=\"{$property['name']}\" name=\"{$property['name']}\" required><br>\n";
            $htmlTemplate3 .= "            <label for=\"{$property['name']}\">{$property['name']}:</label>\n";
            $htmlTemplate3 .= "            </div>\n";
        }
    
        $htmlTemplate3 .= "            <button id=\"update-button\" class=\"waves-effect waves-light btn\">Atualizar</button>\n";
        $htmlTemplate3 .= "            <button id=\"delete-button\" class=\"waves-effect waves-light btn\">Excluir</button>\n";
        $htmlTemplate3 .= "        </form>\n";
        $htmlTemplate3 .= "    </div>\n";
        $htmlTemplate3 .= "</div>\n";
        $htmlTemplate3 .= "  <footer class=\"page-footer light-green\">\n";
        $htmlTemplate3 .= "    <div class=\"container\">\n";
        $htmlTemplate3 .= "      <div class=\"row\">\n";
        $htmlTemplate3 .= "        <div class=\"col l6 s12\">\n";
        $htmlTemplate3 .= "          <h5 class=\"white-text\">Company Bio</h5>\n";
        $htmlTemplate3 .= "          <p class=\"grey-text text-lighten-4\"></p>\n";
        $htmlTemplate3 .= "        </div>\n";
        $htmlTemplate3 .= "        <div class=\"col l3 s12\">\n";
        $htmlTemplate3 .= "          <h5 class=\"white-text\">Settings</h5>\n";
        $htmlTemplate3 .= "          <ul>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 1</a></li>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 2</a></li>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 3</a></li>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 4</a></li>\n";
        $htmlTemplate3 .= "          </ul>\n";
        $htmlTemplate3 .= "        </div>\n";
        $htmlTemplate3 .= "        <div class=\"col l3 s12\">\n";
        $htmlTemplate3 .= "          <h5 class=\"white-text\">Connect</h5>\n";
        $htmlTemplate3 .= "          <ul>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 1</a></li>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 2</a></li>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 3</a></li>\n";
        $htmlTemplate3 .= "            <li><a class=\"white-text\" href=\"#!\">Link 4</a></li>\n";
        $htmlTemplate3 .= "         </ul>\n";
        $htmlTemplate3 .= "        </div>\n";
        $htmlTemplate3 .= "      </div>\n";
        $htmlTemplate3 .= "   </div>\n";
        $htmlTemplate3 .= "    <div class=\"footer-copyright\">\n";
        $htmlTemplate3 .= "    </div>\n";
        $htmlTemplate3 .= "  </footer>\n";
        $htmlTemplate3 .= "  <!--  Scripts-->\n";
        $htmlTemplate3 .= "  <script src=\"js/jquery-2.1.1.min.js\"></script>\n";
        $htmlTemplate3 .= "    <script src=\"js/materialize.min.js\"></script>\n";
        $htmlTemplate3 .= "  <script src=\"js/init.js\"></script>\n";
        $htmlTemplate3 .= "    <script src=\"js/sweetalert2.all.min.js\"></script>\n";
        $htmlTemplate3 .= "    <script src=\"js/Gerenciar$className.js\"></script>\n";
        $htmlTemplate3 .= "  </body>\n";
        $htmlTemplate3 .= "</html>\n";
    
        file_put_contents("gerenciar$className.html", $htmlTemplate3);
    } 
    public function createHtmlTemplate($className) {
        $htmlTemplate = <<<EOT
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
          <meta name="viewport" content="width=device-width, initial-scale=1"/>
          <title>Starter Template - Materialize</title>
          <!-- CSS  -->
          <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
          <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
        </head>
        <body>
          <nav class="light-green lighten-1" role="navigation" >
            <div class="nav-wrapper container"><a id="logo-container" href="#" class="brand-logo">Logo</a>
              <ul class="right hide-on-med-and-down">
                <li><a href="cria{{className}}.html">Cadastrar</a></li>
                <li><a href="todos{{className}}.html">Listar todos</a></li>
                <li><a href="gerenciar{{className}}.html">Buscar</a></li>
              </ul>
        
              <ul id="nav-mobile" class="sidenav">
                <li><a href="cria{{className}}.html">Cadastrar</a></li>
                <li><a href="todos{{className}}.html">Listar todos</a></li>
                <li><a href="gerenciar{{className}}.html">Buscar</a></li>
              </ul>
              <a href="#" data-target="nav-mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
            </div>
          </nav>
          <div class="section no-pad-bot" id="index-banner">
            <div class="container">
              <br><br>
              <h4 class="header center orange-text">PHPCRUD-MVC-OOP-REST</h4>
              <div class="row center">
                <h5 class="header col s12 light">A modern responsive front-end framework based on Material Design</h5>
              </div>
              <div class="row center">
               <h5 class="header col s12 light">fully customizable now </h5>
                <div class="container">
              Made by <a class="orange-text text-lighten-3" href="https://www.linkedin.com/in/faustinopsy/">Faustinopsy</a>
              </div>
              </div>
              <br><br>
            </div>
          </div>
          <div class="container">
            <div class="section">
              <!--   Icon Section   -->
              <div class="row">
                <div class="col s12 m4">
                  <div class="icon-block">
                    <h2 class="center light-blue-text"><i class="material-icons">flash_on</i></h2>
                    <h5 class="center">Speeds up development</h5>
        
                    <p class="light">We did most of the heavy lifting for you to provide a default stylings that incorporate our custom components. Additionally, we refined animations and transitions to provide a smoother experience for developers.</p>
                  </div>
                </div>
                <div class="col s12 m4">
                  <div class="icon-block">
                    <h2 class="center light-blue-text"><i class="material-icons">group</i></h2>
                    <h5 class="center">User Experience Focused</h5>
                    <p class="light">By utilizing elements and principles of Material Design, we were able to create a framework that incorporates components and animations that provide more feedback to users. Additionally, a single underlying responsive system across all platforms allow for a more unified user experience.</p>
                  </div>
                </div>
                <div class="col s12 m4">
                  <div class="icon-block">
                    <h2 class="center light-blue-text"><i class="material-icons">settings</i></h2>
                    <h5 class="center">Easy to work with</h5>
                    <p class="light">We have provided detailed documentation as well as specific code examples to help new users get started. We are also always open to feedback and can answer any questions a user may have about Materialize.</p>
                  </div>
                </div>
              </div>
            </div>
            <br><br>
          </div>
          <footer class="page-footer light-green">
            <div class="container">
              <div class="row">
                <div class="col l6 s12">
                  <h5 class="white-text">Company Bio</h5>
                  <p class="grey-text text-lighten-4">We are a team of college students working on this project like it's our full time job. Any amount would help support and continue development on this project and is greatly appreciated.</p>
                </div>
                <div class="col l3 s12">
                  <h5 class="white-text">Settings</h5>
                  <ul>
                    <li><a class="white-text" href="#!">Link 1</a></li>
                    <li><a class="white-text" href="#!">Link 2</a></li>
                    <li><a class="white-text" href="#!">Link 3</a></li>
                    <li><a class="white-text" href="#!">Link 4</a></li>
                  </ul>
                </div>
                <div class="col l3 s12">
                  <h5 class="white-text">Connect</h5>
                  <ul>
                    <li><a class="white-text" href="#!">Link 1</a></li>
                    <li><a class="white-text" href="#!">Link 2</a></li>
                    <li><a class="white-text" href="#!">Link 3</a></li>
                    <li><a class="white-text" href="#!">Link 4</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="footer-copyright">
            </div>
          </footer>
          <!--  Scripts-->
          <script src="js/jquery-2.1.1.min.js"></script>
          <script src="js/materialize.min.js"></script>
          <script src="js/init.js"></script>
          </body>
        </html>
    EOT;
    
    $generatedHtml = str_replace('{{className}}', $className, $htmlTemplate);
     file_put_contents("index.html", $generatedHtml);
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
    public function createJsComponents($className, $properties) {
        $this->createHtmlSPA();
        $this->createJsCreateItem($className, $properties);
        $this->createJsListItem($className, $properties);
        $this->createJsSearchItem($className, $properties);
        $this->createJsUpdateItemForm($className, $properties);
        $this->createJsApp($className);
    }
    private function createHtmlSPA(){
       
        $generatedHtml = <<<EOT
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Gerenciador de Itens SPA</title>
            <link rel="stylesheet" href="css/styles.css">
        </head>
        <body>
            <header>
                <nav>
                    <ul>
                        <li><a href="#" data-view="create">Criar Item</a></li>
                        <li><a href="#" data-view="list">Listar Itens</a></li>
                        <li><a href="#" data-view="search">Buscar por ID</a></li>
                    </ul>
                </nav>
            </header>
            <main id="app">
            </main>
            <footer>
                <p>&copy; 2024 Gerenciador de Itens SPA</p>
            </footer>
            <script type="module" src="js/App.js"></script>
        </body>
        </html>
        EOT;
    
        file_put_contents("index.html", $generatedHtml);
    }
    private function createJsListItem($className, $properties) {
        $jsDir = "js/components";
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    
        $jsListItemTemplate = <<<EOT
    import UpdateItemForm from './UpdateItemForm.js';
    class ListItem {
        constructor() {
        }
    
        render() {
            this.fetchItems()
            return `
                <button id="fetchItemsButton">Buscar Todos os Itens</button>
                <div id="itemsList"></div>
            `;
        }
    
        afterRender() {
            document.getElementById('fetchItemsButton').addEventListener('click', () => this.fetchItems());
        }
    
        async fetchItems() {
            const response = await fetch('/backend/Routes/{$className}Route.php');
            const items = await response.json();
            let itemsHtml = '<h2>Itens Disponíveis</h2><div class="panel">';
            items.{$className}.forEach((item) => {
                itemsHtml += `<div class="item" data-id="\${item.id}">
                    \${item.id} - \${item.nome} - \${item.idade}
                    <button class="delete-button" data-id="\${item.id}">Deletar</button></div>`;
            });
            itemsHtml += '</div>';
            document.getElementById('itemsList').innerHTML = itemsHtml;
    
            items.{$className}.forEach((item) => {
                document.querySelector(`.delete-button[data-id='\${item.id}']`).addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.deleteItem(item.id);
                });
                document.querySelector(`.item[data-id='\${item.id}']`).addEventListener('click', () => {
                    this.openUpdateModal(item);
                });
            });
        }
    
        async deleteItem(id) {
            await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
            });
    
            alert('Item deletado com sucesso.');
            this.fetchItems();
        }
    
        openUpdateModal(item) {
            const updateForm = new UpdateItemForm(this.fetchService, () => this.fetchItems());
            document.getElementById('app').innerHTML = updateForm.render();
            updateForm.fillUpdateForm(item);
            updateForm.afterRender();
        }
    }
    
    export default ListItem;
    EOT;
    
        file_put_contents("$jsDir/ListItem.js", $jsListItemTemplate);
    }
    
    private function createJsSearchItem($className, $properties) {
        $jsDir = "js/components";
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    
        $jsSearchItemTemplate = <<<EOT
    class SearchItem {
        constructor() {
        }
    
        render() {
            return `
                <form id="searchItemForm">
                    ID: <input type="number" id="itemId"><br>
                    <button type="submit">Buscar Item</button>
                </form>
                <div id="searchResult"></div>
            `;
        }
    
        afterRender() {
            document.getElementById('searchItemForm').addEventListener('submit', (e) => this.searchItem(e));
        }
    
        async searchItem(event) {
            event.preventDefault();
            const id = document.getElementById('itemId').value;
    
            const response = await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`);
            const item = await response.json();
    
            if (item) {
                document.getElementById('searchResult').innerHTML = `
                    <div>
                        \${item.{$className}.id} - \${item.{$className}.nome} - \${item.{$className}.idade}
                    </div>
                `;
            } else {
                document.getElementById('searchResult').innerHTML = '<div>Item não encontrado</div>';
            }
        }
    }
    
    export default SearchItem;
    EOT;
    
        file_put_contents("$jsDir/SearchItem.js", $jsSearchItemTemplate);
    }
    
    private function createJsUpdateItemForm($className, $properties) {
        $jsDir = "js/components";
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    
        $formFields = '';
        $formValues = '';
        $fillValues = '';
    
        foreach ($properties as $property) {
            $propName = $property['name'];
            $formFields .= <<<HTML
            <div class="input-field">
                <label for="update{$propName}">{$propName}</label>
                <input type="text" id="update{$propName}" class="validate">
            </div>
    HTML;
            $formValues .= 'const ' . $propName . ' = document.getElementById("update' . $propName . '").value;' . "\n";
            $fillValues .= 'document.getElementById("update' . $propName . '").value = item.' . $propName . ';' . "\n";
        }
    
        $jsUpdateItemFormTemplate = <<<EOT
    class UpdateItemForm {
        constructor(renderCallback) {
            this.renderApp = renderCallback;
        }
    
        render() {
            return `
            <div id="updateItemModal" class="modal">
            <div class="modal-content"><div class="close"> X </div>
                <h4>Atualizar Item</h4>
                <div class="input-field">
                    <label for="updateId">ID para atualizar</label>
                    <input type="text" id="updateId" class="validate">
                </div>
                {$formFields}
                <div class="modal-footer">
                    <button id="updateButton" class="btn">Atualizar Item</button>
                </div>
            </div>
        </div>
            `;
        }
    
        closeModal() {
            const modal = document.getElementById('updateItemModal');
            modal.style.display = 'none';
        }
    
        afterRender() {
            document.getElementById('updateButton').addEventListener('click', () => this.updateItem());
            document.querySelector('.close').addEventListener('click', () => this.closeModal());
        }
    
        async updateItem() {
            {$formValues}
    
            await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, nome, idade }),
            });
    
            alert('Item atualizado com sucesso.');
            this.renderApp();
        }
    
        fillUpdateForm(item) {
            {$fillValues}
        }
    }
    
    export default UpdateItemForm;
    EOT;
    
        file_put_contents("$jsDir/UpdateItemForm.js", $jsUpdateItemFormTemplate);
    }
    
    
    public function createJsCreateItem($className, $properties) {
        $jsDir = "js/components";
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    
        $formFields = '';
        $formValues = '';
        $fieldChecks = '';
        $fieldJson = '';
    
        foreach ($properties as $property) {
            $propName = $property['name'];
            $formFields .= ucfirst($propName) . ': <input type="text" id="' . $propName . '"><br>' . "\n";
            $formValues .= 'const ' . $propName . ' = document.getElementById("' . $propName . '").value;' . "\n";
            $fieldChecks .= 'if (!' . $propName . ') { alert("' . ucfirst($propName) . ' é obrigatório."); return; }' . "\n";
            $fieldJson .= $propName . ': ' . $propName . ', ';
        }
    
        $fieldJson = rtrim($fieldJson, ', ');
    
        $jsCreateTemplate = <<<EOT
    class CreateItem {
        constructor() {
        }
    
        render() {
            return `
                <form id="addItemForm">
                    {$formFields}
                    <button type="submit">Adicionar Item</button>
                </form>
            `;
        }
    
        afterRender() {
            document.getElementById('addItemForm').addEventListener('submit', (e) => this.addItem(e));
        }
    
        async addItem(event) {
            event.preventDefault();
            {$formValues}
    
            {$fieldChecks}
    
            await fetch('/backend/Routes/{$className}Route.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ {$fieldJson} }),
            });
    
            alert('Item adicionado com sucesso.');
        }
    }
    
    export default CreateItem;
    EOT;
    
        file_put_contents("$jsDir/CreateItem.js", $jsCreateTemplate);
    }
    
    private function createJsApp($className) {
        $jsDir = "js";
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    
        $jsAppTemplate = <<<EOT
    import CreateItem from './components/CreateItem.js';
    import ListItem from './components/ListItem.js';
    import SearchItem from './components/SearchItem.js';
    import UpdateItemForm from './components/UpdateItemForm.js';
    
    class App {
        constructor(apiBaseUrl) {
            this.apiBaseUrl = apiBaseUrl;
            this.appElement = document.getElementById('app');
            this.initApp();
        }
    
        initApp() {
            this.createItem = new CreateItem();
            this.listItem = new ListItem();
            this.searchItem = new SearchItem();
            
            document.querySelectorAll('nav a').forEach(navLink => {
                navLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.navigate(e.target.dataset.view);
                });
            });
    
            this.navigate('create');
        }
    
        navigate(view) {
            switch(view) {
                case 'create':
                    this.render(this.createItem.render());
                    this.createItem.afterRender();
                    break;
                case 'list':
                    this.render(this.listItem.render());
                    this.listItem.afterRender();
                    break;
                case 'search':
                    this.render(this.searchItem.render());
                    this.searchItem.afterRender();
                    break;
            }
        }
    
        render(html) {
            this.appElement.innerHTML = html;
        }
    }
    
    const apiBaseUrl = location.host; 
    const itemManager = new App(apiBaseUrl);
    EOT;
    
        file_put_contents("$jsDir/App.js", $jsAppTemplate);
    }
    
}
