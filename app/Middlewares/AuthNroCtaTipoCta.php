<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

//valida que exista en deposito en la talba de depositos
class AuthNroCtaTipoCta
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $response = new Response();
        $parametros = null;
        $parametros = $request->getQueryParams();
        if($parametros == null){
            $parametros = $request->getParsedBody();
        }
        if($parametros !== null){
            $TipoCuenta = $parametros["tipoCuenta"];
            $nroCuenta = $parametros["nroCuenta"];

            $cuenta = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
            if($cuenta->tipoCuenta == $TipoCuenta){
                $response = $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["mensaje" => "Tipo de Cuenta no coincide con el nro de cuenta"]));
            }
        }else{
            $response->getBody()->write(json_encode(["mensaje" => "faltan parametros"]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}