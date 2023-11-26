<?php
require_once './models/Cuenta.php';
require_once './models/Deposito.php';
require_once './models/Retiro.php';
require_once './models/Ajuste.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AjusteController extends Ajuste{
    
    public function CargarAjuste($request, $response, $args)
    {  
        $parametros = $request->getParsedBody();
        $tipoOperacion = $parametros['tipoOperacion'];
        $idOperacion = $parametros['IdOperacion'];
        $motivo = $parametros['motivo'];
        $monto = $parametros['monto'];

        if($tipoOperacion == 'retiro'){
            // $listaOperacion = Retiro::ObtenerTodosRetiros();
            $operacion = Retiro::ObtenerRetiroPorID($idOperacion);
        }elseif($tipoOperacion == 'deposito'){
            // $listaOperacion = Deposito::ObtenerTodosDepositos();
            $operacion = Deposito::ObtenerDepositoPorID($idOperacion);
        }
            
        if($operacion != false){
            $nroCuenta = $operacion->nroCuenta;
            $cuentaAjustable = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);

            // Creamos el ajuste
            $ajusteNuevo = new Ajuste();
            $ajusteNuevo->nroCuenta = $nroCuenta;
            $ajusteNuevo->tipoOperacion = $tipoOperacion;
            $ajusteNuevo->idOperacion = $idOperacion;
            $ajusteNuevo->motivo = $motivo;
            $ajusteNuevo->monto = $monto;
            // $ajusteNuevo->saldo = $saldo + $ajuste;

            // Cuenta::ActualizarSaldo($nroCuenta,$monto);
            
            if($tipoOperacion == "deposito"){
                if($motivo == 'saldo positivo'){
                    Cuenta::ActualizarSaldo($nroCuenta,$monto);
                    $ajusteNuevo->crearAjuste();
                    $payload = json_encode(array("mensaje" => "Ajuste realizado con exito"));
                }else if($motivo == 'saldo negativo'){
                    if($monto > $cuentaAjustable->saldo){
                        $payload = json_encode(array('error: ' => '<br>El ajuste negativo no puede ser mayor al balance de la cuenta: <br>balance actual: $'. $cuentaAjustable->saldo));
                    }else{
                        $montoNegativo = $monto * -1;
                        Cuenta::ActualizarSaldo($nroCuenta,$montoNegativo);
                        $ajusteNuevo->crearAjuste();
                        $payload = json_encode(array("mensaje" => "Ajuste realizado con exito"));
                    }
                }
            }elseif($tipoOperacion == "retiro"){
                if($motivo == 'saldo positivo'){
                    if($monto > $operacion->GetMonto()){
                        $payload = json_encode(array('error: ' => '<br>El importe retirado en el retiro ID:' . $operacion->id . "fue de $".$operacion->GetMonto().", el ajuste positivo tiene que ser un monto de 0 a " . $operacion->GetMonto()));
                    }else{
                        Cuenta::ActualizarSaldo($nroCuenta,$monto);
                        $ajusteNuevo->crearAjuste();
                        $payload = json_encode(array("mensaje" => "Ajuste realizado con exito"));
                    }
                }else if($motivo == 'saldo negativo'){
                    if($monto > $cuentaAjustable->saldo){
                        $payload = json_encode(array('error: ' => '<br>El ajuste negativo no puede ser mayor al balance de la cuenta: <br>balance actual: $'. $cuentaAjustable->saldo));
                    }else{
                        $montoNegativo = $monto * -1;
                        Cuenta::ActualizarSaldo($nroCuenta,$montoNegativo);
                        $ajusteNuevo->crearAjuste();
                        $payload = json_encode(array("mensaje" => "Ajuste realizado con exito"));
                    }
                }
            }
        }else{
            $payload = json_encode(array("mensaje" => "No existe el ". $tipoOperacion . " con el id " .$idOperacion));
        }

        // $payload = json_encode(array("mensaje" => "Ajuste realizado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}