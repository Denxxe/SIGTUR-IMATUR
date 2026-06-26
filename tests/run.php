<?php
/**
 * Suite mínima de pruebas — SIGTUR-IMATUR
 *
 * Runner sin dependencias (el proyecto no usa Composer). Cubre la lógica PURA
 * de cálculo crítica que NO toca la base de datos: política de contraseñas,
 * derecho/antigüedad de vacaciones, edad y tiempo de servicio.
 *
 * Ejecutar:  php tests/run.php
 * Salida:    lista de checks y código de salida 0 (OK) / 1 (hubo fallos).
 */

define('APP_ROOT', dirname(__DIR__));

// Solo clases con lógica pura — no se instancia Database ni se conecta a la BD.
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/core/Util.php';
require_once APP_ROOT . '/app/models/Usuario.php';
require_once APP_ROOT . '/app/models/Vacacion.php';
require_once APP_ROOT . '/app/models/Empleado.php';

$tests = 0; $fails = 0;

function check(bool $cond, string $msg): void {
    global $tests, $fails;
    $tests++;
    if ($cond) { echo "  \xE2\x9C\x93 $msg\n"; }
    else { $fails++; echo "  \xE2\x9C\x97 FALLA: $msg\n"; }
}
function eq($obtenido, $esperado, string $msg): void {
    check($obtenido === $esperado, $msg . " (esperado " . var_export($esperado, true) . ", obtenido " . var_export($obtenido, true) . ")");
}
function diasAtras(string $mod): string {
    return (new DateTime('today'))->modify($mod)->format('Y-m-d');
}

echo "\n== Usuario::passwordPolicyError ==\n";
check(Usuario::passwordPolicyError('abc')      !== null, 'rechaza contraseña corta');
check(Usuario::passwordPolicyError('abcdefgh') !== null, 'rechaza sin número');
check(Usuario::passwordPolicyError('12345678') !== null, 'rechaza sin letra');
check(Usuario::passwordPolicyError('clave123') === null, 'acepta válida (letra+número, >=8)');

echo "\n== Vacacion::diasPorAnios (15 + años, tope 30) ==\n";
eq(Vacacion::diasPorAnios(0),   15, '0 años → 15');
eq(Vacacion::diasPorAnios(5),   20, '5 años → 20');
eq(Vacacion::diasPorAnios(20),  30, '20 años → 30 (tope)');
eq(Vacacion::diasPorAnios(100), 30, '100 años → 30 (tope)');

echo "\n== Vacacion::aniosServicio / derechoAcumulado ==\n";
$emp3 = (object)['fecha_ingreso' => diasAtras('-3 years -2 days'), 'fecha_ingreso_administracion' => null];
eq(Vacacion::aniosServicio($emp3), 3, '3 años cumplidos de servicio');
$emp2 = (object)['fecha_ingreso' => diasAtras('-2 years -2 days'), 'fecha_ingreso_administracion' => null];
eq(Vacacion::derechoAcumulado($emp2), 31, 'acumulado 2 años = 15 + 16');
$empAdmin = (object)['fecha_ingreso' => diasAtras('-1 years'), 'fecha_ingreso_administracion' => diasAtras('-5 years -2 days')];
eq(Vacacion::aniosServicio($empAdmin), 5, 'usa fecha_ingreso_administracion (comisión) si existe');

echo "\n== Util::edad ==\n";
eq(Util::edad(diasAtras('-20 years -1 day')), 20, '20 años cumplidos');
check(Util::edad(diasAtras('+1 day')) === null, 'fecha futura → null');
check(Util::edad('') === null, 'fecha vacía → null');
check(Util::edad('no-es-fecha') === null, 'fecha inválida → null');

echo "\n== Empleado::tiempoServicio ==\n";
check(strpos(Empleado::tiempoServicio(diasAtras('-1 years -2 months')), '1 año') !== false, 'incluye "1 año"');
eq(Empleado::tiempoServicio(''), '—', 'sin fecha → —');
eq(Empleado::tiempoServicio(diasAtras('+10 days')), '—', 'ingreso futuro → —');

echo "\n" . str_repeat('-', 48) . "\n";
echo "$tests pruebas ejecutadas, $fails fallo(s).\n";
exit($fails > 0 ? 1 : 0);
