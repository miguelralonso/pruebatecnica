<?php


class Client {

    function __construct($token, $base_endpoint=Config::DEFAULT_BASE_ENDPOINT) {
        $this->token = $token;
        $this->classifiers = new Classification($token, $base_endpoint);
        $this->extractors = new Extraction($token, $base_endpoint);
        $this->pipelines = new Pipelines($token, $base_endpoint);
    }

}
?>
