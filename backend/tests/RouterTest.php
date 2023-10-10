<?php

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testGetUsuario()
    {
        
        $requestMethod = 'GET';
        $uri = '/backend/usuario';

        $router = new \App\Router($requestMethod, $uri);

        ob_start();
        $router->run();
        $output = ob_get_clean();

        $responseData = json_decode($output, true);

        $this->assertTrue($responseData['status']);
        $this->assertEquals("Usuários recuperados com sucesso", $responseData['mensagem']);
        $this->assertIsArray($responseData['usuarios']);
    }
    public function testGetUsuarioPorId()
    {
        $requestMethod = 'GET';
        $userId = 4; 
        $uri = "/backend/usuario/{$userId}";
    
        $router = new \App\Router($requestMethod, $uri);
    
        ob_start();
        $router->run();
        $output = ob_get_clean();
    
        $responseData = json_decode($output, true);
    
        $this->assertFalse($responseData['status']);
        $this->assertEquals("nenhuma usuario encontrado", $responseData['mensagem']);
        $this->assertEmpty($responseData['usuarios']);
    }
    public function testGetUsuarioPorIdSucesso()
    {
        $requestMethod = 'GET';
        $userId = 2; 
        $uri = "/backend/usuario/{$userId}";
    
        $router = new \App\Router($requestMethod, $uri);
    
        ob_start();
        $router->run();
        $output = ob_get_clean();
    
        $responseData = json_decode($output, true);
    
        $this->assertTrue($responseData['status']);
        $this->assertEquals("Usuários recuperados com sucesso", $responseData['mensagem']);
        $this->assertIsArray($responseData['usuarios']);
    }
    
}
