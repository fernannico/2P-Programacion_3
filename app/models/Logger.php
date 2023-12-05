<?php
// require_once('tcpdf/tcpdf.php'); // Asegúrate de usar la ruta correcta a TCPDF
// require_once('fpdf/fpdf.php'); // Asegúrate de usar la ruta correcta a FPDF
require_once '../vendor/setasign/fpdf/fpdf.php';

class Logger {
    public $id;
    public $detalle;

    public static function ObtenerLoggerComoPDF()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM loger ORDER BY `id` DESC");
        $consulta->execute();
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        // Crea una instancia de FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12); // Establece la fuente (Arial, tamaño 12) 
                
        // Contenido del PDF
        foreach ($resultados as $fila) {
            foreach ($fila as $valor) {
                $pdf->Cell(0, 10, $valor, 0, 1); // Escribe cada valor de la fila en el PDF
            }
        }

        // Nombre del archivo con marca de tiempo
        $nombreArchivo = 'registro_log.pdf';

        // Ruta donde se guardará el archivo
        $rutaArchivo = '../app/' . $nombreArchivo; // Ruta relativa a una carpeta en tu proyecto

        // Guarda el archivo en la ruta especificada
        $pdf->Output($rutaArchivo, 'F');

        return $nombreArchivo; // Devuelve el nombre del archivo guardado
    }
}
