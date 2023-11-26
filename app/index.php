<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once './db/AccesoDatos.php';
require_once './JWT/AuthJWT.php';
require_once './controllers/CuentaController.php';
require_once './controllers/LoginController.php';
require_once './controllers/DepositoController.php';
require_once './controllers/RetiroController.php';
require_once './controllers/AjusteController.php';
require_once './Middlewares/AuthCuentaMW.php';
require_once './Middlewares/AuthTipoCuentaMW.php';

// Instantiate App
$app = AppFactory::create();
// Set base path
$app->setBasePath('/2P-Programacion_3/app');
// Add error middleware
$app->addErrorMiddleware(true, true, true);
// Add parse body
$app->addBodyParsingMiddleware();

//http://localhost/2P-Programacion_3/app/

$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('[/]', \LoginController::class . ':LoginController');
});

$app->group('/cuentas', function (RouteCollectorProxy $group) {
    $group->post('/cargarCuenta', \CuentaController::class . ':CargarCuenta')//val logger, tipo cta, validaciones de usuario repetido
        ->add(\AuthTipoCuentaMW::class);            //validar el tipo de cuenta
    ;
    $group->put('/modificarCuenta', \CuentaController::class . ':ModificarCuentaController')//val logger, tipo cta, nroCuenta, que coincida nroCuenta con tipo
        ->add(\AuthCuentaMW::class)
        ->add(\AuthTipoCuentaMW::class);            //validar el tipo de cuenta
    $group->delete('/bajarCuenta', \CuentaController::class . ':BajarCuentaController')//val logger, tipo cta, validaciones de usuario repetido
        ->add(\AuthCuentaMW::class);
    //ValidarUsuarioEnJson -> MW para validar usuarios
    //ValidarUsuarioYTipoEnJson -> MW para validar usuarios
});

$app->group('/consultas', function (RouteCollectorProxy $group) {
    $group->post('/consultarCuenta', \CuentaController::class . ':ConsultarCuentaController');//validar si existe el numero, si esta bien cargado el tipo de cuenta

    $group->get('/consultarOperaciones', \CuentaController::class . ':ConsultarOperacionesController');
    
    $group->group('/depositos', function (RouteCollectorProxy $group) {
        //a- El total depositado (monto) por tipo de cuenta y moneda en un día en particular (se envía por parámetro), si no se pasa fecha, se muestran las del día anterior.
        $group->get('/totalDepositado', \DepositoController::class . ':TotalDepositadoController');
        // b- El listado de depósitos para un usuario en particular.
        $group->get('/depositosUsuario', \DepositoController::class . ':DepositosUsuarioController');
        // c- El listado de depósitos entre dos fechas ordenado por nombre.
        $group->get('/depositosFechas', \DepositoController::class . ':DepositosFechasOrdenadoController');
        // d- El listado de depósitos por tipo de cuenta.
        $group->get('/depositosTipoCuenta', \DepositoController::class . ':DepositosTipoCuentaController')
            ->add(\AuthTipoCuentaMW::class);            //validar el tipo de cuenta
        // e- El listado de depósitos por moneda.
        $group->get('/depositosMoneda', \DepositoController::class . ':DepositosPorMonedaController');
    });
    
    $group->group('/retiros', function (RouteCollectorProxy $group) {
        $group->get('/totalRetirado', \RetiroController::class . ':TotalRetiradoController');
        $group->get('/retirosUsuario', \RetiroController::class . ':RetirosUsuarioController');
        $group->get('/retirosFechas', \RetiroController::class . ':RetirosFechasOrdenadoController');
        $group->get('/retirosTipoCuenta', \RetiroController::class . ':RetirosTipoCuentaController')
            ->add(\AuthTipoCuentaMW::class);            //validar el tipo de cuenta
        $group->get('/retirosMoneda', \RetiroController::class . ':RetirosPorMonedaController');
    });
});

