<?php


error_reporting(0);
require_once('includes/configuracion.php'); // Archivo de configuración
require_once('includes/APITwitter.php'); // Librería de conexión API Twitter
require_once('includes/tweet.php'); // Clases de Tweet y entidades
require 'includes/monkey-learn/SleepRequests.php';
require 'includes/monkey-learn/Classification.php';
require 'includes/monkey-learn/Client.php';
require 'includes/monkey-learn/Config.php';
require 'includes/monkey-learn/Extraction.php';
require 'includes/monkey-learn/HandleErrors.php';
require 'includes/monkey-learn/MonkeyLearnException.php';
require 'includes/monkey-learn/MonkeyLearnResponse.php';
require 'includes/monkey-learn/Pipelines.php';


// Función que inserta los datos de Tweets y entidades en la base de datos
function insertaDatos( $datos ){
	global $conexion;

	foreach ($datos as $tabla => $valores) {

		$query = "";
		unset($lineasQuery);

		$query = "INSERT INTO $tabla (".implode(',', array_keys($valores[0])).") VALUES ";

		foreach ($valores as $datos) {

			$lineasQuery[] = "('".implode("','", $datos)."')";			
		}

		if ( count($lineasQuery) > 0 ){

			$conexion->exec($query.implode(',', $lineasQuery).';');
		}
	}
}


// Consulto últimos (hasta 100) Tweets del Hashtag #farina. Si ya hay Tweets guardados, consulto a partir del último
$stmt = $conexion->prepare("SELECT max(id) as ultimo FROM tweets");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
$ultimoId = $stmt->fetch(); // Último ID de Tweet en la base de datos
if ( $ultimoId['ultimo'] ){ $desde = '&since_id='.$ultimoId['ultimo']; }

$url = 'https://api.twitter.com/1.1/search/tweets.json';
$campos = '?q=#farina -filter:retweets&result_type=recent&count=100&tweet_mode=extended'.$desde;
$metodo = 'GET';
$twitter = new TwitterAPIExchange($parametrosAPITwitter);
$json =  $twitter->setGetfield($campos)->buildOauth($url, $metodo)->performRequest();
$datosTwitter = json_decode($json, true);

// Clasifico los datos relevantes de los Tweets consultados
foreach ( $datosTwitter['statuses'] as $i => $status ) {
    
	$idTweet = $status['id_str'];
	$fecha = date("Y-m-d H:i:s", strtotime($status['created_at']));

	$datos['tweets'][] = array(

		'id' => $idTweet, 
		'fecha' => $fecha,
		'texto' => addslashes($status['full_text']),
		'fuente' => $status['source'],
		'respuestas' => $status['reply_count'],
		'retweets' => $status['retweet_count'],
		'favoritos' => $status['favorite_count'],
		'idioma' => $status['lang'],
		'resp_idUsuario' => $status['in_reply_to_user_id_str'],
		'resp_nickUsuario' => $status['in_reply_to_screen_name'],
		'coord_long' => $status['coordinates']['coordinates'][0],
		'coord_lat' => $status['coordinates']['coordinates'][1],
		'idUsuario' => $status['user']['id_str'],
		'nombreUsuario' => $status['user']['name'],
		'nick' => $status['user']['screen_name'],
		'imgUsuario' => $status['user']['profile_image_url'],
		'sentimiento' => ''
	);

	foreach ( $status['entities']['hashtags'] as $vals ) {
		
		$datos['hashtags'][] = array(

			'idTweet' => $idTweet,
			'texto' => $vals['text'],
			'posI' => $vals['indices'][0],
			'posF' => $vals['indices'][1]
		);
	};

	foreach ( $status['entities']['user_mentions'] as $vals ) {
		
		$datos['menciones'][] = array(

			'idTweet' => $idTweet,
			'nombre' => $vals['name'],
			'nick' => $vals['screen_name'],
			'posI' => $vals['indices'][0],
			'posF' => $vals['indices'][1]
		);
	};

	foreach ( $status['entities']['urls'] as $vals ) {
		
		$datos['urls'][] = array(

			'idTweet' => $idTweet,
			'url_mostrar' => $vals['display_url'],
			'url' => $vals['expanded_url'],
			'posI' => $vals['indices'][0],
			'posF' => $vals['indices'][1]
		);
	};

	foreach ( $status['entities']['media'] as $vals ) {
		
		$datos['media'][] = array(

			'idTweet' => $idTweet,
			'tipo' => $vals['type'],
			'url_mostrar' => $vals['display_url'],
			'url' => $vals['media_url'],
			'ancho' => $vals['sizes']['small']['w'],
			'alto' => $vals['sizes']['small']['h'],
			'posI' => $vals['indices'][0],
			'posF' => $vals['indices'][1]
		);
	};
}

