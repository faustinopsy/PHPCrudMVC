<?php
require_once 'vendor/autoload.php'; 

use App\Database\TableCreator;


header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;
       
        if (!isset($data['className'], $data['propertyCount'])) {
            throw new Exception('Nome da classe é obrigatória e ao menos uma propriedade!');
        }
        $className = ucfirst($data['className']);
        $propertyCount = $data['propertyCount'];
        $properties = [];
        $useDateTime = false;
        for ($i = 0; $i < $propertyCount; $i++) {
            if (!isset($data["propName{$i}"], $data["propType{$i}"])) {
                throw new Exception("Nome e tipo da propriedade são obrigatórios {$i}!");
            }
            $properties[] = [
                'name' => $data["propName{$i}"],
                'type' => $data["propType{$i}"],
            ];
            if ($data["propType{$i}"] === 'datetime') {
                $useDateTime = true;
            }
        }
        $classTemplate = "<?php\n\nnamespace App\Model;\n\n";
        if ($useDateTime) {
            $classTemplate .= "use DateTime;\n\n";
        }
        $classTemplate .="class $className {\n";
           
        foreach ($properties as $property) {
            $classTemplate .= "    private {$property['type']} \${$property['name']};\n";
        }
        foreach ($properties as $property) {
            $camelCasePropName = ucfirst($property['name']);
            $classTemplate .= "\n    public function get$camelCasePropName() {\n";
            $classTemplate .= "        return \$this->{$property['name']};\n    }\n";

            $classTemplate .= "\n    public function set$camelCasePropName({$property['type']} \${$property['name']}) {\n";
            $classTemplate .= "        \$this->{$property['name']} = \${$property['name']};\n";
            $classTemplate .= "        return \$this;\n    }\n";
        }
        $classTemplate .= "}\n";
        $dir = "Backend/Model";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents("$dir/$className.php", $classTemplate);

        $make = new TableCreator();
        require_once "$dir/$className.php";
        
        $fullClassName = "App\\Model\\$className";
        $classeMigrate = new $fullClassName();
        
        $make->createController($classeMigrate);
        $make->createTableFromModel($classeMigrate);
        $make->createTests($classeMigrate);
        $make->createRoute($classeMigrate);

        function createJsClasses($className, $properties) {
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
                $jsCreateTemplate .= "      if (!{$className}.nome) {\n";
                $jsCreateTemplate .= "          alert(\"Por favor, insira um nome!\");\n";
                $jsCreateTemplate .= "          return;\n";
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

            $jsUpdateTemplate = "class Update{$className} {\n";
            $jsUpdateTemplate .= "    constructor() {\n";
            foreach ($properties as $property) {
                $jsUpdateTemplate .= "        this.{$property['name']} = null;\n";
            }
            $jsUpdateTemplate .= "    }\n\n";
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
            $jsUpdateTemplate .= "}\n\n";
            $jsUpdateTemplate .= "document.getElementById('update-button').addEventListener('click', function(e) {\n";
            $jsUpdateTemplate .= "    e.preventDefault();\n";
            $jsUpdateTemplate .= "    const id = document.getElementById('id-to-update').value;\n";
            $jsUpdateTemplate .= "    const updater = new Update{$className}();\n";
            $jsUpdateTemplate .= "    updater.update(id);\n";
            $jsUpdateTemplate .= "});\n";
        
            file_put_contents("$jsDir/Update{$className}.js", $jsUpdateTemplate);

            $jsDeleteTemplate = "class Delete{$className} {\n";
            $jsDeleteTemplate .= "    async delete(id) {\n";
            $jsDeleteTemplate .= "      try {\n";
            $jsDeleteTemplate .= "          const response = await fetch(`/backend/Routes/{$className}Route.php?id=\${id}`, { \n";
            $jsDeleteTemplate .= "          method: 'DELETE',\n";
            $jsDeleteTemplate .= "          headers: {\n";
            $jsDeleteTemplate .= "              'Content-Type': 'application/json'\n";
            $jsDeleteTemplate .= "          }\n";
            $jsDeleteTemplate .= "      });\n";
            $jsDeleteTemplate .= "      if (!response.ok) {\n";
            $jsDeleteTemplate .= "          throw new Error('Erro na exclusão.');\n";
            $jsDeleteTemplate .= "      }\n";
            $jsDeleteTemplate .= "      Swal.fire(\n";
            $jsDeleteTemplate .= "          '{$className} excluído com sucesso',\n";
            $jsDeleteTemplate .= "          '',\n";
            $jsDeleteTemplate .= "          'success'\n";
            $jsDeleteTemplate .= "      );\n";
            $jsDeleteTemplate .= "      } catch (error) {\n";
            $jsDeleteTemplate .= "          Swal.fire(\n";
            $jsDeleteTemplate .= "              error.message,\n";
            $jsDeleteTemplate .= "              '',\n";
            $jsDeleteTemplate .= "              'error'\n";
            $jsDeleteTemplate .= "          );\n";
            $jsDeleteTemplate .= "      }\n";
            $jsDeleteTemplate .= "    }\n";
            $jsDeleteTemplate .= "}\n\n";
            $jsDeleteTemplate .= "document.getElementById('delete-button').addEventListener('click', function(e) {\n";
            $jsDeleteTemplate .= "    e.preventDefault();\n";
            $jsDeleteTemplate .= "    const id = document.getElementById('id-to-delete').value;\n";
            $jsDeleteTemplate .= "    const deleter = new Delete{$className}();\n";
            $jsDeleteTemplate .= "    deleter.delete(id);\n";
            $jsDeleteTemplate .= "});\n";
        
            file_put_contents("$jsDir/Delete{$className}.js", $jsDeleteTemplate);
        }
       
       
        
        function createHtmlForm($className, $properties) {
            $htmlTemplate = "<!DOCTYPE html>\n";
            $htmlTemplate .= "<html lang=\"en\">\n";
            $htmlTemplate .= "<head>\n";
            $htmlTemplate .= "    <meta charset=\"UTF-8\">\n";
            $htmlTemplate .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
            $htmlTemplate .= "    <title>$className Form</title>\n";
            $htmlTemplate .= "<link rel=\"stylesheet\" href=\"css/styles.css\">\n";
            $htmlTemplate .= "<link rel=\"stylesheet\" href=\"css/sweetalert2.min.css\">\n";
            $htmlTemplate .= "</head>\n";
            $htmlTemplate .= "<body>\n";
            $htmlTemplate .= "    <form id=\"$className-form\">\n";
            foreach ($properties as $property) {
                if ($property['name'] === 'id') {
                    continue; 
                }
                $htmlTemplate .= "        <label for=\"{$property['name']}\">{$property['name']}:</label>\n";
                $htmlTemplate .= "        <input type=\"text\" id=\"{$property['name']}\" name=\"{$property['name']}\"><br>\n";
            }
            $htmlTemplate .= "        <input type=\"submit\" value=\"Submit\">\n";
            $htmlTemplate .= "    </form>\n";
            $htmlTemplate .= "<script src=\"js/sweetalert2.all.min.js\"></script>\n";
            $htmlTemplate .= "    <script src=\"js/Create$className.js\"></script>\n";
            $htmlTemplate .= "</body>\n";
            $htmlTemplate .= "</html>\n";
        
            file_put_contents("cria$className.html", $htmlTemplate);
            $htmlTemplate2 = "<!DOCTYPE html>\n";
            $htmlTemplate2 .= "<html lang=\"pt_BR\">\n";
            $htmlTemplate2 .= "<head>\n";
            $htmlTemplate2 .= "<meta charset=\"UTF-8\">\n";
            $htmlTemplate2 .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
            $htmlTemplate2 .= "<title>Usuários</title>\n";
            $htmlTemplate2 .= "<link rel=\"stylesheet\" href=\"css/styles.css\">\n";
            $htmlTemplate2 .= "<link rel=\"stylesheet\" href=\"css/sweetalert2.min.css\">\n";
            $htmlTemplate2 .= "</head>\n";
            $htmlTemplate2 .= "<body>\n";
            $htmlTemplate2 .= "<div class=\"card\">\n";
            $htmlTemplate2 .= "<a href=\"../\">Voltar</a>\n";
            $htmlTemplate2 .= "<div id=\"Lista\"></div>\n";
            $htmlTemplate2 .= "</div>\n";
            $htmlTemplate2 .= "<script src=\"js/sweetalert2.all.min.js\"></script>\n";
            $htmlTemplate2 .= "<script src=\"js/Busca{$className}s.js\"></script> \n";
            $htmlTemplate2 .= "</body>\n";
            $htmlTemplate2 .= "</html>";
        
            file_put_contents("todos$className.html", $htmlTemplate2);

            $htmlTemplate3 = "<!DOCTYPE html>\n";
            $htmlTemplate3 .= "<html lang=\"pt_BR\">\n";
            $htmlTemplate3 .= "<head>\n";
            $htmlTemplate3 .= "    <meta charset=\"UTF-8\">\n";
            $htmlTemplate3 .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
            $htmlTemplate3 .= "    <link rel=\"stylesheet\" href=\"css/styles.css\">\n";
            $htmlTemplate3 .= "    <link rel=\"stylesheet\" href=\"css/sweetalert2.min.css\">\n";
            $htmlTemplate3 .= "    <title>Gerenciamento de $className</title>\n";
            $htmlTemplate3 .= "</head>\n";
            $htmlTemplate3 .= "<body>\n";
            $htmlTemplate3 .= "    <div class=\"card\">\n";
            $htmlTemplate3 .= "        <form id=\"userForm\">\n";
            $htmlTemplate3 .= "            <a href=\"../\">Voltar</a>\n";
            $htmlTemplate3 .= "            <h3>Buscar $className</h3>\n";
            $htmlTemplate3 .= "            <label for=\"getUserId\">ID do $className:</label>\n";
            $htmlTemplate3 .= "            <input type=\"number\" id=\"getUserId\">\n";
            $htmlTemplate3 .= "            <button onclick=\"getUser()\">Buscar</button><br>\n";
            $htmlTemplate3 .= "            <h3>Gerenciar $className</h3>\n";
        
            foreach ($properties as $property) {
                if ($property['name'] === 'id') {
                    continue; 
                }
                $htmlTemplate3 .= "            <label for=\"{$property['name']}\">{$property['name']}:</label>\n";
                $htmlTemplate3 .= "            <input type=\"text\" id=\"{$property['name']}\" name=\"{$property['name']}\" required><br>\n";
            }
        
            $htmlTemplate3 .= "            <button id=\"atualizar\">Atualizar</button>\n";
            $htmlTemplate3 .= "            <button id=\"excluir\">Excluir</button>\n";
            $htmlTemplate3 .= "        </form>\n";
            $htmlTemplate3 .= "    </div>\n";
            $htmlTemplate3 .= "    <script src=\"js/sweetalert2.all.min.js\"></script>\n";
            $htmlTemplate3 .= "    <script src=\"js/get$className.js\"></script>\n";
            $htmlTemplate3 .= "    <script src=\"js/update$className.js\"></script>\n";
            $htmlTemplate3 .= "    <script src=\"js/delete$className.js\"></script>\n";
            $htmlTemplate3 .= "</body>\n";
            $htmlTemplate3 .= "</html>\n";
        
            file_put_contents("gerenciar$className.html", $htmlTemplate3);
        } 
        function createHtmlTemplate($className) {
            $htmlTemplate = <<<EOT
        <!DOCTYPE html>
        <html lang="pt_BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Formulário de {{className}}</title>
            <link rel="stylesheet" href="css/styles.css">
        </head>
        <body>
            <div class="card">
                <h1>Escolha a Opção</h1>
                <a href="cria{{className}}.html">Cadastrar</a><br>
                <a href="todos{{className}}.html">Listar todos</a><br>
                <a href="busca{{className}}.html">Buscar</a>
            </div>
        </body>
        </html>
        EOT;
        
        $generatedHtml = str_replace('{{className}}', $className, $htmlTemplate);
         file_put_contents("{$className}Form.html", $generatedHtml);
        }
        
        createJsClasses($className, $properties);
        createHtmlForm($className, $properties);
        createHtmlTemplate($className);
        echo json_encode(['success' => true]);

        
    } else {
        throw new Exception('Invalid request method!');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
