# PHPCrudMVC

## Descrição
PHPCrudMVC 5.1.0 (Atualizado 20-10-2023 layout materializecss) é um pequeno e simplificado framework PHP, projetado para fornecer funcionalidades básicas e essenciais para o desenvolvimento de aplicações web. Ele oferece uma estrutura básica para operações CRUD (Create, Read, Update, Delete) e gerenciamento de conexões com banco de dados, permitindo que os desenvolvedores se concentrem na lógica de negócios específica de suas aplicações.
Este MicroFramework visa trazer facilidades que um grande framework tem, porém indo direto ao ponto usando PHP puro com PSR-4 sem esconder como as coisas funcionam, este modelo é então idealizado para estudante de programação que queiram entender os fluxos de dados que envolve uma aplicação real.
Com a estrutura MVC e uma capacidade de comunicação REST, onde o frontend é independente do backend, ambos trabalham separados e se comunicam no padrão API REST.

## Instalação
Clone o repositório
```bash
git clone https://github.com/faustinopsy/PHPCrudMVC.git
```
Instale as dependências do Composer
```bash
composer install
```


### Instalação e Configuração Inicial
O arquivo `install.php` na raiz do projeto facilita a configuração inicial do banco de dados e a criação de classes e tabelas correspondentes. Ao acessar este arquivo, será apresentado um formulário `migrate.php` solicitando as credenciais do banco de dados e, em seguida, criará o banco de dados conforme especificado, onde você fornece o nome da classe a ser criada e suas propriedades, em seguida abrirá os campos para colocar os nomes das propriedades e seus tipos, após confirmar será criado no diretório Model a classe, e no diretório Controller será criado as classe controller correspondente, e também será criado n diretório Routers o arquivo de rotas correspondente a classe controller.
 o `migrate.php` poderá ser chamado sempre que houver necessidade de criação de classes sem a necessidade de iniciar pelo install

### Criação Automática de Classes e Tabelas
Após a configuração inicial do banco de dados, o `install.php` (lembre-se de não subir para produção nem o install.php nem o migrate.php) também facilita a criação de classes modelo e suas tabelas correspondentes no banco de dados. O usuário pode especificar o nome da classe e suas propriedades (nome e tipo) através de um formulário. Com base nessas informações, o seguinte é gerado automaticamente:
- **Classe Modelo**: Uma classe PHP no diretório `Backend/Model` que representa um modelo de dados com propriedades e métodos getter e setter.
- **Controlador**: Um controlador correspondente no diretório `Backend/Controller` que facilita as operações CRUD para o modelo.
- **Tabela de Banco de Dados**: Uma tabela no banco de dados que corresponde ao modelo, com colunas que representam as propriedades da classe.
- **Procedures**: Procedures SQL para operações básicas de CRUD relacionadas à tabela criada.

- **Frontend**:será criado na raiz os htmls responsáveis chamar as requisições por meios dos arquivos javascipt correspondentes que são criados também.

### Geração de Classes JavaScript e Formulários HTML
Foi descontinuado a criação de javascript e formulários html, mas na raiz encontra-se modelos para realizar uma cópia e reproduzir para as classes correspondentes, e ajustar o caminho para a rota especifica

## Testes Automatizados
Os testes para as classes modelo e controladores também são gerados automaticamente, garantindo que as operações básicas de CRUD funcionem conforme esperado. Os testes são salvos no diretório `backend/tests` e podem ser executados usando PHPUnit para validar a lógica de negócios e operações de banco de dados.
De certo que os métodos deveram ser construidos é gerado apenas a base para criar seus proprios testes

## Uso
Após a configuração inicial e a criação das classes, controladores e tabelas, o MicroFramework está pronto para ser usado. Os desenvolvedores podem criar novas rotas, expandir os controladores existentes e adicionar novas lógicas de negócios conforme necessário, enquanto aproveitam as funcionalidades básicas de roteamento, banco de dados e CRUD fornecidas pelo MicroFramework.

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
    
### Connection
A classe Connection é responsável por estabelecer e gerenciar a conexão com o banco de dados. Ela utiliza PDO para garantir a compatibilidade com diversos sistemas de gerenciamento de banco de dados.

### Crud
A classe Crud estende Connection e oferece métodos para realizar operações CRUD básicas no banco de dados. Ela utiliza reflexão para determinar os campos que devem ser utilizados nas operações de banco de dados, permitindo uma certa flexibilidade e reutilização de código.

### UserController
UserController é uma classe exemplo que estende Crud, oferecendo funcionalidades específicas para manipular dados do usuário. Ela demonstra como as classes e métodos do MicroFramework podem ser estendidos e utilizados em casos de uso específicos.


## Pré-requisitos
```bash
PHP >= 8.2
Composer
MySQL (ou outro SGBD compatível com PDO)
```
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