// Si hay Tweets nuevos, consulto los análisis de sentimientos, única consulta para todos los Tweets recuperados

if ( count($datos['tweets']) > 0 ){

	$monkeyl = new Client($APITokenMonkeyLearn);
	$module_id = 'cl_u9PRHNzf';

	foreach ( $datos['tweets'] as $val ) {
		
		$text_list[] = $val['texto'];
	}

	$resultadoSentimientos = $monkeyl->classifiers->classify($module_id, $text_list, false);

	foreach ( $resultadoSentimientos->result as $id => $res ) {
		
		$datos['tweets'][$id]['sentimiento'] = $res[0]['label'];
	}
}


// Guardo los datos recogidos de los tweets en la base de datos
insertaDatos($datos);


// Consulto los datos de los Tweets en la base de datos para mostrar y los guardo en array
$stmt = $conexion->prepare("SELECT * FROM tweets ORDER BY fecha DESC LIMIT 300");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

while ( $datos = $stmt->fetch() ) {

	$listadoTweets[$datos["id"]] = new Tweet( $datos );
}

// Consulto los datos de las entidades en la base de datos para mostrar y los agrego a sus correspondientes Tweets
$arrayEntities = array('hashtags','menciones','urls','media');

foreach ($arrayEntities as $entity) {
	
	$stmt = $conexion->prepare("SELECT * FROM ".$entity." WHERE idTweet in ('".implode("','", array_keys($listadoTweets))."') ORDER BY posF DESC");
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();

	while ( $datos = $stmt->fetch() ) {

		$tweet = $listadoTweets[$datos["idTweet"]];
		$nuevaEntidad = new $entity( $datos );
		$tweet->guardaEntidad( $entity, $nuevaEntidad );
	}

}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Prueba Tecnica</title>

<link href="https://fonts.googleapis.com/css?family=Montserrat:400,600" rel="stylesheet">
<link href="includes/estilo.css" rel="stylesheet"> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="includes/Chart.bundle.min.js"></script>


<script type="text/javascript">

	$( document ).ready(function() {

		$( "#menuGraficas > span" ).on( "click", function() {

			$( "#menuGraficas > span" ).removeClass("activa");
			$(this).addClass("activa");
			
			$( "#graficoHoras, #graficoHashtags, #graficoMenciones, #graficoFuentes" ).addClass("oculta");
			$("#"+$(this).data('grafica')).removeClass("oculta");
		});

	});

</script>

</head>

<body>

<header><div>Seguimiento del hashtag #farina</div></header>

<nav>
	<div>
		<a href="seguimiento-tweets.php" id="cargarMas">CARGAR MÁS TWEETS</a>
	</div>
</nav>

<div id="contenedor">
<div id="listado-tweets">
<div>Total de Tweets cargados: <?= count($listadoTweets) ?></div>

	<?php

		for ($i=0; $i < 25; $i++) { 
			$horas[$i] = 0;
		}

		// Muestro Tweets y preparo datos para las gráficas
		foreach ( $listadoTweets as $idTweet => $tweet ) {
			
			echo $tweet->tweetFormateado();

			// Preparo datos para gráfica de fuentes de tweet
			$fuentes[strip_tags($tweet->fuente)] = $fuentes[strip_tags($tweet->fuente)] + 1;
			arsort($fuentes);


			// Preparo datos para gráfica de actividad últimas 24 horas
			if ( $tweet->haceHoras() < 25 ){

				$horas[ $tweet->haceHoras() ]++;
			}
			
			krsort($horas);

			// Preparo datos para gráfica de hashtags
			$hashtagsTweet = $tweet->getHashtags();

			foreach ( $hashtagsTweet as $texto ) {
				
				$hashtags[$texto]++;
			}

			arsort($hashtags);
			$mostrarHashtags = array_slice($hashtags, 0, 25);

			// Preparo datos para gráfica de TOP 10 menciones
			$mencionesTweet = $tweet->getMenciones();

			foreach ( $mencionesTweet as $texto ) {
				
				$menciones[$texto]++;
			}

			arsort($menciones);
			$mostrarMenciones = array_slice($menciones, 0, 10);
		}

	?>