$app->group('/operaciones', function (RouteCollectorProxy $group) {
    $group->post('/deposito', \DepositoController::class . ':CargarDeposito')
        ->add(\AuthCuentaMW::class);
        //habra qie validar que la cuenta ingresada corresponda al usuario logueado (si el nro cuenta correponde al dni)
    $group->post('/retiro', \RetiroController::class . ':CargarRetiro');
    $group->post('/ajuste', \AjusteController::class . ':CargarAjuste');
});

$app->run();
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'CuentaAlta':
                # code 1
                // include_once "CuentaAlta.php";
                break;
                
            case 'ConsultarCuenta':
                # code 2
                include_once "ConsultarCuenta.php";
                break;
            case 'DepositoCuenta':
                # code 3
                include_once "DepositoCuenta.php";
                break;
            case 'RetiroCuenta':
                # code 6
                include_once "RetiroCuenta.php";
                break;
            case 'AjusteCuenta':
                # code 7
                include_once "AjusteCuenta.php";
                break;
                
            default:
                echo "la accion no se encuentra entre las opciones. Indicar:<br>
                <br>CuentaAlta<br>ConsultarCuenta<br>DepositoCuenta<br>RetiroCuenta<br>AjusteCuenta";
                break;
        }
    }else {
        echo 'Falta el parametro accion';
    }
}else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['consultarDepositos'])) {
        # code 4
        include_once "ConsultaMovimientos.php";        

        switch ($_GET['consultarDepositos']) {
            case 'totalDepositado':
                ConsultarTotalDepositado();
                break;
            case 'porUsuario':
                consultarDepositosPorUsuario();
                break;
            case 'entreFechas':
                consultarDepositosEntreFechas();
                break;
            case 'tipoDeCuenta':
                consultarDepositosPorTipoCuenta();
                break;
            case 'moneda':
                consultarDepositosPorMoneda();
                break;
            case 'operacionesUsuario':
                consultarOperacionesPorUsuario();
                break;
            default:
            echo "la accion no se encuentra entre las opciones. Indicar si la consulta de deposito es:<br>
            <br>totalDepositado<br>porUsuario<br>entreFechas<br>tipoDeCuenta<br>moneda<br>operacionesUsuario";
            break;    
        }
        
    }elseif (isset($_GET['consultarRetiros'])) {
        # code 10
        include_once "ConsultaMovimientos.php";        

        switch ($_GET['consultarRetiros']) {
            case 'totalRetirado':
                ConsultarTotalRetirado();
                break;
            case 'porUsuario':
                consultarRetirosPorUsuario();
                break;
            case 'entreFechas':
                consultarRetirosEntreFechas();
                break;
            case 'tipoDeCuenta':
                consultarRetirosPorTipoCuenta();
                break;
            case 'moneda':
                consultarRetirosPorMoneda();
                break;
            case 'operacionesUsuario':
                consultarOperacionesPorUsuario();
                break;    
            default:
            echo "la accion no se encuentra entre las opciones. Indicar si la consulta de Retiro es:<br>
            <br>totalRetirado<br>porUsuario<br>entreFechas<br>tipoDeCuenta<br>moneda<br>operacionesUsuario";
            break;    
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    if (isset($_GET['accion'])) {
        switch ($_GET['accion']) {
            case 'modificarCuenta':
                # code 5
                include_once "ModificarCuenta.php";       
                break;
            default:
                echo "accion no definida. Indicar si la accion es:<br>
                <br>modificarCuenta<br>";
            break;
        }
    } 
} else if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    if (isset($_GET['accion'])) {
        switch ($_GET['accion']) {
            case 'BorrarCuenta':
                # code 5
                include_once "BorrarCuenta.php";       
                break;
            default:
                echo "accion no definida. Indicar si la accion es:<br>
                <br>BorrarCuenta<br>";
            break;
        }
    } 
}else{
    echo 'Falta el parametro accion';
}*/
?>