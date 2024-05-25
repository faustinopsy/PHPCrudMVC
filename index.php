<?php

if (file_exists('index.html')) {
    header('Location: install.php');
    exit;
} else {
    header('Location: index.html');
    exit;
}

