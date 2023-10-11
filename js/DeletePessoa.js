class DeletePessoa {
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

document.getElementById('delete-button').addEventListener('click', function(e) {
    e.preventDefault();
    const id = document.getElementById('id-to-delete').value;
    const deleter = new DeletePessoa();
    deleter.delete(id);
});
