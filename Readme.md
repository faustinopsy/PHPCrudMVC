# MicroFramework

## Descrição
MicroFramework é um pequeno e simplificado framework PHP, projetado para fornecer funcionalidades básicas e essenciais para o desenvolvimento de aplicações web. Ele oferece uma estrutura básica para operações CRUD (Create, Read, Update, Delete) e gerenciamento de conexões com banco de dados, permitindo que os desenvolvedores se concentrem na lógica de negócios específica de suas aplicações.
Este MicroFramework visa trazer facilidades que um grande framework tem, porém indo direto ao ponto usando PHP puro sem esconder como as coisas funcionam, este modelo é então idealizado para estudante de programação que queiram entender os fluxo de dados que envolve uma aplicação real que possui uma estrutura MVC e uma capacidade de comunicação REST, onde o frontend é independente do backend, ambos trabalham separados e se comunicam no padrão API REST.

## Instalação
Clone o repositório
```bash
git clone https://github.com/faustinopsy/miniframework.git
```
Instale as dependências do Composer
```bash
composer install
```
Configure o banco de dados no arquivo config
Execute os scripts de criação de tabela conforme necessário
## Estrutura do Projeto
O projeto é estruturado de maneira clara e concisa, facilitando a compreensão e o desenvolvimento por parte dos desenvolvedores. A estrutura principal é composta por três classes principais:
- **Router**: é a classe que é responsável por gerenciar as requisições do frontend mediante os verbos http e o recurso ex.: recurso='backend/usuario' e verbo GET ele irá executar a classe e método correspondente.
- **backend/Database/config**: credenciais do banco de dados.
- **backend/Database/Connection**: Gerencia a conexão com o banco de dados.
- **backend/Database/Crud**: Fornece métodos genericos para realizar operações CRUD no banco de dados.
- **backend/Controller/UserController**: Um exemplo de controlador que estende as funcionalidades da classe Crud, permitindo a manipulação de dados do usuário.
- **backend/Model/Usuarios**: uma classe com propriedades estaticamente tipadas, este é um requisito para que se crie as tabelas do banco de dados de forma mapeada
- **backend/Database/TableCreate**: é a classe responsável por criar as tabelas no banco de dados quando ela recebe a classe model correspondente e cria também os store procedure para a classe.
    - **exemplo de uso do ORM (Mapeamento obejeto relacional)** : 
    o exemplo abaixo mostra que a oser  executado é criado uma tabela no banco de dados respeitando a estrutura da classe, desde que a classe siga o modelo de propriedades privadas e tipagem estatica como a classe usuarios que esta no diretório Model.
   ```bash

    use App\Database\TableCreator;
    use App\Model\Usuarios;
    $table = new TableCreator();
    $user = new Usuarios();
    $table->createTableFromModel($user);
    ```
### Connection
A classe Connection é responsável por estabelecer e gerenciar a conexão com o banco de dados. Ela utiliza PDO para garantir a compatibilidade com diversos sistemas de gerenciamento de banco de dados.

### Crud
A classe Crud estende Connection e oferece métodos para realizar operações CRUD básicas no banco de dados. Ela utiliza reflexão para determinar os campos que devem ser utilizados nas operações de banco de dados, permitindo uma certa flexibilidade e reutilização de código.

### UserController
UserController é uma classe exemplo que estende Crud, oferecendo funcionalidades específicas para manipular dados do usuário. Ela demonstra como as classes e métodos do MicroFramework podem ser estendidos e utilizados em casos de uso específicos.


## Pré-requisitos
O que você precisa para instalar o software e como instalá-lo:
PHP >= 8.2
Composer
MySQL (ou outro SGBD compatível com PDO)

## Testes
O MicroFramework agora vem com testes unitários, garantindo que as funcionalidades principais estejam funcionando conforme esperado e facilitando a identificação e correção de bugs durante o desenvolvimento. Os testes foram escritos utilizando PHPUnit e cobrem operações básicas de CRUD.

### Executando os Testes
Para executar os testes, você precisa ter o PHPUnit instalado e o xdebug para gerar os relatórios coverage.
um outro requisito é ter no php.ini a diretiva abaixo, e como verá a extensão xdebug no lugar indicado dentro do diretório extension do php:
```bash
[xdebug]
zend_extension ="C:/php/ext/php_xdebug.dll"

xdebug.remote_enable = off
xdebug.profiler_enable = off
xdebug.profiler_enable_trigger = Off
xdebug.profiler_output_name = cachegrind.out.%t.%p
xdebug.profiler_output_dir ="C:/tmp"
xdebug.show_local_vars=0
xdebug.mode = coverage
```
 Uma vez instalado, você pode executar os testes usando o seguinte comando no diretório raiz do projeto:
```bash
./vendor/bin/phpunit backend/tests --coverage-html coverage-report/
```
🤝 Contribuindo
Contribuições, issues e solicitações de feature são bem-vindas! Sinta-se à vontade para conferir a página de issues.

📜 Licença
Este projeto está licenciado sob a licença MIT .

📫 Contato
Rodrigo Faustino - @faustinopsy - rodrigohipnose@gmail.com