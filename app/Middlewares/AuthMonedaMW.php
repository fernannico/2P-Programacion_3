<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

//valida que exista en deposito en la talba de depositos
class AuthMonedaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $response = new Response();
        $parametros = null;
        $parametros = $request->getQueryParams();

        // var_dump($parametros["moneda"]);
        if($parametros !== null){
            $moneda = $parametros["moneda"];
            $dolar = "U"."$"."D";
    
            if($moneda == $dolar || $moneda == "$"){
                $response = $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["mensaje" => "Tipo de moeda no permitido<br> ingresar $ o ".$dolar]));
            }
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}