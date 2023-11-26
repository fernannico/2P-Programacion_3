<?php

    class Ajuste {
        public $idAjuste;
        public $nroCuenta;
        public $idOperacion;
        public $tipoOperacion;
        public $monto;
        public $motivo;
        
        public function crearAjuste()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (nroCuenta,tipoOperacion,idOperacion,motivo,monto) VALUES (:nroCuenta,:tipoOperacion,:idOperacion,:motivo,:monto)");

            $consulta->bindParam(':nroCuenta', $this->nroCuenta);
            $consulta->bindParam(':tipoOperacion', $this->tipoOperacion);
            $consulta->bindParam(':idOperacion', $this->idOperacion);
            $consulta->bindParam(':motivo', $this->motivo);
            $consulta->bindParam(':monto', $this->monto);

            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        }

    }
