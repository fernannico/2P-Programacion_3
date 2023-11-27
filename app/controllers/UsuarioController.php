<?php


require_once './models/Usuario.php';
require_once './models/Cuenta.php';
require_once './models/Retiro.php';
require_once './models/Deposito.php';
// require_once './interfaces/IApiUsable.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class UsuarioController extends Cuenta /*implements IApiUsable*/
{
    
    public function CargarCuenta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $rol = $parametros['rol'];
        $contrasena = $parametros['contrasena'];
        $mail = $parametros['mail'];

        $usuario = new Usuario();
        $usuario->mail = $mail;
        $usuario->contrasena = $contrasena;
        $usuario->rol = $rol;
        $usuario->crearUsuario();
        $payload = json_encode(array("mensaje" => "usuario creado con exito<BR>"));
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');

    }
}