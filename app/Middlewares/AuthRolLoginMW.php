<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthRolLoginMW
{
    public $rol;
    public function __construct($rol)
    {
        $this->rol = $rol;
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);

        if($data->rol === $this->rol){
            $request = $request->withAttribute('datosToken', $data);
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: usuario no autorizado'));
            $response->getBody()->write($payload);
        }
            
        return $response->withHeader('Content-Type', 'application/json');
    
    }
}