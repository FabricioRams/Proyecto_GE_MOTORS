<?php
declare(strict_types=1);

namespace GemMotors\Services;

use GemMotors\Config\App;

/**
 * Servicio para generación de PDFs (diagnósticos, hojas de ruta, boletas de servicio)
 * En un sistema real, esto usaría mPDF o DomPDF
 * Por ahora, simularemos la generación con un placeholder
 */
class PDFService
{
    /**
     * Genera un PDF de diagnóstico para una orden de trabajo
     * @param int $ordenId ID de la orden de trabajo
     * @return string Ruta o contenido del PDF generado
     */
    public static function generarDiagnostico(int $ordenId): string
    {
        // En un sistema real, aquí se generaría el PDF con mPDF/DomPDF
        // Por ahora, retornamos un placeholder indicando que se generó
        
        // Obtener datos de la orden y diagnósticos
        $orden = \GemMotors\Models\OrdenTrabajo::find($ordenId);
        if ($orden === null) {
            throw new \InvalidArgumentException('Orden de trabajo no encontrada');
        }
        
        $diagnosticos = $orden->getDiagnosticos();
        $vehiculo = $orden->getVehiculo();
        $cliente = $orden->getCliente();
        
        // Definir el stream de contenido por separado para calcular su longitud
        $stream = "BT /F1 24 Tf 100 700 Td (G&E Motors - Diagnostico OBD-II) Tj ET ";
        $stream .= "BT /F1 12 Tf 100 650 Td (OT: {$orden->numero_ot}) Tj ET ";
        $stream .= "BT /F1 12 Tf 100 630 Td (Fecha: " . date('d/m/Y') . ") Tj ET ";
        
        $yPosition = 530;
        foreach ($diagnosticos as $index => $diagnostico) {
            $codigo = $diagnostico->codigos_falla[0] ?? 'Desconocido';
            $stream .= "BT /F1 10 Tf 120 {$yPosition} Td (" . ($index + 1) . ". " . $codigo . ") Tj ET ";
            $yPosition -= 20;
        }
        
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdfContent .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdfContent .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n";
        $pdfContent .= "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream\nendobj\n";
        $pdfContent .= "trailer\n<< /Size 5 /Root 1 0 R >>\n%%EOF";
        
        // En un sistema real, guardaríamos el PDF en un directorio temporal o en Cloudinary
        // y retornaríamos la URL o ruta
        // Por ahora, retornamos el contenido codificado en base64 para simular
        return base64_encode($pdfContent);
    }

    /**
     * Genera un PDF de hoja de ruta para una orden de trabajo
     * @param int $ordenId ID de la orden de trabajo
     * @return string Ruta o contenido del PDF generado
     */
    public static function generarHojaDeRuta(int $ordenId): string
    {
        // Similar a generarDiagnostico pero con formato de hoja de ruta
        $orden = \GemMotors\Models\OrdenTrabajo::find($ordenId);
        if ($orden === null) {
            throw new \InvalidArgumentException('Orden de trabajo no encontrada');
        }
        
        // Simular generación de PDF de hoja de ruta
        $body = "BT /F1 24 Tf 100 700 Td (G&E Motors - Hoja de Ruta) Tj ET BT /F1 12 Tf 100 650 Td (OT: {$orden->numero_ot}) Tj ET";
        $pdfContent = "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length " . strlen($body) . " >>\nstream\n" . $body . "\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f\ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n500\n%%EOF";

        return base64_encode($pdfContent);
    }

    /**
     * Genera un PDF de boleta de servicio para una orden de trabajo
     * @param int $ordenId ID de la orden de trabajo
     * @return string Ruta o contenido del PDF generado
     */
    public static function generarBoletaDeServicio(int $ordenId): string
    {
        // Similar a generarDiagnostico pero con formato de boleta de servicio
        $orden = \GemMotors\Models\OrdenTrabajo::find($ordenId);
        if ($orden === null) {
            throw new \InvalidArgumentException('Orden de trabajo no encontrada');
        }
        
        // Calcular total (repuestos + mano de obra)
        $totalRepuestos = $orden->getTotalRepuestos();
        $mecanico = $orden->getMecanico();
        $horasTrabajo = 2.0; // Placeholder ya que no hay una lista de asignaciones múltiples aún
        $costoManoObra = $horasTrabajo * 25.00; // Tarifa horaria ejemplo
        $total = $totalRepuestos + $costoManoObra;
        
        $body = "BT /F1 24 Tf 100 700 Td (Boleta G&E Motors) Tj ET";
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length " . strlen($body) . " >>\nstream\n" . $body . "\nendstream\nendobj\ntrailer\n<< /Size 5 /Root 1 0 R >>\n%%EOF";
        
        return base64_encode($pdfContent);
    }
}