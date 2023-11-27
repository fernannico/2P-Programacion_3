<?php

    class Retiro {
        public $id;
        public $fecha;
        public $nroCuenta;
        public $tipoCuenta;
        public $moneda;
        public $retiro;
        public $saldo;

        public function crearRetiro()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO retiros (nroCuenta, tipoCuenta, moneda, retiro, saldo) VALUES (:nroCuenta,:tipoCuenta,:moneda,:retiro,:saldo)");

            // $consulta->bindParam(':id', $this->nombre);
            // $consulta->bindParam(':fecha', $this->apellido);
            $consulta->bindParam(':nroCuenta', $this->nroCuenta);
            $consulta->bindParam(':tipoCuenta', $this->tipoCuenta);
            $consulta->bindParam(':moneda', $this->moneda);
            $consulta->bindParam(':retiro', $this->retiro);
            $consulta->bindParam(':saldo', $this->saldo);

            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        }

        public function GetMonto() {
            return $this->retiro;
        }
        
        public static function ObtenerTodosRetiros()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM retiros");
            $consulta->execute();
            
            return $consulta->fetchAll(PDO::FETCH_CLASS, 'Retiro');
        }

        public static function ObtenerRetiroPorID($id)
        {
            $retiroBuscado = false;
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nroCuenta, tipoCuenta, moneda, retiro, saldo FROM retiros WHERE id = :id");
            $consulta->bindParam(":id", $id);
            $consulta->execute();
            
            $retiroBuscado = $consulta->fetchObject('Retiro');
            // var_dump($retiroBuscado);
            return $retiroBuscado;
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
    
        public static function ObtenerRetirosEntreFechas($fechaInicio, $fechaFin) {
            $retirosEntreFechas = Array();
            $fechas = Retiro::ObtenerConjuntoFechas($fechaInicio,$fechaFin);
            $retiros = Retiro::ObtenerTodosRetiros();
            
            // if($fechas !== null && $retiros !== null && !empty($retiros)){
                foreach($fechas as $fecha){
                    foreach($retiros as $retiro){
                        $fechaRetiro = new DateTime($retiro->fecha);
                        $fechaRetiroFormateada = $fechaRetiro->format('d-m-Y');            
                        if($fechaRetiroFormateada == $fecha){
                            // var_dump($retiro);
                            $retirosEntreFechas[] = $retiro;
                        }
                    }
                }
            // }
    
            return $retirosEntreFechas;
        }
        public static function CompararPorNumeroDeCuenta($a, $b){
            if ($a->nroCuenta == $b->nroCuenta) {
                return 0;
            }
            return ($a->nroCuenta < $b->nroCuenta) ? -1 : 1;
        }
        
        public static function OrdenarRetirosPorNumeroCuenta($retiros){
            usort($retiros, 'Retiro::CompararPorNumeroDeCuenta');
            return $retiros;
        }
        public function __toString() {
            return 
            // "id " . $this->id .
            // "<br>fecha: " . $this->fecha .
            "<br>nroCuenta: " . $this->nroCuenta .
            "<br>tipoCuenta: " . $this->tipoCuenta .
            "<br>moneda: " . $this->moneda .
            "<br>retiro: " . $this->retiro .
            "<br>saldo: " . $this->saldo;
        }
    }