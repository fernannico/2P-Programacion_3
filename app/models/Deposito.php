<?php

    class Deposito /*implements JsonSerializable*/{
        public $id;
        public $fecha;
        public $nroCuenta;
        public $tipoCuenta;
        public $moneda;
        public $deposito;
        public $saldo;

        public function crearDeposito()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO depositos (nroCuenta, tipoCuenta, moneda, deposito, saldo) VALUES (:nroCuenta,:tipoCuenta,:moneda,:deposito,:saldo)");

            // $consulta->bindParam(':id', $this->nombre);
            // $consulta->bindParam(':fecha', $this->apellido);
            $consulta->bindParam(':nroCuenta', $this->nroCuenta);
            $consulta->bindParam(':tipoCuenta', $this->tipoCuenta);
            $consulta->bindParam(':moneda', $this->moneda);
            $consulta->bindParam(':deposito', $this->deposito);
            $consulta->bindParam(':saldo', $this->saldo);

            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        }
    }