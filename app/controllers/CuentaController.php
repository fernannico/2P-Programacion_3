<?php
require_once './models/Cuenta.php';
// require_once './interfaces/IApiUsable.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class CuentaController extends Cuenta /*implements IApiUsable*/
{
    
    public function CargarCuenta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $apellido = $parametros['apellido'];
        $nroDocumento = $parametros['nroDocumento'];
        $mail = $parametros['mail'];
        $contrasena = $parametros['contrasena'];
        $tipoCuenta = $parametros['tipoCuenta'];
        $moneda = $parametros['moneda'];
        // $saldo = $parametros['saldo'];               ver que hacer porque es 0 por defecto pero puede crearse con algo de $ ya

        // Creamos el cuenta
        $cuenta = new Cuenta();
        $cuenta->nombre = $nombre;
        $cuenta->apellido = $apellido;
        $cuenta->nroDocumento = $nroDocumento;
        $cuenta->mail = $mail;
        $cuenta->contrasena = $contrasena;
        $cuenta->tipoCuenta = $tipoCuenta;
        $cuenta->moneda = $moneda;
        // $cuenta->saldo = $saldo;
        $cuenta->crearCuenta();
        var_dump($cuenta);

        $payload = json_encode(array("mensaje" => "Cuenta creado con exito"));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /*
    public function TraerUno($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        
        if(isset($id) & $id !== ""){
            $cuenta = Cuenta::obtenerCuentaPorID($id);

            if ($cuenta) {
                $payload = json_encode($cuenta);
                $response->getBody()->write($payload);
            } else {
                $response->getBody()->write(json_encode(["error" => "Cuenta no encontrado"]));
            }
        }else{
            $response->getBody()->write(json_encode(["error" => "ID de cuenta no proporcionado"]));            
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Cuenta::obtenerTodosCuentas();
        $payload = json_encode(array("listaCuenta" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CerrarMesaController(Request $request, Response $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];

        // $dataToken = $request->getAttribute('datosToken');
        // var_dump($dataToken->puesto);

        if(Cuenta::CambiarEstadoMesa($idMesa,"cerrada")){
            $retorno = json_encode(array("mensaje" => "mesa cerrada"));
        }else{
            $retorno = json_encode(array("mensaje" => "mesa NO cerrada"));
        }
        $response->getBody()->write($retorno);
        return $response;
    }

    public function CambiarEstadoMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];
        $estado = $parametros["estado"];

        // $dataToken = $request->getAttribute('datosToken');
        // var_dump($dataToken->puesto);

        if(Cuenta::CambiarEstadoMesa($idMesa, $estado)){
            $retorno = json_encode(array("mensaje" => "estado de la mesa cambiado: " . $estado));
        }else{
            $retorno = json_encode(array("mensaje" => "estado no cambiado"));
        }
        $response->getBody()->write($retorno);
        return $response;
    }

    public function CambiarEstadoCuentaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idCuenta = $parametros["idCuenta"];
        $estado = $parametros["estado"];

        if(Cuenta::CambiarEstadoCuenta($idCuenta, $estado)){
            $retorno = json_encode(array("mensaje" => "estado del cuenta cambiado: " . $estado));
        }else{
            $retorno = json_encode(array("mensaje" => "estado no cambiado"));
        }
        $response->getBody()->write($retorno);
        return $response;


    }

    public function ModificarCuentaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idCuenta = $parametros["idCuenta"];
        $nombre = $parametros["nombre"];
        $puesto = $parametros["puesto"];
        $sector = $parametros["sector"];
        $contrasena = $parametros["contrasena"];
        // $estado = $parametros["estado"];
        if(Cuenta::ModificarCuenta($idCuenta,$nombre,$puesto,$sector,$contrasena)){
            $retorno = json_encode(array("mensaje" => "Cuenta modificado: "));
        }else{
            $retorno = json_encode(array("mensaje" => "Cuenta no modificado"));
        }
        $response->getBody()->write($retorno);
        return $response;

    }

    public function CargarCuentasDesdeCsv($request,$response, $args)
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['archivo'] ?? null;

        if ($uploadedFile === null || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $retorno = json_encode(array("mensaje"=>"No se ha enviado ningun archivo o hubo un error en la carga"));
        }else{
            $tempFileName = $uploadedFile->getClientFilename();

            if (($archivo = fopen($tempFileName, "r")) !== false) {
                $encabezado = fgets($archivo);

                while (!feof($archivo)) {
                    $linea = fgets($archivo);
                    $datos = str_getcsv($linea);
                    
                    $cuenta = new Cuenta();
                    $cuenta->id = $datos[0];
                    $cuenta->nombre = (string)$datos[1];
                    $cuenta->puesto = (string)$datos[2];
                    $cuenta->sector = (string)$datos[3];
                    $cuenta->ingresoSist = (string)$datos[4];
                    $cuenta->cantOperaciones = (string)$datos[5];
                    $cuenta->contrasena = (string)$datos[6];
                    $cuenta->estado = (string)$datos[7];
                    $cuenta->crearCuenta();
                }

                fclose($archivo);
                                
                $retorno = json_encode(array("mensaje"=>"Cuentas cargados en la bdd"));
            }else{
                $retorno = json_encode(array("mensaje"=>"Error en el archivo, no se encontro"));
            } 
        }

        $response->getBody()->write($retorno);
        return $response;
    }
    
    public function DescargarCuentasDesdeCsv($request,$response, $args)
    {
        $path = "cuentasDesc.csv";
        $cuentasArray = Array();
        $cuentas = Cuenta::obtenerTodosCuentas();

        foreach ($cuentas as $cuentaInd) {
            $contrasena = Cuenta::obtenerContrasenaPorID($cuentaInd->id);
            // var_dump($contrasena);
            $cuentaInd->contrasena = $contrasena;
        }        
        foreach($cuentas as $cuentaInd){
            $cuenta = array($cuentaInd->id, $cuentaInd->nombre, $cuentaInd->puesto, $cuentaInd->sector, $cuentaInd->ingresoSist, $cuentaInd->cantOperaciones, $cuentaInd->contrasena, $cuentaInd->estado);
            $cuentasArray[] = $cuenta;
        }

        $archivo = fopen($path, "w");
        $encabezado = array("id", "nombre", "puesto", "sector", "ingresoSist", "cantOperaciones", "contrasena", "estado");
        fputcsv($archivo, $encabezado);
        foreach($cuentasArray as $fila){
            fputcsv($archivo, $fila);
        }
        fclose($archivo);
        $retorno = json_encode(array("mensaje"=>"Cuentas guardados en CSV con exito"));
           
        $response->getBody()->write($retorno);
        return $response;
    }*/
}