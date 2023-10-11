document.getElementById('getAllButton').addEventListener('click', getAll);

async function getAll() {
    try {
        const response = await fetch('/backend/Routes/UsuariosRoute.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
        });
        if (!response.ok) {
            switch(response.status) {
                case 503:
                    throw new Error('Serviço indisponível.');
                default:
                    throw new Error('Ocorreu um erro inesperado. Por favor, tente novamente.');
            }   
        }
        const data = await response.json();
        if(data.status){
            displayUsers(data);
        }else{
            Swal.fire(
                data.mensagem,
                '',
                'info'
              )
        }
    } catch (error) {
        alert('Erro na requisição: ' + error.mensagem);
    }
}
function displayUsers(data) {
    const users = data.usuarios;
    const usersDiv = document.getElementById('usersList');
    usersDiv.innerHTML = '';
    const list = document.createElement('ul');
    users.forEach(user => {
        const listItem = document.createElement('li');
        listItem.textContent = `${user.id} - ${user.nome} - ${user.email}`;
        list.appendChild(listItem);
    });
    usersDiv.appendChild(list);
}

getAll();
