<?php
require_once './models/Logger.php';
//require_once './middlewares/autenticadorMW.php';
require_once './JWT/AuthJWT.php';
class LoggerController{

    public function LoggerController($request, $response, $args){
        if(Logger::ObtenerLoggerComoPDF()){
            $payload = json_encode(array('mensjae: ' => 'txt creado'));
        }else {
            $payload = json_encode(array('error: ' => 'txt no creado'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }

}