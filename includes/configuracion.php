<?php


// Datos de conexión a la base de datos
$conexion = new PDO('mysql:host=xxxxxxxxx;dbname=elogia', 'root', '');

// Datos de conexión con la API de twitter
$parametrosAPITwitter = array(
    'oauth_access_token' => "xxxxxxxxxxxxxxxx",
    'oauth_access_token_secret' => "xxxxxxxxxxxxxxxx",
    'consumer_key' => "xxxxxxxxxxxxxxxx",
    'consumer_secret' => "xxxxxxxxxxxxxxxx"
);

$APITokenMonkeyLearn = 'xxxxxxxxxxxxxxxx';

?>