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
require_once './Middlewares/ValidarLoginMW.php';
require_once './Middlewares/AuthDniTipoCta.php';
require_once './Middlewares/AuthNroCtaTipoCta.php';
require_once './Middlewares/AuthDniNombreAellido.php';
require_once './Middlewares/AuthCuentaMW.php';
require_once './Middlewares/AuthCuentasUsuario.php';
require_once './Middlewares/AuthTipoCuentaMW.php';
require_once './Middlewares/AuthMonedaMW.php';
require_once './Middlewares/AuthFechaMW.php';
require_once './Middlewares/AuthDniMW.php';

// Instantiate App
$app = AppFactory::create();
// Set base path
$app->setBasePath('/2P-Programacion_3/app');
// Add error middleware
$app->addErrorMiddleware(true, true, true);
// Add parse body
$app->addBodyParsingMiddleware();

//http://localhost/2P-Programacion_3/app/

$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->post('/login', \LoginController::class . ':LoginController');
});

$app->group('/cuentas', function (RouteCollectorProxy $group) {
    $group->post('/cargarCuenta', \CuentaController::class . ':CargarCuenta')
        ->add(\AuthDniNombreAellido::class)         //validar si ya existe el dni y corresponde al mismo nombre y apellido
        ->add(\AuthDniTipoCta::class)               //validar si ya existe la cta (nueva o actualizar)
        ->add(\AuthTipoCuentaMW::class);            //validar el tipo de cuenta
    $group->put('/modificarCuenta', \CuentaController::class . ':ModificarCuentaController')
        // ->add(\AuthDniNombreAellido::class)         //validar si ya existe el dni y corresponde al mismo nombre y apellido
        ->add(\AuthCuentasUsuario::class)           //valida que el nro de cuenta ingresado sea del usuario logueado
        ->add(\AuthCuentaMW::class)                 //valida que exista en nroCuenta en la talba de cuentas
        ->add(\AuthTipoCuentaMW::class);            //validar el tipo de cuenta
    $group->delete('/bajarCuenta', \CuentaController::class . ':BajarCuentaController')
        ->add(\AuthCuentasUsuario::class)           //valida que el nro de cuenta ingresado sea del usuario logueado
        ->add(\AuthCuentaMW::class);                //valida que exista el nroCuenta en la talba de cuentas
})->add(\ValidarLoginMW::class);                    //valida que exista el token (logueado)
    
$app->group('/operaciones', function (RouteCollectorProxy $group) {
    $group->post('/deposito', \DepositoController::class . ':CargarDeposito')
        ->add(\AuthNroCtaTipoCta::class)            //validar match entre el tipo de cuenta con el nro de cuenta
        ->add(\AuthTipoCuentaMW::class)             //validar el tipo de cuenta
        ->add(\AuthCuentaMW::class)                //valida que exista en nroCuenta en la talba de cuentas
        ->add(\AuthCuentasUsuario::class);           //valida que el nro de cuenta ingresado sea del usuario logueado
    $group->post('/retiro', \RetiroController::class . ':CargarRetiro')
        ->add(\AuthNroCtaTipoCta::class)            //validar match entre el tipo de cuenta con el nro de cuenta
        ->add(\AuthTipoCuentaMW::class)             //validar el tipo de cuenta
        ->add(\AuthCuentaMW::class)                //valida que exista en nroCuenta en la talba de cuentas    
        ->add(\AuthCuentasUsuario::class);           //valida que el nro de cuenta ingresado sea del usuario logueado
    $group->post('/ajuste', \AjusteController::class . ':CargarAjuste');
})->add(\ValidarLoginMW::class);           
        
$app->group('/consultas', function (RouteCollectorProxy $group) {
    $group->post('/consultarCuenta', \CuentaController::class . ':ConsultarCuentaController')
        ->add(\AuthTipoCuentaMW::class)             //validar el tipo de cuenta
        ->add(\AuthCuentasUsuario::class)           //valida que el nro de cuenta ingresado sea del usuario logueado
        ->add(\AuthNroCtaTipoCta::class)            //validar match entre el tipo de cuenta con el nro de cuenta
        ->add(\AuthCuentaMW::class);                //valida que exista en nroCuenta en la talba de cuentas
    
    $group->get('/consultarOperaciones', \CuentaController::class . ':ConsultarOperacionesController');
        // ->add(\AuthDniMW::class);                   //validar que existe el dni 
    
    $group->group('/depositos', function (RouteCollectorProxy $group) {
        $group->get('/totalDepositado', \DepositoController::class . ':TotalDepositadoController')
            ->add(\AuthTipoCuentaMW::class);         //validar el tipo de cuenta
            // ->add(\AuthFechaMW::class);             //validar el formato de la fecha
        $group->get('/depositosUsuario', \DepositoController::class . ':DepositosUsuarioController');
        $group->get('/depositosFechas', \DepositoController::class . ':DepositosFechasOrdenadoController')
            ->add(\AuthFechaMW::class);             //validar el formato de la fecha
        $group->get('/depositosTipoCuenta', \DepositoController::class . ':DepositosTipoCuentaController')
            ->add(\AuthTipoCuentaMW::class);        //validar el tipo de cuenta
        $group->get('/depositosMoneda', \DepositoController::class . ':DepositosPorMonedaController')
            ->add(\AuthMonedaMW::class);            //validar el tipo de moneda
    });
    
    $group->group('/retiros', function (RouteCollectorProxy $group) {
        $group->get('/totalRetirado', \RetiroController::class . ':TotalRetiradoController')
            ->add(\AuthTipoCuentaMW::class);         //validar el tipo de cuenta
            // ->add(\AuthFechaMW::class);             //validar el formato de la fecha
        $group->get('/retirosUsuario', \RetiroController::class . ':RetirosUsuarioController');
        $group->get('/retirosFechas', \RetiroController::class . ':RetirosFechasOrdenadoController')
            ->add(\AuthFechaMW::class);             //validar el formato de la fecha
        $group->get('/retirosTipoCuenta', \RetiroController::class . ':RetirosTipoCuentaController')
            ->add(\AuthTipoCuentaMW::class);        //validar el tipo de cuenta
        $group->get('/retirosMoneda', \RetiroController::class . ':RetirosPorMonedaController')
            ->add(\AuthMonedaMW::class);            //validar el tipo de moneda
    });
})->add(\ValidarLoginMW::class);

$app->run();
?>