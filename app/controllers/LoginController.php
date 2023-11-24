<?php
require_once './models/Cuenta.php';
//require_once './middlewares/autenticadorMW.php';
require_once './JWT/AuthJWT.php';
class LoginController{

    public function LoginController($request, $response, $args){
        $parametros = $request->getParsedBody();

        $nroCuenta = $parametros['nroCuenta'];
        $contrasena = $parametros['contrasena'];
        $cuenta = null;
        $cuenta = self::ObtenerCuentaPornroCuentaPwd($nroCuenta, $contrasena);

        if($cuenta !== null){ 
            $datos = array('nroCuenta' => $cuenta->nroCuenta, 'nroDocumento'=> $cuenta->nroDocumento, 'mail'=> $cuenta->mail, 'tipoCuenta'=> $cuenta->tipoCuenta, 'saldo' => $cuenta->saldo);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error: ' => 'Cuenta / contrasena no coinciden'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    function ObtenerCuentaPornroCuentaPwd($nroCuenta, $contrasena){
        $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT nroCuenta,nombre,apellido,tipoDocumento,nroDocumento,mail,contrasena,tipoCuenta,moneda,saldo,estado from cuentas where nroCuenta = :nroCuenta AND contrasena = :contrasena AND estado = 'activa'");
        $consulta->bindParam(":nroCuenta", $nroCuenta);
        $consulta->bindParam(":contrasena", $contrasena);
        $consulta->execute();
        $usuario = $consulta->fetchObject();
        return $usuario;
    }
}
?>