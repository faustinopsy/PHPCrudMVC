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
            $jsCreateTemplate .= "      const form = document.forms['userForm'];\n";
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
            $jsCreateTemplate .= "          const response = await  fetch('/backend/Routes/{$className}Router.php', { \n";
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
            $jsCreateTemplate .= "          } catch (error) {\n";
            $jsCreateTemplate .= "              Swal.fire(\n";
            $jsCreateTemplate .= "              error.message,\n";
            $jsCreateTemplate .= "              '',\n";
            $jsCreateTemplate .= "              'info'\n";
            $jsCreateTemplate .= "              )\n";
            $jsCreateTemplate .= "           }\n";
            $jsCreateTemplate .= "        }\n";
            $jsCreateTemplate .= "  }\n\n";
            $jsCreateTemplate .= "      document.forms['userForm'].addEventListener('submit', function(e) {\n";
            $jsCreateTemplate .= "           e.preventDefault();\n";
            $jsCreateTemplate .= "          const $className = new Create{$className}();\n";
            $jsCreateTemplate .= "          $className.create();\n";
            $jsCreateTemplate .= "  });\n";
            file_put_contents("$jsDir/Create{$className}.js", $jsCreateTemplate);
        
            $jsFetchTemplate = "class Busca{$className}s {\n";
            $jsFetchTemplate .= "    async getAll() {\n";
            $jsFetchTemplate .= "           try {\n";
            $jsFetchTemplate .= "                   const response = await fetch('/backend/Routes/{$className}Router.php', {\n";
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
            $jsFetchTemplate .= "                       data.mensagem,\n";
            $jsFetchTemplate .= "                       '',\n";
            $jsFetchTemplate .= "                       'info'\n";
            $jsFetchTemplate .= "                   )\n";
            $jsFetchTemplate .= "               }\n";
            $jsFetchTemplate .= "             } catch (error) {\n";
            $jsFetchTemplate .= "                   alert('Erro na requisição: ' + error.mensagem);\n";
            $jsFetchTemplate .= "           }\n";
            $jsFetchTemplate .= "    }\n\n";
            $jsFetchTemplate .= "    display{$className}(data) {\n";
            $jsFetchTemplate .= "    const {$className} = data.usuarios;\n";
            $jsFetchTemplate .= "    const {$className}Div = document.getElementById('Lista');\n";
            $jsFetchTemplate .= "       {$className}Div.innerHTML = '';\n";
            $jsFetchTemplate .= "       const list = document.createElement('ul');\n";
            $jsFetchTemplate .= "       {$className}.forEach(user => {\n";
            $jsFetchTemplate .= "        const listItem = document.createElement('li');\n";
            $jsFetchTemplate .= "        listItem.textContent = `\${user.id} - \${user.nome} - \${user.email}`;\n";
            $jsFetchTemplate .= "        list.appendChild(listItem);\n";
            $jsFetchTemplate .= "    });\n";
            $jsFetchTemplate .= "   usersDiv.appendChild(list);\n";
            $jsFetchTemplate .= "    }\n";
            $jsFetchTemplate .= "}\n\n";
            $jsFetchTemplate .= "document.getElementById('getAllButton').addEventListener('click', () => {\n";
            $jsFetchTemplate .= "    const buscar = new Busca{$className}s();\n";
            $jsFetchTemplate .= "    buscar.getAll();\n";
            $jsFetchTemplate .= "});\n";
            file_put_contents("$jsDir/Busca{$className}s.js", $jsFetchTemplate);
        }
        
        function createHtmlForm($className, $properties) {
            $htmlTemplate = "<!DOCTYPE html>\n";
            $htmlTemplate .= "<html lang=\"en\">\n";
            $htmlTemplate .= "<head>\n";
            $htmlTemplate .= "    <meta charset=\"UTF-8\">\n";
            $htmlTemplate .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
            $htmlTemplate .= "    <title>$className Form</title>\n";
            $htmlTemplate .= "</head>\n";
            $htmlTemplate .= "<body>\n";
            $htmlTemplate .= "    <form id=\"$className-form\">\n";
            foreach ($properties as $property) {
                $htmlTemplate .= "        <label for=\"{$property['name']}\">{$property['name']}:</label>\n";
                $htmlTemplate .= "        <input type=\"text\" id=\"{$property['name']}\" name=\"{$property['name']}\"><br>\n";
            }
            $htmlTemplate .= "        <input type=\"submit\" value=\"Submit\">\n";
            $htmlTemplate .= "    </form>\n";
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
        
            file_put_contents("busca$className.html", $htmlTemplate2);
        } 
        createJsClasses($className, $properties);
        createHtmlForm($className, $properties);
        echo json_encode(['success' => true]);

        
    } else {
        throw new Exception('Invalid request method!');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
