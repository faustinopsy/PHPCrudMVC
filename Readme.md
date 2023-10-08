MicroFramework
Descrição
MicroFramework é um pequeno e simplificado framework PHP, projetado para fornecer funcionalidades básicas e essenciais para o desenvolvimento de aplicações web. Ele oferece uma estrutura básica para operações CRUD (Create, Read, Update, Delete) e gerenciamento de conexões com banco de dados, permitindo que os desenvolvedores se concentrem na lógica de negócios específica de suas aplicações.

Estrutura do Projeto
O projeto é estruturado de maneira clara e concisa, facilitando a compreensão e o desenvolvimento por parte dos desenvolvedores. A estrutura principal é composta por três classes principais:

Connection: Gerencia a conexão com o banco de dados.
Crud: Fornece métodos para realizar operações CRUD no banco de dados.
UserController: Um exemplo de controlador que estende as funcionalidades da classe Crud, permitindo a manipulação de dados do usuário.
Connection
A classe Connection é responsável por estabelecer e gerenciar a conexão com o banco de dados. Ela utiliza PDO para garantir a compatibilidade com diversos sistemas de gerenciamento de banco de dados.

Crud
A classe Crud estende Connection e oferece métodos para realizar operações CRUD básicas no banco de dados. Ela utiliza reflexão para determinar os campos que devem ser utilizados nas operações de banco de dados, permitindo uma certa flexibilidade e reutilização de código.

UserController
UserController é uma classe exemplo que estende Crud, oferecendo funcionalidades específicas para manipular dados do usuário. Ela demonstra como as classes e métodos do MicroFramework podem ser estendidos e utilizados em casos de uso específicos.

Testes
O MicroFramework agora vem com testes unitários, garantindo que as funcionalidades principais estejam funcionando conforme esperado e facilitando a identificação e correção de bugs durante o desenvolvimento. Os testes foram escritos utilizando PHPUnit e cobrem operações básicas de CRUD.

Executando os Testes
Para executar os testes, você precisa ter o PHPUnit instalado. Uma vez instalado, você pode executar os testes usando o seguinte comando no diretório raiz do projeto:

phpunit --bootstrap vendor/autoload.php tests


Como Usar
Configuração
Configure o banco de dados e ajuste as configurações no arquivo config.php.
Certifique-se de que todas as dependências estão instaladas usando o Composer.
Utilização Básica
Criar um Modelo: Crie um modelo para representar uma entidade do seu domínio.
Criar um Controlador: Crie um controlador que estenda a classe Crud ou UserController para manipular os dados do seu modelo.
Operações CRUD: Utilize os métodos disponíveis na classe Crud para realizar operações de banco de dados.
Contribuindo
Sinta-se à vontade para contribuir com o MicroFramework! Faça um fork, adicione suas melhorias e faça um pull request.

Licença
Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE.md para mais detalhes.