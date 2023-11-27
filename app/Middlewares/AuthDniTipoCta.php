<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthDniTipoCta {
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();

        $nroDocumento = $parametros['nroDocumento'] ?? null;
        $tipoCuenta = $parametros['tipoCuenta'] ?? null;

        // Verificar si el DNI y el tipo de cuenta ya existen en la base de datos
        $existeCuenta = Cuenta::ObtenerCuentaPorDniTipo($nroDocumento, $tipoCuenta);

        // Agregar una clave al cuerpo del request para indicar si la cuenta es nueva o existe
        // $parametros['estadoCuenta'] = $existeCuenta ? 'actualizar' : 'nueva';
        if($existeCuenta){
            $parametros['estadoCuenta'] = 'actualizar';
        }else{
            $parametros['estadoCuenta'] = 'nueva';
        }
        $request = $request->withParsedBody($parametros);
        
        $response =$handler->handle($request);
        // Permitir que el controlador se ejecute
        return $response;
    }
}
