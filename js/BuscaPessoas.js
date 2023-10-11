class BuscaPessoas {
    async getAll() {
           try {
                   const response = await fetch('/backend/Routes/PessoaRoute.php', {
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
                   this.displayPessoa(data);
               }else{
                   Swal.fire(
                       'nenhum dado retornado',
                       '',
                       'info'
                   )
               }
             } catch (error) {
                   alert('Erro na requisição: ' + error.mensagem);
           }
    }

    displayPessoa(data) {
        const Pessoa = data.Pessoa;
        const PessoaDiv = document.getElementById('Lista');
        PessoaDiv.innerHTML = '';
        const list = document.createElement('ul');
        Pessoa.forEach(item => {
            const listItem = document.createElement('li');
            listItem.textContent = this.objectToString(item);
            list.appendChild(listItem);
        });
        PessoaDiv.appendChild(list);
    }

    objectToString(obj) {
        return Object.keys(obj).map(function(key) {
            return key + ': ' + obj[key];
        }).join(' - ');
    }
}

    const buscar = new BuscaPessoas();
    buscar.getAll();