</div>

<div id="graficas">
	<div id="menuGraficas">
		<span id="verHoras" class="activa" data-grafica="graficoHoras">ACTIVIDAD</span>
		<span id="verHashtags" data-grafica="graficoHashtags">HASHTAGS</span>
		<span id="verMenciones" data-grafica="graficoMenciones">MENCIONES</span>
		<span id="verFuentes" data-grafica="graficoFuentes">FUENTES</span>
	</div>
	<canvas id="graficoHoras" width="580" height="580"></canvas>
	<canvas id="graficoHashtags" class="oculta" width="580" height="580"></canvas>
	<canvas id="graficoMenciones" class="oculta" width="580" height="580"></canvas>
	<canvas id="graficoFuentes" class="oculta" width="580" height="580"></canvas>
</div>
<script>

// Graficas

Chart.defaults.global.defaultFontFamily = "'Montserrat', 'Helvetica', 'Arial', sans-serif";
Chart.defaults.global.defaultFontColor = '#555';


var graficoFuentes = document.getElementById("graficoFuentes");
var graficoFuentes = new Chart(graficoFuentes, {
    type: 'doughnut',
    data: {
        labels: [<?= '"'.implode('","', array_keys($fuentes)). '"' ?>],
        datasets: [{
            data: [<?= implode(',', $fuentes) ?>],
            backgroundColor: [
                '#CC0000','#359','#FFCC00','#6DD900','#B200B2','#8C4600'
            ]
        }]
    },
    options: {

    	legend: {
            position: 'bottom'
        },
    	layout: {
            padding: {
                left: 0
            }
        }
    }
});


var graficoHashtags = document.getElementById("graficoHashtags");
var graficoHashtags = new Chart(graficoHashtags, {
    type: 'pie',
    data: {
        labels: [<?= '"#'.implode('","#', array_keys($mostrarHashtags)). '"' ?>],
        datasets: [{
            data: [<?= implode(',', $mostrarHashtags) ?>],
            backgroundColor: [
                '#CC0000','#359','#FFCC00','#6DD900','#B200B2','#8C4600'
            ]
        }]
    },
    options: {

    	legend: {
            position: 'bottom'
        },
    	layout: {
            padding: {
                left: 15,
                right: 15,
                top: 15,
                bottom: 15
            }
        }
    }
});

var graficoMenciones = document.getElementById("graficoMenciones");
var graficoMenciones = new Chart(graficoMenciones, {
    type: 'horizontalBar',
    data: {
        labels: [<?= '"@'.implode('","@', array_keys($mostrarMenciones)). '"' ?>],
        datasets: [{
        	label: 'Top 10 menciones',
            data: [<?= implode(',', $mostrarMenciones) ?>],
            backgroundColor: [
                '#CC0000','#359','#FFCC00','#6DD900','#B200B2','#8C4600'
            ]
        }]
    },
    options: {
        scales: {
            xAxes: [{
                ticks: {
                    beginAtZero:true
                },
                gridLines: {
	                offsetGridLines: false
	            }
            }]
        },
        layout: {
            padding: {
                left: 15,
                right: 15,
                top: 15,
                bottom: 15
            }
        }
    }
});

var graficoHoras = document.getElementById("graficoHoras");
var graficoHoras = new Chart(graficoHoras, {
    type: 'line',
    data: {
        labels: [<?= '"'.implode('H","', array_keys($horas)). '"' ?>],
        datasets: [{
        	label: 'Actividad últimas 24 horas',
        	borderColor: '#1DA1F2',
        	borderWidth: 2,
        	fill: false,
        	pointRadius: 0,
            data: [<?= implode(',', $horas) ?>]
        }]
    },
    options: {
        elements: {
            line: {
                tension: 0.2,
            }
        }
    }
});
</script>
</div>

</body>
</html> 