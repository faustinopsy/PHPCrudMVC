<?php

if (file_exists('config.php')) {
    require_once 'config.php';
    $db_host = DB_HOST;
    $db_name = DB_NAME;
    $db_user = DB_USER;
    $db_password = DB_PASSWORD;
    $db_type = DB_TYPE;
} else {
    if (isset($_POST['submit'])) {
        $db_type = isset($_POST['db_type']) ? $_POST['db_type'] : 'mysql';
        if ($db_type === 'mysql' && (!isset($_POST['db_host']) || !isset($_POST['db_name']) || !isset($_POST['db_user']) || !isset($_POST['db_password']))) {
            echo "Preencha todos os campos";
            exit;
        }
        $db_host = isset($_POST['db_host']) ? $_POST['db_host'] : '';
        $db_name = isset($_POST['db_name']) ? $_POST['db_name'] : '';
        $db_user = isset($_POST['db_user']) ? $_POST['db_user'] : '';
        $db_password = isset($_POST['db_password']) ? $_POST['db_password'] : '';
    } else {
        echo '<div id="app">';
        echo '<div class="form-card">';
        echo '<link href="css/stylecriador.css" rel="stylesheet">';
        echo '<form action="install.php" method="post">';
        echo '<label for="db_type">Tipo de Banco de Dados:</label>';
        echo '<select id="db_type" name="db_type">';
        echo '<option value="mysql">MySQL</option>';
        echo '<option value="sqlite">SQLite</option>';
        echo '</select><br><br>';
        echo '<div id="mysql_fields">';
        echo '<label for="db_host">DB Host:</label>';
        echo '<input type="text" id="db_host" name="db_host" placeholder="localhost"><br><br>';
        echo '<label for="db_name">DB Nome do banco:</label>';
        echo '<input type="text" id="db_name" name="db_name" placeholder="bancoXXYZ"><br><br>';
        echo '<label for="db_user">DB Nome do usuário do banco:</label>';
        echo '<input type="text" id="db_user" name="db_user" placeholder="root"><br><br>';
        echo '<label for="db_password">DB Senha do banco:</label>';
        echo '<input type="text" id="db_password" name="db_password" placeholder="admin123"><br><br>';
        echo '</div>';
        echo '<input type="submit" value="Continuar" name="submit">';
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}

if (isset($db_type)) {
    if ($db_type === 'mysql' && isset($db_host) && isset($db_user)) {
        $conn = new PDO("mysql:host=$db_host;", $db_user, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
        $conn->exec($sql);
        echo "Banco $db_name Criado";
    } elseif ($db_type === 'sqlite') {
        $db_name = 'Database.db';
        $db_file = __DIR__ . '/backend/Database/' . $db_name;
        if (!file_exists($db_file)) {
            $conn = new PDO('sqlite:' . $db_file);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Banco SQLite $db_name Criado";
        }
    }

    $config_file = 'backend/Database/config.php';
    $config_content = "<?php\n";
    $config_content .= "define('DB_TYPE', '$db_type');\n";
    if ($db_type === 'mysql') {
        $config_content .= "define('DB_HOST', '$db_host');\n";
        $config_content .= "define('DB_NAME', '$db_name');\n";
        $config_content .= "define('DB_USER', '$db_user');\n";
        $config_content .= "define('DB_PASSWORD', '$db_password');\n";
    } elseif ($db_type === 'sqlite') {
        $config_content .= "define('DB_NAME', '$db_name');\n";
    }
    $config_content .= "\n";
    file_put_contents($config_file, $config_content, LOCK_EX);
    if (!file_exists($config_file)) {
        die('Erro ao escrever o arquivo');
    }
    if ($db_type === 'mysql') {
        header('Location: migrate.php#mysql');
        exit;
    }else{
        header('Location: migrate.php#lite');
        exit;
    }
}
?>

<script>
    document.getElementById('db_type').addEventListener('change', function() {
        var dbType = this.value;
        var mysqlFields = document.getElementById('mysql_fields');
        if (dbType === 'mysql') {
            mysqlFields.style.display = 'block';
        } else {
            mysqlFields.style.display = 'none';
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        var dbType = document.getElementById('db_type').value;
        var mysqlFields = document.getElementById('mysql_fields');
        if (dbType === 'sqlite') {
            mysqlFields.style.display = 'none';
        }
    });
</script>
