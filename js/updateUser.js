document.getElementById('atualizar').addEventListener('click', function(e) {
    e.preventDefault();
const userId = document.getElementById('getUserId').value;
    async function atualizar(){
    const usuarioAtualizado = {};
    const form = document.forms['userForm'];
for (let i = 0; i < form.elements.length; i++) {
    const element = form.elements[i];
    if (element.name) {
        usuarioAtualizado[element.name] = element.value;
    }
}
    try {
        const response = await fetch('/backend/Routes/UsuariosRoute.php?id=' + userId, { 
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(usuarioAtualizado)
    });
    if (!response.ok) {
        switch(response.status) {
            case 400:
                throw new Error('Solicitação inválida.');
            case 501:
                throw new Error('Funcionalidade não implementada.');
            case 503:
                throw new Error('Serviço indisponível.');
            default:
                throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');
        }
    }
    const data = await response.json();

    Swal.fire(
        'Usuário atualizado com sucesso',
        '',
        'success'
      )
    } catch (error) {
        alert('Erro na requisição: ' + error.message);
        Swal.fire(
            error.message,
            '',
            'info'
          )
    }
}
atualizar()
});