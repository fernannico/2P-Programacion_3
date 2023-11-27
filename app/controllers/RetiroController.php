<?php
require_once './models/Retiro.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class RetiroController extends Retiro /*implements IApiUsable*/
{
    
    public function CargarRetiro($request, $response, $args)
    {
        // $header = $request->getHeaderLine('Authorization');
        // $token = trim(explode("Bearer", $header)[1]);
        
        // $data = AutentificadorJWT::ObtenerData($token);
        // $nroCuenta = $data->nroCuenta;
        // $tipoCuenta = $data->tipoCuenta;
        
        $parametros = $request->getParsedBody();
        $nroCuenta = $parametros['nroCuenta'];
        $tipoCuenta = $parametros['tipoCuenta'];
        $retiro = $parametros['retiro'];
        $moneda = Cuenta::ObtenerMonedaPorCuenta($tipoCuenta);        
        $cuenta = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
        $saldo = $cuenta->saldo;

        if($retiro > $saldo){
            $payload = json_encode(array("mensaje" => "El monto retirado no puede superar al saldo de la cuenta <br>Saldo: $" . $saldo));
        }else{
            // Creamos el retiro
            $retiroNuevo = new Retiro();
            $retiroNuevo->nroCuenta = $nroCuenta;
            $retiroNuevo->tipoCuenta = $tipoCuenta;
            $retiroNuevo->moneda = $moneda;
            $retiroNuevo->retiro = $retiro;
            $retiroNuevo->saldo = $saldo - $retiro;
            $retiroNuevo->crearRetiro();
    
            $retiro = $retiro * -1;
            Cuenta::ActualizarSaldo($nroCuenta,$retiro);
            // var_dump($retiro);
            $ultimoId = (int)Retiro::ObtenerUltimoId();
            $retiroNuevo->id = $ultimoId;
            $retiroNuevo->fecha = date("d-m-Y H:i:s");
            $payload = json_encode(array("mensaje" => "Retiro creado con exito <br>" . $retiroNuevo->__toString()));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function TotalRetiradoController($request, $response, $args)
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

        $lista = Retiro::ObtenerTodosRetiros();
        $listaNueva = Array();
        $totalRetirado = 0;
        foreach($lista as $retiro)
        {
            $fechaRetiro = new DateTime($retiro->fecha);
            $fechaRetiroFormateada = $fechaRetiro->format('d-m-Y');
            if($retiro->tipoCuenta == $tipoCuenta && $retiro->moneda == $moneda && $fechaRetiroFormateada === $fecha){
                $listaNueva[] = $retiro;
                $totalRetirado += $retiro->retiro;
            }
        }

        $payloadArray = array(
            "total_retirado: <br>" => $totalRetirado,
            "lista_retiros: <br>" => $listaNueva
        );
    
        $payload = json_encode($payloadArray);
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function RetirosUsuarioController($request, $response, $args)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        // var_dump($data->nroDocumento);
        $nroDocumento = $data->nroDocumento;
        // $nroCuenta = $data->nroCuenta;
        $listaRetiros = Retiro::ObtenerTodosRetiros();
        $listaCuentas = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumento);//tengo todas las cuentas con el mismo nro de doc

        foreach($listaCuentas as $cuenta)
        {
            foreach($listaRetiros as $retiro){
                if($retiro->nroCuenta == $cuenta->nroCuenta){
                    $lista[] = $retiro;
                }
            }
        }

        $payload = json_encode(array("lista_retiros" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function RetirosFechasOrdenadoController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $fechaInicio = null;
        $fechaFin = null;
        $fechaInicio = $queryParams["fechaInicio"];
        $fechaFin = $queryParams["fechaFin"];

        $retirosEntreFechas = Retiro::ObtenerRetirosEntreFechas($fechaInicio,$fechaFin);
        // var_dump($retirosEntreFechas);
        $retiros = Retiro::OrdenarRetirosPorNumeroCuenta($retirosEntreFechas);
        if($retiros !== null && !empty($retiros)){

            $payload = json_encode(array("lista_retiros:" => $retiros));
        }else {
            echo "<br>no hay retiros entre estas fechas";
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function RetirosTipoCuentaController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $tipoCuenta = $queryParams['tipoCuenta'];

        $listaRetiros = Retiro::ObtenerTodosRetiros();

        foreach($listaRetiros as $retiro){
            if($retiro->tipoCuenta == $tipoCuenta){
                $lista[] = $retiro;
            }
        }

        $payload = json_encode(array("lista_retiros" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function RetirosPorMonedaController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $moneda = $queryParams['moneda'];

        $listaRetiros = Retiro::ObtenerTodosRetiros();

        foreach($listaRetiros as $retiro){
            if($retiro->moneda == $moneda){
                $lista[] = $retiro;
            }
        }


        $payload = json_encode(array("lista_retiros" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}