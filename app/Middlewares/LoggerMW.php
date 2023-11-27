<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

    class LoggerMW
    {
        public function __invoke(Request $request, RequestHandler $handler): Response
        {
            $ruta = $request->getUri()->getPath();
            // $metodo = $request->getMethod();
            $logMessage = $this->generarLogMessage($ruta);
            $this->escribirLog($logMessage);
    
            return $handler->handle($request);
        }
    
        private function generarLogMessage($ruta)
        {
            $fecha = date('Y-m-d H:i:s');
            $accion = $this->extraerAccion($ruta);
            return "Fecha: $fecha | Acción solicitada: $accion";
        }
    
        private function extraerAccion($ruta)
        {
            $parts = explode('/', $ruta);
            return end($parts);
        }
    
        private function escribirLog($logMessage)
        {
            $objetoAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDatos->prepararConsulta("INSERT INTO loger (detalle) VALUES (:detalle)");
            $consulta->bindParam(':detalle', $logMessage);
            $consulta->execute();
        }
    }
    
