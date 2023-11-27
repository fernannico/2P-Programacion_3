<?php
require_once './models/Cuenta.php';
//require_once './middlewares/autenticadorMW.php';
require_once './JWT/AuthJWT.php';
class LoginController{

    public function LoginController($request, $response, $args){
        $parametros = $request->getParsedBody();

        // $rol = 
        $mail = $parametros['mail'];
        $contrasena = $parametros['contrasena'];
        $usuario = null;
        $usuario = self::ObtenerUsuarioPorMailPwd($mail, $contrasena);
        // var_dump($usuario);
        if($usuario !== null){ 
            // if($usuario->estado != 'inactiva'){
            $datos = array('mail' => $usuario->mail, 'rol'=> $usuario->rol);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
            // }else{
            //     $payload = json_encode(array('error: ' => 'Cuenta inactivada'));
            // }
        } else {
            $payload = json_encode(array('error: ' => 'mail / contrasena no coinciden'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    function ObtenerUsuarioPorMailPwd($mail, $contrasena){
        $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT mail, contrasena, rol from usuario where mail = :mail AND contrasena = :contrasena");
        $consulta->bindParam(":mail", $mail);
        $consulta->bindParam(":contrasena", $contrasena);
        $consulta->execute();
        $usuario = $consulta->fetchObject();
        return $usuario;
    }
}
?>