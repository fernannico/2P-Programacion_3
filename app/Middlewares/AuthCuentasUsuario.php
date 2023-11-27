<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Cuenta.php';

class AuthCuentasUsuario
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        // try {
            $header = $request->getHeaderLine('Authorization');
            $token = '';
            if(!empty($header)) {
                $response = new Response();
                $token = trim(explode("Bearer", $header)[1]);
                $datos = AutentificadorJWT::ObtenerData($token);
                
                $parametros = null;
                $parametros = $request->getQueryParams();
                if($parametros == null){
                    $parametros = $request->getParsedBody();
                }else{
                    $response->getBody()->write(json_encode(["mensaje" => "faltan parametros"]));
                }
                
                if($parametros !== null && isset($parametros['nroCuenta'])){
                    $nroCuentaValidar = $parametros['nroCuenta'];
                    $nroDocumentoLogin = $datos->nroDocumento;
                    $listaCuentas = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumentoLogin);
                    $cuentaEsDelUsuario = false;
                    foreach($listaCuentas as $cuentaDelUsuario){
                        if($cuentaDelUsuario->nroCuenta == $nroCuentaValidar){
                            $response = $handler->handle($request);
                            $cuentaEsDelUsuario = true;
                            break;
                        }
                    }
                    if(!$cuentaEsDelUsuario){
                        $response->getBody()->write(json_encode(["Error" => "El nro de cuenta ingresado no le corresponde al usuario logeado"]));
                    }
                }
            }

        return $response->withHeader('Content-Type', 'application/json');
    }
}