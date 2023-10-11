class UpdatePessoa {
    constructor() {
        this.id = null;
        this.nome = null;
        this.idade = null;
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
}

document.getElementById('update-button').addEventListener('click', function(e) {
    e.preventDefault();
    const id = document.getElementById('id-to-update').value;
    const updater = new UpdatePessoa();
    updater.update(id);
});
