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

echo '<div id="app">';
echo '<div class="form-card">';
echo '<link href="css/stylecriador.css" rel="stylesheet"><form action="install.php" method="post">';
echo '<label for="db_host">DB Host:</label>';
echo '<input type="text" id="db_host" name="db_host" placeholder="localhost"><br><br>';
echo '<label for="db_name">DB Nome do banco:</label>';
echo '<input type="text" id="db_name" name="db_name" placeholder="bancoXXYZ"><br><br>';
echo '<label for="db_user">DB Nome do usuário do banco:</label>';
echo '<input type="text" id="db_user" name="db_user" placeholder="root"><br><br>';
echo '<label for="db_password">DB Senha do banco:</label>';
echo '<input type="text" id="db_password" name="db_password" placeholder="admin123"><br><br>';
echo '<input type="submit" value="Continuar" name="submit">';
echo '</form>';
echo '</div>';
echo '</div>';
}
}

if(isset($db_host) && isset($db_user)){
  $conn = new PDO("mysql:host=$db_host;", $db_user, $db_password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
  $conn->exec($sql);
  echo "Banco $db_name Criado";

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

