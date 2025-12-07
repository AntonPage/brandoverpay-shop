<?php
require_once 'config.php';

// Очищаємо сесію
session_unset();
session_destroy();

// Редірект на головну
redirect('index.php');
?>