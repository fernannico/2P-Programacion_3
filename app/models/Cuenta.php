<?php

class Cuenta /*implements JsonSerializable*/{
    public $nroCuenta;
    public $nombre;
    public $apellido;
    public $tipoDocumento;
    public $nroDocumento;
    public $mail;
    public $contrasena;
    public $tipoCuenta;
    public $moneda;
    public $saldo;
    public $estado;

    // public function __construct($nroCuenta='',$nombre='',$apellido='',$tipoDocumento='',$nroDocumento='',$mail='',$contrasena='',$tipoCuenta='',$moneda='',$saldo= '',$estado = '') {
    //     $this->nroCuenta = $nroCuenta;
    //     $this->nombre = $nombre;
    //     $this->apellido = $apellido;
    //     $this->tipoDocumento = $tipoDocumento;
    //     $this->nroDocumento = $nroDocumento;
    //     $this->mail = $mail;
    //     $this->contrasena = $contrasena;
    //     $this->tipoCuenta = $tipoCuenta;
    //     $this->moneda = $moneda;
    //     $this->saldo = $saldo;
    //     $this->estado = $estado;
    // }

    public function CrearCuenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO cuentas (nombre,apellido,nroDocumento,mail,contrasena,tipoCuenta,moneda) VALUES (:nombre,:apellido,:nroDocumento,:mail,:contrasena,:tipoCuenta,:moneda)");

        $consulta->bindParam(':nombre', $this->nombre);
        $consulta->bindParam(':apellido', $this->apellido);
        $consulta->bindParam(':nroDocumento', $this->nroDocumento);
        $consulta->bindParam(':mail', $this->mail);
        $consulta->bindParam(':contrasena', $this->contrasena);
        $consulta->bindParam(':tipoCuenta', $this->tipoCuenta);
        $consulta->bindParam(':moneda', $this->moneda);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerMonedaPorCuenta($tipoCuenta)
    {
        $tipoCAUSD= "CAU" . "$" . "S";
        $tipoCCUSS= "CCU" . "$" . "S";
        $tipoCAS = "CA" . "$";
        $tipoCCS = "CC" . "$";
        $dolar = "U"."$"."D";

        if($tipoCuenta == $tipoCAUSD || $tipoCuenta == $tipoCCUSS) {
            $moneda = $dolar;
        }elseif($tipoCuenta == $tipoCAS || $tipoCuenta == $tipoCCS){
            $moneda = "$";
        }

        return $moneda;
    }

    // public static function ObtenerTodasCuentas()
    // {
    //     $objAccesoDatos = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM cuentas");
    //     $consulta->execute();
        
    //     return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cuenta');
    // }

    public static function ObtenerCuentasPorNroDocumento($nroDocumento)
    {
        $cuentasEncontradas = array();
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nroCuenta, nombre, apellido, tipoDocumento, nroDocumento, mail, tipoCuenta, moneda, saldo, estado FROM cuentas WHERE nroDocumento = :nroDocumento");
        $consulta->bindParam(":nroDocumento", $nroDocumento);
        $consulta->execute();
        
        $cuentasEncontradas = $consulta->fetchAll(PDO::FETCH_CLASS, 'Cuenta');
        // var_dump($cuentasEncontradas);
        return $cuentasEncontradas;
    }   

    public static function ObtenerCuentaPorNroCuenta($nroCuenta)
    {
        $cuentaBuscado = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nroCuenta, nombre, apellido, tipoDocumento, nroDocumento, mail, tipoCuenta, moneda, saldo, estado FROM cuentas WHERE nroCuenta = :nroCuenta");
        $consulta->bindParam(":nroCuenta", $nroCuenta);
        $consulta->execute();
        
        $cuentaBuscado = $consulta->fetchObject('Cuenta');
        // var_dump($cuentaBuscado);
        return $cuentaBuscado;
    }   

    public static function ObtenerSaldoPorNroCuenta($nroCuenta)
    {
        $saldo = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT saldo FROM cuentas WHERE nroCuenta = :nroCuenta");
        $consulta->bindParam(":nroCuenta", $nroCuenta);
        $consulta->execute();
        
        $saldo = $consulta->fetchColumn();
        return $saldo;
    }

