class GerenciarPessoa {
    constructor() {
        this.id = null;
        this.nome = null;
        this.idade = null;
        this.altura = null;
    }

async search(id) {

    try {
        const response = await fetch(`/backend/Routes/PessoaRoute.php?id=${id}`, { 
       method: 'GET',
    headers: {
            'Content-Type': 'application/json'
    },
});
   if (!response.ok) {
       throw new Error('Erro na atualização.');
   }
const data = await response.json();
if(data.status){
  document.getElementById('nome').value =data.Pessoa.nome;
  document.getElementById('idade').value =data.Pessoa.idade;
  document.getElementById('altura').value =data.Pessoa.altura;
  }else{
       Swal.fire(
        data.mensagem,
      '',
    'info'
  )
 }
 } catch (error) {
Swal.fire(
    error.message,
    '',
   'error'
 );
 }
 }
    async update(id) {
      const Pessoa = {};
      const form = document.forms['Pessoa-form'];
      for (let i = 0; i < form.elements.length; i++) {
          const element = form.elements[i];
          if (element.name) {
              Pessoa[element.name] = element.value;
          }
      }
      try {
          const response = await fetch(`/backend/Routes/PessoaRoute.php?id=${id}`, { 
          method: 'PUT',
          headers: {
              'Content-Type': 'application/json'
          },
          body: JSON.stringify(Pessoa)
      });
      if (!response.ok) {
          throw new Error('Erro na atualização.');
      }
      Swal.fire(
          'Pessoa atualizado com sucesso',
          '',
          'success'
      );
      } catch (error) {
          Swal.fire(
              error.message,
              '',
              'error'
          );
      }
    }
    async delete(id) {
      try {
          const response = await fetch(`/backend/Routes/PessoaRoute.php?id=${id}`, { 
          method: 'DELETE',
          headers: {
              'Content-Type': 'application/json'
          }
      });
      if (!response.ok) {
          throw new Error('Erro na exclusão.');
      }
      Swal.fire(
          'Pessoa excluído com sucesso',
          '',
          'success'
      );
      } catch (error) {
          Swal.fire(
              error.message,
              '',
              'error'
          );
      }
    }
}

document.getElementById('search-button').addEventListener('click', function(e) {
    e.preventDefault();
    const id = document.getElementById('idPessoa').value;
    const Pessoa = new GerenciarPessoa();
    Pessoa.search(id);
});
document.getElementById('update-button').addEventListener('click', function(e) {
    e.preventDefault();
    const id = document.getElementById('idPessoa').value;
    const Pessoa = new GerenciarPessoa();
    Pessoa.update(id);
});
document.getElementById('delete-button').addEventListener('click', function(e) {
    e.preventDefault();
    const id = document.getElementById('idPessoa').value;
    const Pessoa = new GerenciarPessoa();
    Pessoa.delete(id);
});
