<?php
require_once './models/Deposito.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class DepositoController extends Deposito /*implements IApiUsable*/
{
    
    public function CargarDeposito($request, $response, $args)
    {
        
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        
        $data = AutentificadorJWT::ObtenerData($token);
        $nroCuenta = $data->nroCuenta;
        $tipoCuenta = $data->tipoCuenta;
        $moneda = Cuenta::ObtenerMonedaPorCuenta($tipoCuenta);
        
        $parametros = $request->getParsedBody();
        $deposito = $parametros['deposito'];
        $saldo = Cuenta::ObtenerSaldoPorNroCuenta($nroCuenta);

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

        $payload = json_encode(array("mensaje" => "Deposito creado con exito"));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function TotalDepositadoController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $fecha = $queryParams['fecha'];
        
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $tipoCuenta = $data->tipoCuenta;
        $moneda = Cuenta::ObtenerMonedaPorCuenta($tipoCuenta);

        $lista = Deposito::ObtenerTodosDepositos();
        // var_dump($fecha);
        $listaNueva = Array();
        $totalDepositado = 0;
        foreach($lista as $deposito)
        {
            $fechaDeposito = new DateTime($deposito->fecha);
            // var_dump($fechaDeposito);
            $fechaDepositoFormateada = $fechaDeposito->format('d-m-Y');
            // var_dump($fechaDepositoFormateada);
            if($deposito->tipoCuenta == $tipoCuenta && $deposito->moneda == $moneda && $fechaDepositoFormateada === $fecha){
                $listaNueva[] = $deposito;
                $totalDepositado += $deposito->deposito;
            }
        }

        $payloadArray = array(
            "total Depositado: <br>" => $totalDepositado,
            "lista Depositos: <br>" => $listaNueva
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
        // var_dump($data->nroDocumento);
        $nroDocumento = $data->nroDocumento;
        // $nroCuenta = $data->nroCuenta;
        $listaDepositos = Deposito::ObtenerTodosDepositos();
        $listaCuentas = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumento);//tengo todas las cuentas con el mismo nro de doc

        foreach($listaCuentas as $cuenta)
        {
            foreach($listaDepositos as $deposito){
                if($deposito->nroCuenta == $cuenta->nroCuenta){
                    $lista[] = $deposito;
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
        // var_dump($depositosEntreFechas);
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
                $lista[] = $deposito;
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