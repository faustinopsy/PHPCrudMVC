<?php

if (file_exists('config.php')) {
    require_once 'config.php';
		$db_host = DB_HOST;
        $db_name = DB_NAME;
        $db_user = DB_USER;
        $db_password = DB_PASSWORD;
} else {
    if (isset($_POST['submit'])) {
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_password = $_POST['db_password'];

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
echo '<input type="submit" value="Submit" name="submit">';
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

        $config_file = 'config.php';
        $config_content = 
"<?php
return [
	'db' => [
		'host' => $db_host,
		'name' => $db_name,
		'user' => $db_user,
		'pass' => $db_password,
		'db_type' => 'mysql',
	],
	
];\n";
        file_put_contents($config_file, $config_content, LOCK_EX);
        if (!file_exists($config_file)) {
            die('Erro ao escrever o arquivo');
        }
        header('Location: migrate.php');
        exit;
}

