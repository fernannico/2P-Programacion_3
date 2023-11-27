<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthDniNombreAellido {
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();

        $nroDocumento = $parametros['nroDocumento'] ?? null;
        $nombre = $parametros['nombre'] ?? null;
        $apellido = $parametros['apellido'] ?? null;
        
        $listaDnis = Cuenta::ObtenerListaDnis();
        $dniExiste = false;
        foreach($listaDnis as $dni)
        {
            if($dni == $nroDocumento){
                $dniExiste = true;
                break;
            }
        }

        $listaCuentasDni = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumento);
        $cuenta = $listaCuentasDni[0];

        if($dniExiste == false){
            $response = $handler->handle($request);
        }elseif($dniExiste == true && $apellido == $cuenta->apellido && $nombre == $cuenta->nombre){
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "dni ya cargado y no coincide con ese nombre"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}