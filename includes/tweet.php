<?php

class tweet {

	var $id;
	var $fecha;
	var $texto;
	var $textoConFormato;

	var $tweetFormateado;

	var $fuente;

	var $respuestas;
	var $retweets;
	var $favoritos;

	var $idioma;

	var $resp_idUsuario;
	var $resp_nickUsuario;

	var $coord_long;
	var $coord_lat;

	var $idUsuario;
	var $nombreUsuario;
	var $nick;
	var $imgUsuario;
	var $sentimiento;

	var $entidades;

	function __construct( $datos ){

		$this->id = $datos['id'];
		$this->fecha = $datos['fecha'];
		$this->texto = $datos['texto'];
		$this->textoConFormato = $datos['texto'];
		$this->tweetFormateado = false;

		$this->fuente = $datos['fuente'];

		$this->respuestas = $datos['respuestas'];
		$this->retweets = $datos['retweets'];
		$this->favoritos = $datos['favoritos'];

		$this->idioma = $datos['idioma'];

		$this->resp_idUsuario = $datos['resp_idUsuario'];
		$this->resp_nickUsuario = $datos['resp_nickUsuario'];

		$this->coord_long = $datos['coord_long'];
		$this->coord_lat = $datos['coord_lat'];

		$this->idUsuario = $datos['idUsuario'];
		$this->nombreUsuario = $datos['nombreUsuario'];
		$this->nick = $datos['nick'];
		$this->imgUsuario = $datos['imgUsuario'];

		$this->sentimiento = $datos['sentimiento'];
	}

	function guardaEntidad( $clase, $objeto ){

		$this->entidades[$clase][] = $objeto;
	}

	function tweetFormateado(){

		if ( !$this->tweetFormateado ){

			$this->formateaTexto();

			$this->tweetFormateado = '<div>';

				$this->tweetFormateado .= '<div class="imagenUsuario"><img src="'.$this->imgUsuario.'"></div>';

				$this->tweetFormateado .= '<div class="textoTweet">';

					if ( $this->sentimiento ){
						$this->tweetFormateado .= '<img class="sentimiento" src="includes/iconos/'.$this->sentimiento.'.svg">';
					}

					$this->tweetFormateado .= '<span class="nombreUsuario"><a href="https://twitter.com/'.$this->nombreUsuario.'" target="_blank">'.$this->nombreUsuario.'</a> @'.$this->nick.' - '.$this->diferenciaHora().'</span>';

					if ( $this->resp_idUsuario ){
						$this->tweetFormateado .= 'En respuesta a ';
					}
				$this->tweetFormateado .= $this->textoConFormato;

				$this->tweetFormateado .= '</div>';

			$this->tweetFormateado .= '</div>';
		}

		return $this->tweetFormateado;
	}


	function formateaTexto(){

		$formato = false;

		foreach ( $this->entidades as $clase => $lista) {
			
			foreach ($lista as $obj) {
				
				$sustituciones[$obj->posF] = $obj;
			}
		}

		krsort($sustituciones);

		foreach ($sustituciones as $entidad) {
			
			$this->insertaEntidad( $entidad );
		}
	}

	private function insertaEntidad( $entidad ){

		$principio = mb_substr( $this->textoConFormato, 0, $entidad->posI );
		$final = mb_substr( $this->textoConFormato, $entidad->posF );

		$this->textoConFormato = $principio.$entidad->formato.$final;
	}

	private function diferenciaHora(){

		$ahora = date("Y-m-d H:i:s");

		if ( (strtotime('-1 day', strtotime($ahora) )) < strtotime($this->fecha) ){

			// Muestro la diferencia con fecha actual
			$fechaActual = new DateTime( $ahora );
			$fechaTweet = new DateTime( $this->fecha );
			$diferencia = $fechaActual->diff($fechaTweet);

			if ( $diferencia->h > 0 ){

				return $diferencia->h.' h';

			} elseif ( $diferencia->i > 0 ) {
				
				return $diferencia->i.' min';

			} else {
				
				return $diferencia->s.' seg';
			}

		} else {

			// Muestro la fecha del tweet

			return date ( 'j M' , strtotime($this->fecha) );
		}
	}

	function haceHoras(){

		$ahora = date("Y-m-d H:i:s");

		if ( (strtotime('-1 day', strtotime($ahora) )) < strtotime($this->fecha) ){

			// Muestro la diferencia con fecha actual
			$fechaActual = new DateTime( $ahora );
			$fechaTweet = new DateTime( $this->fecha );
			$diferencia = $fechaActual->diff($fechaTweet);

			if ( $diferencia->h > 0 ){

				return $diferencia->h+1;

			} else {
				
				return 1;
			}

		} else {

			return 25;
		}
	}

	function getHashtags(){

		foreach ( $this->entidades['hashtags'] as $vals ) {
			
			$datos[] = $vals->texto;
		}

		return $datos;
	}

	function getMenciones(){

		foreach ( $this->entidades['menciones'] as $vals ) {
			
			$datos[] = $vals->nick;
		}

		return $datos;
	}
}


class hashtags{

	var $texto;
	var $posI;
	var $posF;
	var $formato;

	function __construct( $datos ){

		$this->texto = $datos['texto'];
		$this->posI = $datos['posI'];
		$this->posF = $datos['posF'];
		$this->formato = '<a href="https://twitter.com/hashtag/'.$datos['texto'].'?src=hash" target="_blank" class="hashtag">#'.$datos['texto'].'</a> ';
	}

}

class menciones{

	var $nombre;
	var $nick;
	var $posI;
	var $posF;
	var $formato;

	function __construct( $datos ){

		$this->nombre = $datos['nombre'];
		$this->nick = $datos['nick'];
		$this->posI = $datos['posI'];
		$this->posF = $datos['posF'];
		$this->formato = '<a href="https://twitter.com/'.$datos['nick'].'" target="_blank" class="mencion">@'.$datos['nick'].'</a> ';
	}
}

class urls{

	var $url_mostrar;
	var $url;
	var $posI;
	var $posF;
	var $formato;

	function __construct( $datos ){

		$this->url_mostrar = $datos['url_mostrar'];
		$this->url = $datos['url'];
		$this->posI = $datos['posI'];
		$this->posF = $datos['posF'];
		$this->formato = '<a class="url" href="'.$datos['url'].'" target="_blank">'.$datos['url_mostrar'].'</a> ';
	}
}

class media{

	var $tipo;
	var $url_mostrar;
	var $url;
	var $ancho;
	var $alto;
	var $posI;
	var $posF;
	var $formato;

	function __construct( $datos ){

		$this->tipo = $datos['tipo'];
		$this->url_mostrar = $datos['url_mostrar'];
		$this->url = $datos['url'];
		$this->ancho = $datos['ancho'];
		$this->alto = $datos['alto'];
		$this->posI = $datos['posI'];
		$this->posF = $datos['posF'];
		$this->formato = '<div class="media"><img src="'.$datos['url'].'" width="'.$datos['ancho'].'" height="'.$datos['alto'].'"></div>';

	}
}

?>