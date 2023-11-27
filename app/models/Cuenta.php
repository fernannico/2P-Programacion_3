<?php

class Cuenta {
    public $nroCuenta;
    public $nombre;             //usr
    public $apellido;           //usr
    public $tipoDocumento;      //usr
    public $nroDocumento;       //usr
    public $mail;               //usr
    public $contrasena;         //usr
    public $tipoCuenta;
    public $moneda;
    public $saldo;
    public $estado;
    public $imagen;

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
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO cuentas (nombre,apellido,nroDocumento,mail,contrasena,tipoCuenta,moneda,imagen,saldo) VALUES (:nombre,:apellido,:nroDocumento,:mail,:contrasena,:tipoCuenta,:moneda,:imagen,:saldo)");

        $consulta->bindParam(':nombre', $this->nombre);
        $consulta->bindParam(':apellido', $this->apellido);
        $consulta->bindParam(':nroDocumento', $this->nroDocumento);
        $consulta->bindParam(':mail', $this->mail);
        $consulta->bindParam(':contrasena', $this->contrasena);
        $consulta->bindParam(':tipoCuenta', $this->tipoCuenta);
        $consulta->bindParam(':moneda', $this->moneda);
        $consulta->bindParam(':imagen', $this->imagen);
        $consulta->bindParam(':saldo', $this->saldo);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function GuardarImagen($nombreImagen,$directorioDestino) {
        $retorno = false;
        $carpeta_archivos = $directorioDestino;
        $ultimoNroCuenta = (int)Cuenta::ObtenerUltimoNroCuenta();
        $nroCuenta = ($ultimoNroCuenta);
        $nombre_archivo = $nroCuenta . $this->tipoCuenta . ".jpg";       
        $ruta_destino = $carpeta_archivos . $nombre_archivo;

        if (move_uploaded_file($nombreImagen,  $ruta_destino)){
            $retorno = true;
        }     
        return $retorno;
    }

    public static function MoverImagen($nombreImagen,$carpetaOrigen,$directorioDestino) {
        $retorno = false;
        // $nombreImagen = $nombreArchivo; 

        $rutaOrigen = $carpetaOrigen . $nombreImagen;
        $rutaDestino = $directorioDestino . $nombreImagen;

        if (file_exists($rutaOrigen)) {
            try {
                if (rename($rutaOrigen, $rutaDestino)) {
                    $retorno = true;
                }
            } finally {
                // Manejar la excepción si ocurre algún error al mover el archivo
                $retorno = false;
            }
        }
    
        return $retorno;
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
        return $cuentasEncontradas;
    }   

    public static function ObtenerListaDnis()
    {
        $dnisEncontrados = Array();
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nroDocumento FROM cuentas");
        $consulta->execute();
        
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($resultados as $resultado) {
            $dnisEncontrados[] = $resultado['nroDocumento'];
        }
        
        return $dnisEncontrados;        
    }

    public static function ObtenerCuentaPorDniTipo($nroDocumento,$tipoCuenta)
    {
        $cuentaEncontrada = false;
        $listaCuentas = Cuenta::ObtenerCuentasPorNroDocumento($nroDocumento);

        if(!empty($listaCuentas)){
            foreach($listaCuentas as $cuenta){
                if($cuenta->tipoCuenta == $tipoCuenta){
                    $cuentaEncontrada = $cuenta;
                    break;
                }
            }
        }
        return $cuentaEncontrada;        
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

    public static function ObtenerUltimoNroCuenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("SELECT MAX(nroCuenta) AS ultimo_nroCuenta FROM cuentas");
        $consulta->execute();
    
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); // Obtener el resultado como un array asociativo
        $ultimo_nroCuenta = $resultado['ultimo_nroCuenta']; // Capturar el valor del último ID
        
        return $ultimo_nroCuenta;
    }

    public static function ActualizarSaldo($nroCuenta, $monto)
    {
        $retorno = false;
        $cuenta = Cuenta::ObtenerCuentaPorNroCuenta($nroCuenta);
        $saldo = $cuenta->saldo;
        
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

    public function __toString() {
        return 
        "nro Cuenta: " . $this->nroCuenta .
        "<br>nombre: " . $this->nombre .
        "<br>apellido: " . $this->apellido .
        "<br>tipo Documento: " . $this->tipoDocumento .
        "<br>nro Documento: " . $this->nroDocumento .
        "<br>mail: " . $this->mail .
        "<br>tipo Cuenta: " . $this->tipoCuenta .
        "<br>moneda: " . $this->moneda .
        "<br>saldo: " . $this->saldo . 
        "<br>estado: " . $this->estado; 
    }

}

?>