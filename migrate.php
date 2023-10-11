<!DOCTYPE html>
<html lang="pt_BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criador de classes</title>
    <style>
    .campos-form {
      display: flex;
      align-items: center;
    }
    h1, h2, h3, h4 {
  text-align: center;
  
  }
    .campos-form label {
      width: 150px;
      text-align: right;
      margin-right: 10px;
    }
    pre {
      background-color: #1e1e1e;
      color: #d4d4d4;
      font-family: Consolas, "Courier New", Courier, monospace;
      font-size: 14px;
      line-height: 1.5;
      overflow-x: auto;
      padding: 16px;
    }
    form {
    width: 50%;
    margin: 0 auto;
    padding: 20px;
    background-color: #f2f2f2;
    border-radius: 10px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
  }

  form p {
    margin-bottom: 15px;
    font-size: 18px;
  }

  form input[type="text"],select  {
    width: 100%;
    padding: 10px;
    font-size: 18px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }
  form input[type="number"],select {
    width: 100%;
    padding: 10px;
    font-size: 18px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }
  form input[type="submit"] {
    width: 100%;
    padding: 10px;
    font-size: 18px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 20px;
  }

  form input[type="submit"]:hover {
    background-color: #3e8e41;
  }
  .btn-danger {
  background-color: #dc3545;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.btn-info {
  background-color: #17a2b8;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}
.card {
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
  max-width: 300px;
  margin: auto;
  text-align: center;
  font-family: arial;
}
@media (max-width: 600px) {
  form {
    width: 90%;
  }
  .campos-form {
  display: flex;
  flex-direction: column;
  flex-wrap: wrap;
}
.campos-form {
    flex-direction: row;
  }
}


.card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-body {
  padding: 2px 16px;
}

.card-title {
  font-size: 22px;
  margin-top: 10px;
}

.card-text {
  font-size: 14px;
  color: gray;
  margin-bottom: 10px;
}

.btn {
  border: none;
  outline: 0;
  padding: 12px 16px;
  background-color: #f1c40f;
  color: white;
  cursor: pointer;
  border-radius: 5px;
  text-align: center;
  font-size: 18px;
  margin-top: 10px;
}

.btn:hover {
  background-color: #f7dc6f;
}
.switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: 0.4s;
    }

    input:checked + .slider {
      background-color: #2196F3;
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    .slider.round {
      border-radius: 34px;
    }

    .slider.round:before {
      border-radius: 50%;
    }
    .property {
    display: flex;
    align-items: center;
    gap: 10px; 
}

.property label,
.property input,
.property select {
    margin-bottom: 10px; 
}

  </style>
</head>
<body>
    <h1>Criador de classe</h1>
    <form id="classForm">
        <label for="className">Nome da Classe:</label>
        <input type="text" id="className" name="className" required><br><br>
        <label for="propertyCount">Quantidade de propriedades:</label>
        <input type="number" id="propertyCount" name="propertyCount" min="1" required><br><br>
        <div id="properties"></div>
        <input type="submit" value="Criar Classe">
    </form>

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
            document.addEventListener('DOMContentLoaded', (event) => {
            document.getElementById('propType0').addEventListener('change', function(e) {
                e.target.value = 'int';
            });
        });
        });
        
        document.getElementById('classForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);

            fetch('createClass.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                alert('Classe e controller e tabela Criadas!');
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('Erro ao criar classe!');
            });
        });
    </script>
</body>
</html>
