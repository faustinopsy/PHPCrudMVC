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

  form input[type="text"],select, input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 18px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }
  form input[type="number"],select,input[type="password"] {
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
<?php

if (file_exists('config.php')) {
    require_once 'config.php';
		$db_host = DB_HOST;
        $db_name = DB_NAME;
        $db_user = DB_USER;
        $db_password = DB_PASSWORD;
} else {
    if (isset($_POST['submit'])) {
if(!($_POST['db_host']) || !($_POST['db_name']) || !($_POST['db_user'] )
|| !($_POST['db_password'])){
 echo "preecha todos os campos";
 exit;
}
        $db_host = isset($_POST['db_host'])? $_POST['db_host']: '';
        $db_name = isset($_POST['db_name'])? $_POST['db_name'] : '';
        $db_user = isset($_POST['db_user'])? $_POST['db_user']: '';
        $db_password = isset($_POST['db_password'])?$_POST['db_password'] :'';

    } else {

echo '<form action="install.php" method="post">';
echo '<label for="db_host">DB Host:</label>';
echo '<input type="text" id="db_host" name="db_host"><br><br>';
echo '<label for="db_name">DB Name:</label>';
echo '<input type="text" id="db_name" name="db_name"><br><br>';
echo '<label for="db_user">DB User:</label>';
echo '<input type="text" id="db_user" name="db_user"><br><br>';
echo '<label for="db_password">DB Password:</label>';
echo '<input type="password" id="db_password" name="db_password"><br><br>';
echo '<input type="submit" value="Continuar" name="submit">';
echo '</form>';
}
}

if(isset($db_host) && isset($db_user)){
        $conn = mysqli_connect($db_host, $db_user, $db_password);
        if (!$conn) {
            die('Erro ao conectar ao banco de dados: ' . mysqli_error($conn));
        }
        $db_selected = mysqli_select_db($conn, $db_name);
        if (!$db_selected) {
            $sql = 'CREATE DATABASE IF NOT EXISTS ' . $db_name;
            if (mysqli_query($conn, $sql)) {
                echo 'Banco de dados ' . $db_name . ' Criado com sucesso';
				$db_selected = mysqli_select_db($conn, $db_name);
				if (!$db_selected) {
					die('Error selecting database: ' . mysqli_error($conn));
				}
            } else {
                die('Erro ao criar o banco de dados: ' . mysqli_error($conn));
            }
        }

        $config_file = 'backend/Database/config.php';
        $config_content = 
"<?php
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASSWORD', '$db_password');
define('DB_TYPE', 'mysql');
	
\n";
        file_put_contents($config_file, $config_content, LOCK_EX);
        if (!file_exists($config_file)) {
            die('Erro ao escrever o arquivo');
        }
        header('Location: migrate.php');
        exit;
}

