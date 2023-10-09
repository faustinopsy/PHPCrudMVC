async function getUser() {
    const userId = document.getElementById("getUserId").value;
    try {
        const response = await fetch('/backend/usuario/' + userId, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
    });
    if (!response.ok) {
        switch(response.status) {
            case 400:
                throw new Error('Solicitação inválida.');
            case 401:
                throw new Error('Não autorizado - Faça login novamente.');
            case 403:
                throw new Error('Acesso proibido.');
            case 404:
                throw new Error('Recurso não encontrado.');
            case 405:
                throw new Error('Método não permitido.');
            case 409:
                throw new Error('Conflito - Usuário já existe.');
            case 422:
                throw new Error('Dados inválidos ou inprocessáveis.');
            case 429:
                throw new Error('Muitas solicitações - Tente novamente mais tarde.');
            case 500:
                throw new Error('Erro interno do servidor.');
            case 501:
                throw new Error('Funcionalidade não implementada.');
            case 503:
                throw new Error('Serviço indisponível.');
            default:
                throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');
        }
        
    }

    const data = await response.json();

        document.getElementById("nome").value = data.usuarios[0].nome; 
        document.getElementById("email").value = data.usuarios[0].email; 
        document.getElementById("senha").value = data.usuarios[0].senha; 
    } catch (error) {
        alert('Erro na requisição: ' + error.message);
    }
}