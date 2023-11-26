<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

//valida que exista en deposito en la talba de depositos
class AuthTipoCuentaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $TipoCuenta = $parametros["tipoCuenta"];

        if(self::ValidarTipoCuenta($TipoCuenta) !== false){
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "Tipo de Cuenta no permitido"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public static function ValidarTipoCuenta($tipo) {
        $retorno = false;

        $tipoCAUSD= "CAU" . "$" . "S";
        $tipoCCUSS= "CCU" . "$" . "S";

        if($tipo == "CA$" || $tipo == $tipoCAUSD || $tipo == "CC$" || $tipo == $tipoCCUSS || (!empty($tipo) && !trim($tipo) === "")) {
            $retorno = true;
        }

        return $retorno;
    }
}