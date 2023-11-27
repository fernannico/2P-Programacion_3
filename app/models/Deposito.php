<?php

class Deposito {
    public $id;
    public $fecha;
    public $nroCuenta;
    public $tipoCuenta;
    public $moneda;
    public $deposito;
    public $saldo;
    public $imagen;

    public function crearDeposito()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO depositos (nroCuenta, tipoCuenta, moneda, deposito, saldo,imagen) VALUES (:nroCuenta,:tipoCuenta,:moneda,:deposito,:saldo,:imagen)");

        // $consulta->bindParam(':id', $this->nombre);
        // $consulta->bindParam(':fecha', $this->apellido);
        $consulta->bindParam(':nroCuenta', $this->nroCuenta);
        $consulta->bindParam(':tipoCuenta', $this->tipoCuenta);
        $consulta->bindParam(':moneda', $this->moneda);
        $consulta->bindParam(':deposito', $this->deposito);
        $consulta->bindParam(':saldo', $this->saldo);
        $consulta->bindParam(':imagen', $this->imagen);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function GetMonto() {
        return $this->deposito;
    }

    public function GuardarImagen($nombreImagen) {
        $retorno = false;
        $carpeta_archivos = "ImagenesDeDepositos2023/";
        $ultimoId = (int)Deposito::ObtenerUltimoId();
        $id = ($ultimoId+1);

        $nombre_archivo = $this->tipoCuenta . "_" . $this->nroCuenta . "_" . $id . ".jpg";       
        $ruta_destino = $carpeta_archivos . $nombre_archivo;

        if (move_uploaded_file($nombreImagen,  $ruta_destino)){
            $retorno = true;
        }     
        return $retorno;
    }
    public static function ObtenerTodosDepositos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM depositos");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Deposito');
    }

    public static function ObtenerDepositoPorID($id)
    {
        $depositoBuscado = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nroCuenta, tipoCuenta, moneda, deposito, saldo FROM depositos WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $depositoBuscado = $consulta->fetchObject('Deposito');
        // var_dump($depositoBuscado);
        return $depositoBuscado;
    }   
    public static function ObtenerUltimoId()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("SELECT MAX(id) AS ultimo_id FROM depositos");
        $consulta->execute();
    
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); 
        $ultimo_id = $resultado['ultimo_id']; 
        
        return $ultimo_id;
    }
    
    public static function ObtenerConjuntoFechas($fechaInicio, $fechaFin) {
        $fechas = Array();
    
        $fechaActual = strtotime($fechaInicio);
    
        while ($fechaActual <= strtotime($fechaFin)) {
            $fechas[] = date("d-m-Y", $fechaActual);
            $fechaActual = strtotime("+1 day", $fechaActual);
        }
    
        return $fechas;
    }

    public static function ObtenerDepositosEntreFechas($fechaInicio, $fechaFin) {
        $depositosEntreFechas = Array();
        $fechas = Deposito::ObtenerConjuntoFechas($fechaInicio,$fechaFin);
        $depositos = Deposito::ObtenerTodosDepositos();
        
        // if($fechas !== null && $depositos !== null && !empty($depositos)){
            foreach($fechas as $fecha){
                foreach($depositos as $deposito){
                    $fechaDeposito = new DateTime($deposito->fecha);
                    $fechaDepositoFormateada = $fechaDeposito->format('d-m-Y');            
                    if($fechaDepositoFormateada == $fecha){
                        // var_dump($deposito);
                        $depositosEntreFechas[] = $deposito;
                    }
                }
            }
        // }

        return $depositosEntreFechas;
    }
    public static function CompararPorNumeroDeCuenta($a, $b){
        if ($a->nroCuenta == $b->nroCuenta) {
            return 0;
        }
        return ($a->nroCuenta < $b->nroCuenta) ? -1 : 1;
    }
    
    public static function OrdenarDepositosPorNumeroCuenta($depositos){
        usort($depositos, 'Deposito::CompararPorNumeroDeCuenta');
        return $depositos;
    }

    public function __toString() {
        return 
        "<br>id " . $this->id .
        "<br>fecha: " . $this->fecha .
        "<br>nroCuenta: " . $this->nroCuenta .
        "<br>tipoCuenta: " . $this->tipoCuenta .
        "<br>moneda: " . $this->moneda .
        "<br>deposito: " . $this->deposito .
        "<br>saldo: " . $this->saldo .
        "<br>imagen: " . $this->imagen;
    }

}