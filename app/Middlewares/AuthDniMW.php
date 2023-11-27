<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Cuenta.php';

//valida que exista en nroCuenta en la talba de cuentas -> VER DE HACERLO POR USUARIOS
class AuthDniMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getQueryParams();
        $nroDocumento = $parametros["nroDocumento"];
        $dniExiste = false;

        $listaDnis = Cuenta::ObtenerListaDnis();
        foreach($listaDnis as $dni){
            if($dni == $nroDocumento){
                $dniExiste = true;
                break;
            }
        }
        if($dniExiste){
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "dni no encontrado"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}