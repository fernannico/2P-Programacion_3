<?php
require_once './models/Deposito.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class DepositoController extends Deposito /*implements IApiUsable*/
{
    
    public function CargarDeposito($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        // $id = $parametros[''];
        // $fecha = $parametros[''];
        $nroCuenta = $parametros['nroCuenta'];
        $tipoCuenta = $parametros['tipoCuenta'];
        $moneda = $parametros['moneda'];
        $deposito = $parametros['deposito'];
        $saldo = Cuenta::ObtenerSaldoPorNroCuenta($nroCuenta);
        // $nombre = $parametros['nombre'];

        // Creamos el deposito
        $depositoNuevo = new Deposito();
        $depositoNuevo->nroCuenta = $nroCuenta;
        $depositoNuevo->tipoCuenta = $tipoCuenta;
        $depositoNuevo->moneda = $moneda;
        $depositoNuevo->deposito = $deposito;
        $depositoNuevo->saldo = $saldo + $deposito;
        $depositoNuevo->crearDeposito();

        Cuenta::ActualizarSaldo($nroCuenta,$deposito);
        // var_dump($deposito);

        $payload = json_encode(array("mensaje" => "Deposito creado con exito"));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}