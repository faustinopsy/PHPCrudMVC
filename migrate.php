<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criador de classes</title>
    <link href="css/stylecriador.css" rel="stylesheet">
    <link rel="stylesheet" href="css/sweetalert2.min.css">
</head>
<body>
<div id="app">
    <div class="form-card">
    <h1>Criador de classe</h1>
    <form id="classForm">
        <label for="className">Nome da Classe:</label>
        <input type="text" id="className" name="className" required placeholder="Usuario"><br><br>
        <label for="propertyCount">Quantidade de propriedades:</label>
        <input type="number" id="propertyCount" name="propertyCount" min="1" required placeholder="3"><br><br>
        <div class="toggle-container">
            <label class="toggle-label">
                Criar SPA:
                <input type="checkbox" id="isSPA" name="isSPA" class="toggle-checkbox" checked >
                <div class="toggle-switch"></div>
            </label>
        </div>
        <div id="properties"></div>
        <input type="submit" value="Criar Classe">
    </form>
    </div>
</div>
<script src="js/sweetalert2.all.min.js"></script>
<script>
    document.getElementById('propertyCount').addEventListener('input', function(e) {
        const count = e.target.value;
        const propertiesDiv = document.getElementById('properties');
        propertiesDiv.innerHTML = '';
        propertiesDiv.innerHTML += `
        <div class="property">
            <label for="propName0">Nome da Propriedade:</label>
            <input type="text" id="propName0" name="propName0" value="id" readonly>
            <label for="propType0">Tipo:</label>
            <select name="propType0" required>
                <option value="int" readonly selected>int</option>
            </select>
        </div>
        <br><br>
        `;

        for(let i = 1; i < count; i++) {
            propertiesDiv.innerHTML += `
            <div class="property">
                <label for="propName${i}">Nome da Propriedade:</label>
                <input type="text" id="propName${i}" name="propName${i}" required>
                <label for="propType${i}">Tipo:</label>
                <select name="propType${i}" required>
                    <option value="string">string</option>
                    <option value="int">int</option>
                    <option value="float">float</option>
                    <option value="datetime">datetime</option>
                    <option value="bool">bool</option>
                </select>
                </div>
                <br><br>
            `;
        }
    });
    document.getElementById('classForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('spa', document.getElementById('isSPA').checked);
        formData.append('tipo',location.hash);
        fetch('createClass.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            Swal.fire(
                'Tudo criado com sucesso!',
                '',
                'success'
            );
            location.href = 'index.html'
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Erro ao criar classe!');
        });
    });
</script>
</body>
</html>
