document.forms['userForm'].addEventListener('submit', function(e) {
    e.preventDefault();
async  function create() {
    const usuario = {};
    const form = document.forms['userForm'];

        for (let i = 0; i < form.elements.length; i++) {
            const element = form.elements[i];
            if (element.name) {
                usuario[element.name] = element.value;
            }
        }

    if (!usuario.nome) {
        alert("Por favor, insira um nome!");
        return;
    }
    try {
        const response = await  fetch('/backend/Routes/UsuariosRoute.php', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(usuario)
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
            'Usuário criado com sucesso',
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
    create();
});

