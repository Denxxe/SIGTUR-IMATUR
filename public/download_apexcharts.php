<?php
/**
 * Script temporal para descargar ApexCharts localmente
 */
$url = 'https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js';
$destino = __DIR__ . '/assets/libs/apexcharts.min.js';

echo "Descargando ApexCharts...\n";

$contexto = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'Mozilla/5.0'
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$contenido = file_get_contents($url, false, $contexto);

if ($contenido !== false && strlen($contenido) > 1000) {
    file_put_contents($destino, $contenido);
    echo "✅ ApexCharts descargado exitosamente (" . round(strlen($contenido)/1024) . " KB)\n";
    echo "<br>Archivo guardado en: $destino\n";
    echo "<br><a href='index.php'>Ir al sistema</a>";
} else {
    echo "❌ Error al descargar. Intentando URL alternativa...\n";
    
    $url2 = 'https://unpkg.com/apexcharts@3.49.0/dist/apexcharts.min.js';
    $contenido2 = file_get_contents($url2, false, $contexto);
    
    if ($contenido2 !== false && strlen($contenido2) > 1000) {
        file_put_contents($destino, $contenido2);
        echo "✅ ApexCharts descargado desde fuente alternativa (" . round(strlen($contenido2)/1024) . " KB)\n";
    } else {
        echo "❌ No se pudo descargar. Verifica tu conexión a Internet.\n";
    }
}
