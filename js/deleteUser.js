document.getElementById('excluir').addEventListener('click', function(e) {
    e.preventDefault();
    const userId = document.getElementById("getUserId").value;
    try {
        const response = fetch('/backend/Routes/UsuariosRoute.php?id=' + userId, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
    });
    if (!response.ok) {
        switch(response.status) {
            case 400:
                throw new Error('Solicitação inválida.');
            case 503:
                throw new Error('Serviço indisponível.');
            default:
                throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');
        }
    }
    const data = response.json();
    Swal.fire(
        'Usuário excluido com sucesso',
        '',
        'success'
      )
    } catch (error) {
        Swal.fire(
            error.mensagem,
            '',
            'info'
          )
    }
});