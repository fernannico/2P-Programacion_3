<?php

class Usuario {
    public $mail;               //usr
    public $contrasena;         //usr
    public $rol;

    
    public function CrearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuario (mail,contrasena,rol) VALUES (:mail,:contrasena,:rol)");

        $consulta->bindParam(':mail', $this->mail);
        $consulta->bindParam(':contrasena', $this->contrasena);
        $consulta->bindParam(':rol', $this->rol);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
        
    }

    // public static function ontener() : Returntype {
        
    // }

}