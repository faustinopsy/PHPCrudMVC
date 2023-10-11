class CreatePessoa {
    constructor() {
        this.id = null;
        this.nome = null;
        this.idade = null;
    }

    async create() {
      const Pessoa = {};
      const form = document.forms['Pessoa-form'];
      for (let i = 0; i < form.elements.length; i++) {
          const element = form.elements[i];
          if (element.name) {
              Pessoa[element.name] = element.value;
          }
      }
      if (!Pessoa.nome) {
          alert("Por favor, insira um nome!");
          return;
      }
      try {
          const response = await  fetch('/backend/Routes/PessoaRoute.php', { 
          method: 'POST',
          headers: {
              'Content-Type': 'application/json'
          },
          body: JSON.stringify(Pessoa)
      });
      if (!response.ok) {
          switch(response.status) {
              case 403:
                  throw new Error('Acesso proibido.');
              case 404:
                  throw new Error('Recurso não encontrado.');
              default:
                  throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');
          }
       }
          const data = await response.json();
              Swal.fire(
                  'Pessoa criado com sucesso',
                  '',
              'success'
               )
      for (let i = 0; i < form.elements.length; i++) {
          const element = form.elements[i];
          if (element.name) {
              element.value='';
          }
      }
      } catch (error) {
              Swal.fire(
              error.message,
              '',
              'info'
              )
           }
        }
  }

      document.forms['Pessoa-form'].addEventListener('submit', function(e) {
           e.preventDefault();
          const Pessoa = new CreatePessoa();
          Pessoa.create();
  });
