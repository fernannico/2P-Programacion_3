<?php
require_once './models/Deposito.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class DepositoController extends Deposito /*implements IApiUsable*/
{
    
    public function CargarDeposito($request, $response, $args)
    {
        
        // $header = $request->getHeaderLine('Authorization');
        // $token = trim(explode("Bearer", $header)[1]);
        
        // $data = AutentificadorJWT::ObtenerData($token);
        // $nroCuenta = $data->nroCuenta;
        // $tipoCuenta = $data->tipoCuenta;
        
        $parametros = $request->getParsedBody();
        $nroCuenta = $parametros['nroCuenta'];
        $tipoCuenta = $parametros['tipoCuenta'];
        $deposito = $parametros['deposito'];
        $moneda = Cuenta::ObtenerMonedaPorCuenta($tipoCuenta);
        $cuenta = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
        $saldo = $cuenta->saldo;

        // Creamos el deposito
        $depositoNuevo = new Deposito();
        $depositoNuevo->nroCuenta = $nroCuenta;
        $depositoNuevo->tipoCuenta = $tipoCuenta;
        $depositoNuevo->moneda = $moneda;
        $depositoNuevo->deposito = $deposito;
        $depositoNuevo->saldo = $saldo + $deposito;
        $ultimoId = (int)Deposito::ObtenerUltimoId() + 1;
        $nombre_imagen = $tipoCuenta . "_" . $nroCuenta . "_" . $ultimoId . ".jpg";          
        $depositoNuevo->imagen = $nombre_imagen;
        $depositoNuevo->crearDeposito();

        Cuenta::ActualizarSaldo($nroCuenta,$deposito);
        $depositoNuevo->GuardarImagen($_FILES['imagen']['tmp_name']);
        $depositoNuevo->id = $ultimoId;
        $depositoNuevo->fecha = date("d-m-Y H:i:s");
        $payload = json_encode(array("mensaje" => "Deposito creado con exito<br>". $depositoNuevo->__toString()));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function TotalDepositadoController($request, $response, $args)
    {
        // $header = $request->getHeaderLine('Authorization');
        // $token = trim(explode("Bearer", $header)[1]);

        // $data = AutentificadorJWT::ObtenerData($token);
        // $tipoCuenta = $data->tipoCuenta;
        $queryParams = $request->getQueryParams();
        $tipoCuenta = $queryParams['tipoCuenta'];
        if(isset($queryParams['fecha']) && !empty($queryParams['fecha'])) {
            $fecha = $queryParams['fecha'];
        }else{
            $fechaAnterior = date("d-m-Y", strtotime(date("d-m-Y") . "-1 day"));
            $fecha = $fechaAnterior;
        }
        $moneda = Cuenta::ObtenerMonedaPorCuenta($tipoCuenta);

        $lista = Deposito::ObtenerTodosDepositos();
        $listaNueva = Array();
        $totalDepositado = 0;
        foreach($lista as $deposito)
        {
            $fechaDeposito = new DateTime($deposito->fecha);
            $fechaDepositoFormateada = $fechaDeposito->format('d-m-Y');
            if($deposito->tipoCuenta == $tipoCuenta && $deposito->moneda == $moneda && $fechaDepositoFormateada === $fecha){
                $listaNueva[] = $deposito->__toString() . "<br>-----------------<br>";
                $totalDepositado += $deposito->deposito;
            }
        }

        $payloadArray = array(
            "<br> total Depositado: " => $totalDepositado,
            "<br>lista Depositos: <br>" => $listaNueva
        );
    
        $payload = json_encode($payloadArray);
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DepositosUsuarioController($request, $response, $args)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $nroDocumento = $data->nroDocumento;
        // $nroCuenta = $data->nroCuenta;
        $listaDepositos = Deposito::ObtenerTodosDepositos();
        $listaCuentas = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumento);//tengo todas las cuentas con el mismo nro de doc

        foreach($listaCuentas as $cuenta)
        {
            foreach($listaDepositos as $deposito){
                if($deposito->nroCuenta == $cuenta->nroCuenta){
                    $lista[] = $deposito->__toString() . "<br>-----------------<br>";
                }
            }
        }

        $payload = json_encode(array("lista_Depositos" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DepositosFechasOrdenadoController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $fechaInicio = null;
        $fechaFin = null;
        $fechaInicio = $queryParams["fechaInicio"];
        $fechaFin = $queryParams["fechaFin"];

        $depositosEntreFechas = Deposito::ObtenerDepositosEntreFechas($fechaInicio,$fechaFin);
        $depositos = Deposito::OrdenarDepositosPorNumeroCuenta($depositosEntreFechas);
        if($depositos !== null && !empty($depositos)){

            $payload = json_encode(array("lista_depositos:" => $depositos));
        }else {
            echo "<br>no hay depositos entre estas fechas";
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DepositosTipoCuentaController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $tipoCuenta = $queryParams['tipoCuenta'];

        $listaDepositos = Deposito::ObtenerTodosDepositos();

        foreach($listaDepositos as $deposito){
            if($deposito->tipoCuenta == $tipoCuenta){
                $lista[] = $deposito->__toString()."<br>-----------------<br>";
            }
        }

        $payload = json_encode(array("lista_depositos" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DepositosPorMonedaController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $moneda = $queryParams['moneda'];

        $listaDepositos = Deposito::ObtenerTodosDepositos();

        foreach($listaDepositos as $deposito){
            if($deposito->moneda == $moneda){
                $lista[] = $deposito;
            }
        }

        $payload = json_encode(array("lista_depositos" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}