    public static function ActualizarSaldo($nroCuenta, $monto)
    {
        $retorno = false;
        $saldo = self::ObtenerSaldoPorNroCuenta($nroCuenta);
        $saldoNuevo = $saldo + $monto;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE cuentas SET saldo = :saldo WHERE nroCuenta = :nroCuenta");
            $consulta->bindParam(":nroCuenta", $nroCuenta);
            $consulta->bindParam(":saldo", $saldoNuevo);
            $consulta->execute();
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }

        return $retorno;
    }

    public static function CambiarEstadoCuenta($nroCuenta, $estado) 
    {
        $retorno = false;
        if(Cuenta::actualizarEstado($nroCuenta, $estado)){
            $retorno = true;
        }
        return $retorno;        
    }

    public static function ActualizarEstado($nroCuenta, $estado)
    {
        $retorno = false;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE cuentas SET estado = :estado WHERE nroCuenta = :nroCuenta");
            $consulta->bindParam(":nroCuenta", $nroCuenta);
            $consulta->bindParam(":estado", $estado);
            $consulta->execute();
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }

        return $retorno;
    }

    public static function ModificarCuenta($nroCuenta,$tipoCuenta,$nombre,$apellido,$nroDocumento,$mail,$contrasena)
    {
        // $retorno = false;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
            $consulta =$objetoAccesoDato->prepararConsulta("UPDATE cuentas SET nombre = :nombre, apellido = :apellido, nroDocumento = :nroDocumento, mail = :mail, contrasena = :contrasena WHERE nroCuenta = :nroCuenta AND tipoCuenta = :tipoCuenta");
            $consulta->bindParam(':nombre', $nombre);
            $consulta->bindParam(':apellido', $apellido);
            $consulta->bindParam(':nroDocumento', $nroDocumento);
            $consulta->bindParam(':mail', $mail);
            $consulta->bindParam(':contrasena', $contrasena);
            $consulta->bindParam(':nroCuenta', $nroCuenta);
            $consulta->bindParam(':tipoCuenta', $tipoCuenta);
            $consulta->execute();
            // $filasAfectadas = $consulta->rowCount();
            // $retorno = $filasAfectadas > 0; // Retorna true si se actualizó al menos una fila
    
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }
        
        return $retorno;
    }
    //getter/setter
    // public function GetNroCuenta() {
    //     return $this->nroCuenta;
    // }
    // public function GetNombre() {
    //     return $this->nombre;
    // }
    // public function GetApellido () {
    //     return $this->apellido;
    // } 
    // public function GetTipoDocumento () {
    //     return $this->tipoDocumento;
    // } 
    // public function GetNroDocumento () {
    //     return $this->nroDocumento;
    // } 
    // public function GetMail () {
    //     return $this->mail;
    // } 
    // public function GetTipoCuenta () {
    //     return $this->tipoCuenta;
    // } 
    // public function GetMoneda () {
    //     return $this->moneda;
    // } 
    // public function GetSaldo () {
    //     return $this->saldo;
    // }
    // public function SetSaldo($cantidad) {
    //     $this->saldo = $cantidad;
    // }
    // public function SetNombre($nombre) {
    //     $this->nombre = $nombre;
    // }
    // public function SetApellido($apellido) {
    //     $this->apellido = $apellido;
    // }
    // public function SetNroDocumento($nroDocumento) {
    //     $this->nroDocumento = $nroDocumento;
    // }
    // public function SetMail($mail) {
    //     $this->mail = $mail;
    // }
    // public function SetEstado($estado) {
    //     $this->estado = $estado;
    // }
    // public function GetEstado() {
    //     return $this->estado;
    // }

    // //metodos
    // public static function ValidarParametrosPost() {
    //     $retorno = false;
    //     if(isset($_POST["nombre"]) && isset($_POST["apellido"]) && isset($_POST["tipoDocumento"]) && isset($_POST["nroDocumento"]) && isset($_POST["email"]) && isset($_POST["tipoCuenta"]) && isset($_POST["moneda"]) && isset($_FILES["imagen"])) {
    //         $retorno = true;
    //     }
    //     return $retorno;
    // }

    // public static function ValidarTipoCuenta($tipo) {
    //     $retorno = false;

    //     $tipoCAUSD= "CAU" . "$" . "S";
    //     $tipoCCUSS= "CCU" . "$" . "S";

    //     if($tipo == "CA$" || $tipo == $tipoCAUSD || $tipo == "CC$" || $tipo == $tipoCCUSS || (!empty($tipo) && !trim($tipo) === "")) {
    //         $retorno = true;
    //     }

    //     return $retorno;
    // }
    // public static function ValidarMoneda($moneda) {
    //     $retorno = false;
    //     $dolar = "U" . "$" . "S";

    //     if($moneda == "$" || $moneda == $dolar || (!empty($moneda) && !trim($moneda) === "")) {
    //         $retorno = true;
    //     }

    //     return $retorno;
    // }

    // public static function ValidarTipoCuentaConMoneda($tipoCuenta,$moneda) {
    //     $retorno = false;
    //     $dolar = "U" . "$" . "S";
    //     $tipoCAUSS= "CAU" . "$" . "S";
    //     $tipoCCUSS= "CCU" . "$" . "S";

    //     if(Cuenta::ValidarTipoCuenta($tipoCuenta) && Cuenta::ValidarMoneda($moneda) ) {
    //         if($moneda == "$" && ($tipoCuenta == "CA$" || $tipoCuenta == "CC$")){
    //             $retorno = true;
    //         }elseif($moneda == $dolar && ($tipoCuenta == $tipoCAUSS || $tipoCuenta == $tipoCCUSS)){
    //             $retorno = true;
    //         }
    //     }

    //     return $retorno;
    // }

    // public function __toString() {
    //     return 
    //     "nro Cuenta: " . $this->nroCuenta .
    //     "<br>nombre: " . $this->nombre .
    //     "<br>apellido: " . $this->apellido .
    //     "<br>tipo Documento: " . $this->tipoDocumento .
    //     "<br>nro Documento: " . $this->nroDocumento .
    //     "<br>mail: " . $this->mail .
    //     "<br>tipo Cuenta: " . $this->tipoCuenta .
    //     "<br>moneda: " . $this->moneda .
    //     "<br>saldo: " . $this->saldo . 
    //     "<br>estado: " . $this->estado; 
    // }

    // public function JsonSerialize() {
    //     return [
    //         'nroCuenta' => $this->GetNroCuenta(),
    //         'nombre' => $this->GetNombre(),
    //         'apellido' => $this->GetApellido(),
    //         'tipoDocumento' => $this->GetTipoDocumento(),
    //         'nroDocumento' => $this->GetNroDocumento(),
    //         'mail' => $this->GetMail(),
    //         'tipoCuenta' => $this->GetTipoCuenta(),
    //         'moneda' => $this->GetMoneda(),
    //         'saldo' => $this->GetSaldo(),
    //         'estado'=> $this->GetEstado()
    //     ];
    // }

    // public static function JsonDeserialize($rutaJson) {
    //     $arrayCuentas = Array();
    //     if(file_exists($rutaJson)){
    //         $jsonString = file_get_contents($rutaJson);
    //         $cuentas = json_decode($jsonString, true);

    //         if (is_array($cuentas)) {
    //             foreach ($cuentas as $item) {
    //                 $cuenta = new Cuenta($item['nombre'], $item['apellido'], $item['tipoDocumento'], $item['nroDocumento'], $item['mail'], $item['tipoCuenta'], $item['moneda'], $item['saldo'],$item['nroCuenta'],$item['estado']);
    //                 $arrayCuentas[] = $cuenta;
    //             }
    //         }
    //     }

    //     return $arrayCuentas;
    // }
    // public static function GuardarCuentaJSON($cuenta,$rutaArchivoJson)
    // {
    //     $cuentaInd = $cuenta->JsonSerialize();

    //     $cuentas = [];
    //     if (file_exists($rutaArchivoJson)) {
    //         $cuentas = json_decode(file_get_contents($rutaArchivoJson), true);
    //     }

    //     $cuentas[] = $cuentaInd;

    //     file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));

    //     return true;
    // }
    
    // public static function ValidarCuentaEnJson($moneda,$tipoCuenta,$nroCuenta,$rutaArchivoJson) {
            
    //     $existe = null;
    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){

    //         if($cuentaCargada->GetNroCuenta()== $nroCuenta){
    //             if($cuentaCargada->GetMoneda() === $moneda && $cuentaCargada->GetTipoCuenta()=== $tipoCuenta){
    //                 $cuentaEncontrada = $cuentaCargada;
    //                 $existe= $cuentaEncontrada;                        
    //                 break;
    //             }
    //         }
    //     }
    //     return $existe;
    // }

    // public static function ValidarUsuarioYTipoEnJson($nombre,$apellido,$nroDocumento,$tipoCuenta,$moneda,$rutaArchivoJson){
        
    //     $existe = null;
    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){

    //         if($cuentaCargada->GetNombre()== $nombre && $cuentaCargada->GetApellido() == $apellido && $cuentaCargada->GetNroDocumento() == $nroDocumento){
    //             if($cuentaCargada->GetMoneda() === $moneda && $cuentaCargada->GetTipoCuenta()=== $tipoCuenta){
    //                 $cuentaEncontrada = $cuentaCargada;
    //                 $existe= $cuentaEncontrada;                        
    //                 break;
    //             }
    //         }
    //     }
    //     return $existe;
    // }

    // //"ya existe ese DNI y pero con otro nombre!"
    // public static function ValidarUsuarioEnJson($nroDocumento,$nombre,$apellido,$rutaArchivoJson){
    //     $usuarioValido = false;
    //     // $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     $cuentaPorDni = Cuenta::ValidarDniEnJson($nroDocumento,$rutaArchivoJson);

    //     if($cuentaPorDni == null){
    //         $usuarioValido = true;// El DNI no existe en el archivo JSON-> cualquier nombre y apellido .
    //     }else{
    //         if ($cuentaPorDni->GetNombre() == $nombre && $cuentaPorDni->GetApellido() == $apellido) {
    //             $usuarioValido = true; // El nombre y apellido coinciden.
    //         } else {
    //             $usuarioValido =  false; // El nombre y apellido no coinciden con el dni!.
    //         }    
    //     }
    //     return $usuarioValido;
    // }

    // public static function ValidarDniEnJson($numDni, $rutaArchivoJson){ 
    //     $existe = null;

    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);
        
    //     foreach($cuentasCargadas as $cuenta){
    //         if($cuenta->GetNroDocumento() == $numDni){
    //             $existe = $cuenta;
    //             break;
    //         }
    //     }

    //     return $existe;
    // }
    // public static function ValidarNroCuentaEnJson($nroCuenta,$rutaArchivoJson){
        
    //     $existe = null;
    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){
    //         if($cuentaCargada->GetNroCuenta()== $nroCuenta){
    //             $cuentaEncontrada = $cuentaCargada;
    //             $existe= $cuentaEncontrada;                        
    //             break;                
    //         }
    //     }
    //     return $existe;
    // }
    // public static function ObtenerCuentaPorNroCuenta($NroCuenta,$rutaArchivoJson) {
    //     $cuentaJson = null;
        
    //     $cuentasJson = Cuenta::JsonDeserialize($rutaArchivoJson);
    //     if($cuentasJson !== null && !empty($cuentasJson)){
    //         foreach($cuentasJson as $cuenta){
    //             if($NroCuenta == $cuenta->GetNroCuenta()){
    //                 $cuentaJson = $cuenta;
    //                 break;
    //             }
    //         }
    //     }        
    //     return $cuentaJson;
    // } 
    
    // public static function ObtenerCuentasPorDni($usuarioDni,$rutaArchivoJson) {
    //     $cuentasUsuario = Array();
    //     $cuentasJson = Cuenta::JsonDeserialize($rutaArchivoJson);
        
    //     foreach($cuentasJson as $cuenta){
    //         if($cuenta->GetNroDocumento() === $usuarioDni) {
    //             $cuentasUsuario []= $cuenta;
    //         }
    //     }

    //     return $cuentasUsuario;
    // }  
    // public function ActualizarSaldoCuentaJson($cantidad,$rutaArchivoJson) {
    //     $retorno = false;

    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){
    //         if($cuentaCargada->GetNroCuenta() == $this->GetNroCuenta() &&  $cuentaCargada->GetTipoCuenta() === $this->GetTipoCuenta() && $cuentaCargada->GetMoneda() === $this->GetMoneda() ){
    //             $cuentaCargada->SetSaldo($this->saldo + $cantidad);
    //             // Actualizar JSON
    //             $cuentas = [];
    //             foreach ($cuentasCargadas as $cuenta) {
    //                 $cuentas[] = $cuenta->JsonSerialize();
    //             }
    //             file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));   
    //             $retorno = true; 
    //         }
    //     }   
    //     return $retorno;
    // }

    // public function GuardarImagen($nombreImagen,$directorioDestino) {
    //     $retorno = false;
    //     $carpeta_archivos = $directorioDestino;
    //     $nombre_archivo = $this->GetNroCuenta() . $this->GetTipoCuenta() . ".jpg";       
    //     $ruta_destino = $carpeta_archivos . $nombre_archivo;

    //     if (move_uploaded_file($nombreImagen,  $ruta_destino)){
    //         $retorno = true;
    //     }     
    //     return $retorno;
    // }

    // public static function MoverImagen($nombreArchivo,$carpetaOrigen,$carpetaDestino) {
    //     $retorno = false;
    //     $nombreImagen = $nombreArchivo; 

    //     $rutaOrigen = $carpetaOrigen . $nombreImagen;
    //     $rutaDestino = $carpetaDestino . $nombreImagen;

    //     if (file_exists($rutaOrigen)) {
    //         try {
    //             if (rename($rutaOrigen, $rutaDestino)) {
    //                 $retorno = true;
    //             }
    //         } finally {
    //             // Manejar la excepción si ocurre algún error al mover el archivo
    //             $retorno = false;
    //         }
    //     }
    
    //     return $retorno;
    // }

    // public static function ConsultarCuenta($tipoCuenta,$nroCuenta,$rutaArchivoJson) {            
    //     $cuenta = null;
    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){

    //         if($cuentaCargada->GetNroCuenta()== $nroCuenta){
    //             if($cuentaCargada->GetTipoCuenta()=== $tipoCuenta){
    //                 $cuentaEncontrada = $cuentaCargada;
    //                 $cuenta= $cuentaEncontrada;                        
    //                 break;
    //             }
    //         }
    //     }

    //     return $cuenta;
    // }

    // public function ModificarCuenta($nombre,$apellido,$nroDocumento,$mail) {
    //     $this->SetNombre($nombre);
    //     $this->SetApellido($apellido);
    //     $this->SetNroDocumento($nroDocumento);
    //     $this->SetMail($mail);
    // }

    // public function ModificarCuentaJson($nombre,$apellido,$nroDocumento,$mail,$rutaArchivoJson) {
    //     $retorno = false;

    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){
    //         if($cuentaCargada->GetNroCuenta() == $this->GetNroCuenta() &&  $cuentaCargada->GetTipoCuenta() === $this->GetTipoCuenta() && $cuentaCargada->GetMoneda() === $this->GetMoneda() ){
    //             $cuentaCargada->ModificarCuenta($nombre,$apellido,$nroDocumento,$mail);
    //             // Actualizar JSON
    //             $cuentas = [];
    //             foreach ($cuentasCargadas as $cuenta) {
    //                 $cuentas[] = $cuenta->JsonSerialize();
    //             }
    //             file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));   
    //             $retorno = true; 
    //         }
    //     }   
    //     return $retorno;
    // }

    
    // public function ModificarEstadoCuentaJson($estado,$rutaArchivoJson) {
    //     $retorno = false;

    //     $cuentasCargadas = Cuenta::JsonDeserialize($rutaArchivoJson);

    //     foreach($cuentasCargadas as $cuentaCargada){
    //         if($cuentaCargada->GetNroCuenta() == $this->GetNroCuenta() &&  $cuentaCargada->GetTipoCuenta() === $this->GetTipoCuenta() && $cuentaCargada->GetMoneda() === $this->GetMoneda()){
    //             $cuentaCargada->SetEstado($estado);
    //             // Actualizar JSON
    //             $cuentas = [];
    //             foreach ($cuentasCargadas as $cuenta) {
    //                 $cuentas[] = $cuenta->JsonSerialize();
    //             }
    //             file_put_contents($rutaArchivoJson, json_encode($cuentas, JSON_PRETTY_PRINT));   
    //             $retorno = true; 
    //         }
    //     }   
    //     return $retorno;
    // }
    
}

?>