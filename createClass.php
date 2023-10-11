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
        echo json_encode(['success' => true]);

        
    } else {
        throw new Exception('Invalid request method!');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
