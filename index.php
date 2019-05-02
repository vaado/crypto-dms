<?php

require_once __DIR__.'/src/FileManager.php';

header('Content-Type: text/plain; charset=utf-8');

$fileManager = new FileManager();
$fileManager->resolveRequest($_POST);
exit;


