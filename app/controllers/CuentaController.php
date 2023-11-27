<?php


require_once './models/Cuenta.php';
require_once './models/Retiro.php';
require_once './models/Deposito.php';
// require_once './interfaces/IApiUsable.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class CuentaController extends Cuenta /*implements IApiUsable*/
{
    
    public function CargarCuenta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $nroDocumento = $parametros['nroDocumento'];
        $tipoCuenta = $parametros['tipoCuenta'];
        if (isset($parametros['saldo'])) {
            $saldo = $parametros['saldo'];
        }else{
            $saldo = 0;
        }     
        
        $estadoCuenta = $parametros['estadoCuenta'];
        if ($estadoCuenta === 'actualizar') {
            // actualizar una cuenta existente
            $cuentaActualizar = Cuenta::ObtenerCuentaPorDniTipo($nroDocumento,$tipoCuenta);
            if($cuentaActualizar){
                Cuenta::ActualizarSaldo($cuentaActualizar->nroCuenta,$saldo);
                // Cuenta::ActualizarEstado($cuentaActualizar->nroCuenta,"activa");
                $cuentaActualizar->saldo += $saldo;
                // $cuentaActualizar->estado = "activa";
                $payload = json_encode(array("mensaje" => "Cuenta actualizado con exito <br>" . $cuentaActualizar->__toString()));
            }
        }elseif ($estadoCuenta === 'nueva') {
            // crear una nueva cuenta
            $nombre = $parametros['nombre'];
            $apellido = $parametros['apellido'];
            $mail = $parametros['mail'];
            $contrasena = $parametros['contrasena'];
            $nombreImagen = $_FILES['imagen']['tmp_name'];
            $directorioImagenesAlta = "ImagenesDeCuentas/2023/";
            $moneda = Cuenta::ObtenerMonedaPorCuenta($tipoCuenta);
               
            // Creamos el cuenta
            $cuenta = new Cuenta();
            $cuenta->nombre = $nombre;
            $cuenta->apellido = $apellido;
            $cuenta->nroDocumento = $nroDocumento;
            $cuenta->mail = $mail;
            $cuenta->contrasena = $contrasena;
            $cuenta->tipoCuenta = $tipoCuenta;
            $cuenta->moneda = $moneda;
            $ultimoNroCuenta = (int)Cuenta::ObtenerUltimoNroCuenta();
            $nombre_imagen = ($ultimoNroCuenta+1) . $tipoCuenta . ".jpg";       
            $cuenta->imagen = $nombre_imagen;
            $cuenta->saldo = $saldo;
            $cuenta->crearCuenta();

            $cuenta->GuardarImagen($nombreImagen,$directorioImagenesAlta);
            $cuentaNueva = Cuenta::ObtenerCuentaPorNroCuenta($ultimoNroCuenta+1);

            $payload = json_encode(array("mensaje" => "Cuenta creado con exito<BR>" . $cuentaNueva->__toString()));
        }
    
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    ///Se ingresa Tipo y Nro. de Cuenta, si coincide con algún registro del archivo banco.json, retornar la moneda/s y saldo de la cuenta/s.
    public function ConsultarCuentaController($request, $response, $args)
    {
        // $header = $request->getHeaderLine('Authorization');
        // $token = trim(explode("Bearer", $header)[1]);
        
        // $data = AutentificadorJWT::ObtenerData($token);
        // $nroCuenta = $data->nroCuenta;
        // $tipoCuenta = $data->tipoCuenta;
        
        // if(isset($nroCuenta) & $nroCuenta !== ""){
        $parametros = $request->getParsedBody();
        if(!isset($parametros['nroCuenta']) || !isset($parametros['tipoCuenta']) || empty($parametros['nroCuenta']) || empty($parametros['tipoCuenta'] ))
        {
            $response->getBody()->write(json_encode(["error" => "faltan parametros"]));
        }else{
            $nroCuenta = $parametros['nroCuenta'];
            $tipoCuenta = $parametros['tipoCuenta'];
    
            $cuenta = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
            if ($cuenta) {
                $tipoCuentaBuscada = $cuenta->tipoCuenta;
                if($tipoCuenta == $tipoCuentaBuscada){
                    $consulta = [
                        "saldo" => $cuenta->saldo,
                        "moneda" => $cuenta->moneda
                    ];    
                    $payload = json_encode($consulta);
                    $response->getBody()->write($payload);
                }else{
                    $response->getBody()->write(json_encode(["error" => "Cuenta encontrada pero no con ese tipo de cuenta"]));
                }
            } else {
                $response->getBody()->write(json_encode(["error" => "Cuenta no encontrado"]));
            }
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ConsultarOperacionesController($request, $response, $args)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $nroDocumento = $data->nroDocumento;
        // $queryParams = $request->getQueryParams();
        // $nroDocumento = $queryParams['nroDocumento'];

        $listaDepositos = Deposito::ObtenerTodosDepositos();
        $listaRetiros = Retiro::ObtenerTodosRetiros();
        $listaCuentas = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumento);//tengo todas las cuentas con el mismo nro de doc

        $listaDepositosFiltrada = [];
        $listaRetirosFiltrada = [];

        foreach($listaCuentas as $cuenta)
        {
            foreach($listaDepositos as $deposito){
                if($deposito->nroCuenta == $cuenta->nroCuenta){
                    $listaDepositosFiltrada[] = $deposito;
                }
            }
            foreach($listaRetiros as $retiro){
                if($retiro->nroCuenta == $cuenta->nroCuenta){
                    $listaRetirosFiltrada[] = $retiro;
                }
            }
        }

        $payload = json_encode([
            "lista_Depositos" => $listaDepositosFiltrada,
            "lista_Retiros" => $listaRetirosFiltrada
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function BajarCuentaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $nroCuenta = $parametros["nroCuenta"];
        $estado = "inactiva";

        if(Cuenta::CambiarEstadoCuenta($nroCuenta, $estado)){
            $cuenta = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
            $nombre_imagen = $nroCuenta . $cuenta->tipoCuenta . ".jpg";
            $carpetaOrigen = "ImagenesDeCuentas/2023/";
            $carpetaDestino = "ImagenesBackupCuentas/2023/";        
            Cuenta::MoverImagen($nombre_imagen,$carpetaOrigen,$carpetaDestino);
            $retorno = json_encode(array("mensaje" => "estado de la cuenta cambiado: " . $estado));
        }else{
            $retorno = json_encode(array("mensaje" => "estado no cambiado"));
        }
        $response->getBody()->write($retorno);
        return $response;
    }

    public function ModificarCuentaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $nroCuenta = $parametros["nroCuenta"];
        $tipoCuenta = $parametros["tipoCuenta"];
        $nombre =$parametros["nombre"];
        $apellido =$parametros["apellido"];
        $nroDocumento =$parametros["nroDocumento"];
        $mail = $parametros["mail"];
        $contrasena = $parametros["contrasena"];
        // $estado = $parametros["estado"];
        if(Cuenta::ModificarCuenta($nroCuenta,$tipoCuenta,$nombre,$apellido,$nroDocumento,$mail,$contrasena)){
            $cuentaModificada = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
            
            $retorno = json_encode(array("mensaje" => "Cuenta modificada: <br>" . $cuentaModificada->__toString() . "<br><br>Por favor, vuelva a iniciar sesion"));
        }else{
            $retorno = json_encode(array("mensaje" => "Cuenta no modificada, no coinciden nroCuenta con el tipoCuenta"));
        }
        $response->getBody()->write($retorno);
        return $response;
    }

}