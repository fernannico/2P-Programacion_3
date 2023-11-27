<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

//valida que exista en deposito en la talba de depositos
class AuthRolMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $response = new Response();
        $parametros = null;

        $parametros = $request->getQueryParams();
        if($parametros == null){
            $parametros = $request->getParsedBody();
        }else{
            $response->getBody()->write(json_encode(["mensaje" => "faltan parametros"]));
        }

        // var_dump($parametros["rol"]);
        if($parametros !== null){
            $rol = $parametros["rol"];
    
            if($rol == 'supervisor' || $rol == "cajero" || $rol = 'operador'){
                $response = $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["mensaje" => "Tipo de rol no permitido"]));
            }
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}