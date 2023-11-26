<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Cuenta.php';

//valida que exista en nroCuenta en la talba de cuentas
class AuthCuentaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $nroCuenta = $parametros["nroCuenta"];

        if(Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta) !== false){
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "cuenta no encontrada"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}