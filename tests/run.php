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
require_once APP_ROOT . '/app/models/Nomina.php';
require_once APP_ROOT . '/app/models/Feriado.php';

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

// =====================================================================
// Nómina — motor de cálculo quincenal (mig. 072, fase N-A)
// =====================================================================
// Los porcentajes se inyectan para no depender de la BD: aquí se prueba la
// FÓRMULA, no el catálogo. Los valores son los de la plantilla real del
// cliente documentada en docs/PLAN_MODULO_NOMINA.md §3.
$PN = [
    'nomina_bono_transporte_mensual' => 12.50,
    'nomina_monto_por_hijo'          => 6.50,
    'nomina_becas_por_hijo'          => 12.50,
    'nomina_semanas_default'         => 4,
    'nomina_pct_sso_trabajador'      => 2,
    'nomina_pct_faov_trabajador'     => 1,
    'nomina_pct_lrppf_trabajador'    => 0.5,
    'nomina_pct_sso_patronal'        => 4,
    'nomina_pct_faov_patronal'       => 2,
    'nomina_pct_rpe_patronal'        => 1.7,
    'nomina_dias_bono_fin_anio'      => 150,
    'nomina_dias_base_anio'          => 360,
    'nomina_dias_bono_vac_base'      => 75,
];

echo "\n== Nomina::calcular — asignaciones ==\n";
// Caso del DEFECTO #1 de la plantilla del cliente: con 23+ años de servicio su
// hoja aplica el 30 % al sueldo MENSUAL y paga 112,80. Sobre el quincenal (188)
// corresponden 56,40. Es el valor verificado en el plan, §5 punto 1.
$c = Nomina::calcular([
    'sueldo_base_mensual'  => 376,
    'pct_antiguedad'       => 30,
    'anios_administracion' => 23,
    'semanas'              => 4,
], $PN);
eq($c['sueldo_base_quincenal'],   188.00, 'base quincenal = mensual / 2');
eq($c['prima_antiguedad'],         56.40, 'prima antigüedad tope 30% sobre el QUINCENAL (la plantilla paga 112,80)');
eq($c['bono_transporte'],           6.25, 'bono transporte = mensual 12,50 / 2, igual para todos');
eq($c['total_asignaciones'],       62.65, 'asignaciones = profesionalización + antigüedad + transporte + hijos');
eq($c['total_sueldo_normal'],     250.65, 'total sueldo normal = base + asignaciones');
eq($c['bono_50'],                  94.00, 'bono 50% = base quincenal / 2');
eq($c['dias_habiles_bono_vac'],       98, 'días hábiles = 75 base + 23 años de administración');

// Prima de profesionalización por grado, y prima por hijos
$c2 = Nomina::calcular([
    'sueldo_base_mensual'    => 376,
    'pct_profesionalizacion' => 20,     // TSU
    'n_hijos'                => 3,
    'semanas'                => 4,
], $PN);
eq($c2['prima_profesionalizacion'], 37.60, 'prima profesionalización TSU 20% sobre el quincenal');
eq($c2['prima_por_hijos'],          19.50, 'prima por hijos = 3 x 6,50');
eq($c2['becas'],                    37.50, 'becas = 3 x 12,50');

echo "\n== Nomina::calcular — deducciones y aportes ==\n";
eq($c['faov_trabajador'], 2.51, 'FAOV trabajador = 1% del total quincenal (250,65)');
check(abs($c['total_deducciones'] - ($c['sso_trabajador'] + $c['faov_trabajador'] + $c['lrppf_trabajador'])) < 0.02,
      'total deducciones = SSO + FAOV + LRPPF');
check(abs($c['neto_a_cobrar'] - ($c['total_sueldo_normal'] - $c['total_deducciones'])) < 0.02,
      'neto a cobrar = total - deducciones');
check(abs($c['total_aportes'] - ($c['sso_patronal'] + $c['faov_patronal'] + $c['rpe_patronal'])) < 0.02,
      'total aportes patronales = SSO + FAOV + RPE');
// El SSO patronal es el doble del del trabajador (4% vs 2%) sobre la misma base
check(abs($c['sso_patronal'] - 2 * $c['sso_trabajador']) < 0.02, 'SSO patronal 4% = doble del 2% del trabajador');

// Las semanas (pregunta N-2) solo mueven SSO/LRPPF/patronales, nunca el FAOV
$c4 = Nomina::calcular(['sueldo_base_mensual' => 376, 'pct_antiguedad' => 30, 'semanas' => 4], $PN);
$c5 = Nomina::calcular(['sueldo_base_mensual' => 376, 'pct_antiguedad' => 30, 'semanas' => 5], $PN);
check(abs($c5['sso_trabajador'] - $c4['sso_trabajador'] * 5 / 4) < 0.02, 'SSO escala con las semanas (x5 / x4)');
eq($c5['faov_trabajador'], $c4['faov_trabajador'], 'FAOV NO depende de las semanas (va sobre el total quincenal)');
eq($c5['total_sueldo_normal'], $c4['total_sueldo_normal'], 'las semanas no alteran el sueldo normal');

echo "\n== Nomina::calcular — alícuotas y divisas ==\n";
$c6 = Nomina::calcular([
    'sueldo_base_mensual'  => 376,
    'pct_antiguedad'       => 30,
    'anios_administracion' => 5,
    'monto_cesta_ticket'   => 300,
    'semanas'              => 4,
], $PN);
// diario = ((total x 2) + cesta) / 30
eq($c6['sueldo_normal_diario'], round(((250.65 * 2) + 300) / 30, 2), 'sueldo normal diario = ((quincenal x 2) + cesta) / 30');
eq($c6['dias_habiles_bono_vac'], 80, 'días hábiles = 75 + 5 años');
check(abs($c6['sueldo_integral_diario'] - ($c6['sueldo_normal_diario'] + $c6['alicuota_bono_vac'] + $c6['alicuota_bono_fin_anio'])) < 0.02,
      'sueldo integral diario = diario + alícuota vacacional + alícuota fin de año');
check($c6['alicuota_bono_fin_anio'] > $c6['alicuota_bono_vac'],
      'con 5 años, la alícuota de fin de año (150/360) supera la vacacional (80/360)');

// Bono de responsabilidad: se pacta en divisas y se paga al cambio; la quincena paga la mitad
$c7 = Nomina::calcular(['sueldo_base_mensual' => 376, 'divisas' => 100, 'tasa_dolar' => 36.58], $PN);
eq($c7['bono_responsabilidad'], 1829.00, 'bono responsabilidad = (100 divisas x 36,58) / 2');
eq($c['bono_responsabilidad'], 0.00, 'sin divisas registradas no hay bono de responsabilidad');

echo "\n== Nomina::calcular — comisión de servicio ==\n";
$cc = Nomina::calcular([
    'sueldo_base_mensual'       => 376,
    'pct_antiguedad'            => 30,
    'tipo_personal'             => 'Comisión de Servicio',
    'sueldo_dependencia_origen' => 100,
], $PN);
eq($cc['diferencia_comision'], 150.65, 'comisión paga la diferencia: 250,65 - 100 de la dependencia de origen');
eq($c['diferencia_comision'], 0.00, 'los demás tipos no llevan diferencia de comisión');
$cc2 = Nomina::calcular([
    'sueldo_base_mensual'       => 376,
    'tipo_personal'             => 'Comisión de Servicio',
    'sueldo_dependencia_origen' => 9999,
], $PN);
eq($cc2['diferencia_comision'], 0.00, 'si la dependencia paga más, la diferencia no se vuelve negativa');

echo "\n== Nomina::codigoGrado (mapeo de nivel académico) ==\n";
eq(Nomina::codigoGrado('TSU'),           'TSU',   'TSU → TSU');
eq(Nomina::codigoGrado('Ingeniero'),     'PROF',  'Ingeniero → PROF (profesional)');
eq(Nomina::codigoGrado('Licenciado'),    'PROF',  'Licenciado → PROF');
eq(Nomina::codigoGrado('Magíster'),      'MAEST', 'Magíster → MAEST');
eq(Nomina::codigoGrado('Doctorado'),     'DR',    'Doctorado → DR');
eq(Nomina::codigoGrado('Bachiller'),     'BACH',  'Bachiller → BACH (0%)');
eq(Nomina::codigoGrado('Técnico Medio'), 'BACH',  'Técnico Medio → BACH, no TSU');
eq(Nomina::codigoGrado('  tsu  '),       'TSU',   'tolera espacios y minúsculas');
// El silencio es el defecto #7 de la plantilla: un valor no reconocido debe
// AVISARSE, no pagarse como 0% sin que nadie se entere.
check(Nomina::codigoGrado('Universitario') === null, 'valor ambiguo ("Universitario") → null para que se reporte');
check(Nomina::codigoGrado(null)            === null, 'sin nivel académico → null');
check(Nomina::codigoGrado('')              === null, 'nivel académico vacío → null');
eq(Nomina::codigoGrado('Universitario', 'PROF'), 'PROF', 'la corrección manual de la ficha manda sobre el mapeo');

echo "\n== Nomina::tipoPersonal (5 tipos = 5 hojas del formato) ==\n";
$mk = fn(array $x) => (object)array_merge(
    ['institucion_origen' => 'IMATUR', 'nivel_jerarquico' => 'Adscrito',
     'tipo_contrato' => 'Fijo', 'clasificacion' => 'Empleado'], $x);
eq(Nomina::tipoPersonal($mk(['nivel_jerarquico' => 'Presidencia'])),      'Alto Nivel',      'Presidencia → Alto Nivel');
eq(Nomina::tipoPersonal($mk(['nivel_jerarquico' => 'Dirección'])),        'Alto Nivel',      'Dirección → Alto Nivel');
eq(Nomina::tipoPersonal($mk(['tipo_contrato' => 'Contratado'])),          'Contratados',     'Contratado → Contratados');
eq(Nomina::tipoPersonal($mk(['clasificacion' => 'Obrero'])),              'Obreros Fijos',   'Obrero fijo → Obreros Fijos');
eq(Nomina::tipoPersonal($mk([])),                                         'Empleados Fijos', 'fijo administrativo → Empleados Fijos');
eq(Nomina::tipoPersonal($mk(['institucion_origen' => 'Gobernación'])),    'Comisión de Servicio', 'origen ≠ IMATUR → Comisión de Servicio');
// La comisión manda sobre el nivel: su hoja calcula la diferencia contra la dependencia de origen
eq(Nomina::tipoPersonal($mk(['institucion_origen' => 'Alcaldía', 'nivel_jerarquico' => 'Dirección'])),
   'Comisión de Servicio', 'un director en comisión va a la hoja de Comisión, no a Alto Nivel');

echo "\n== Nomina::aniosAdministracion ==\n";
$e1 = (object)['fecha_ingreso' => '2020-01-01', 'fecha_ingreso_administracion' => '2010-06-01'];
eq(Nomina::aniosAdministracion($e1, '2026-07-31'), 16, 'usa la fecha de administración pública, no la de IMATUR');
$e2 = (object)['fecha_ingreso' => '2010-06-01', 'fecha_ingreso_administracion' => null];
eq(Nomina::aniosAdministracion($e2, '2026-07-31'), 16, 'sin fecha de administración cae a la de ingreso');
$e3 = (object)['fecha_ingreso' => '2030-01-01', 'fecha_ingreso_administracion' => null];
eq(Nomina::aniosAdministracion($e3, '2026-07-31'), 0, 'ingreso futuro → 0 años');

// =====================================================================
// Feriado — Pascua y feriados movibles (generador anual)
// =====================================================================
// Sin esto, Carnaval y Semana Santa se cargan a mano cada año y, si nadie
// lo hace, el conteo de días hábiles de vacaciones falla EN SILENCIO.

echo "\n== Feriado::pascua (algoritmo Gregoriano anónimo) ==\n";
// Fechas de referencia verificadas contra la mig. 071 y easter_date() de PHP.
eq(Feriado::pascua(2026), '2026-04-05', 'Pascua 2026');
eq(Feriado::pascua(2027), '2027-03-28', 'Pascua 2027');
eq(Feriado::pascua(2028), '2028-04-16', 'Pascua 2028');
eq(Feriado::pascua(2029), '2029-04-01', 'Pascua 2029 (el primer año sin cargar)');
// Casos límite conocidos del algoritmo: los extremos del rango posible.
eq(Feriado::pascua(2038), '2038-04-25', 'Pascua más tardía posible (25 de abril)');
eq(Feriado::pascua(2285), '2285-03-22', 'Pascua más temprana posible (22 de marzo)');

// Contraste contra easter_date() donde la extensión calendar esté disponible.
// Su rango llega solo hasta 2037 — motivo de más para no depender de ella:
// nuestra implementación sí resuelve 2038 y 2285, comprobados arriba.
if (function_exists('easter_date')) {
    $coinciden = true;
    for ($a = 2026; $a <= 2037; $a++) {
        if (date('Y-m-d', easter_date($a)) !== Feriado::pascua($a)) { $coinciden = false; break; }
    }
    check($coinciden, 'coincide con easter_date() de PHP en todo su rango (2026-2037)');
}

echo "\n== Feriado::movibles ==\n";
$m2026 = Feriado::movibles(2026);
eq(count($m2026), 4, '4 feriados movibles por año');
// Estas 4 fechas son exactamente las que cargó la mig. 071 a mano.
eq(array_keys($m2026), ['2026-02-16', '2026-02-17', '2026-04-02', '2026-04-03'],
   '2026 reproduce las fechas de la mig. 071');
eq($m2026['2026-02-16'], 'Lunes de Carnaval',  'nombra el Lunes de Carnaval');
eq($m2026['2026-04-03'], 'Viernes Santo',      'nombra el Viernes Santo');
eq(array_keys(Feriado::movibles(2027)), ['2027-02-08', '2027-02-09', '2027-03-25', '2027-03-26'],
   '2027 reproduce las fechas de la mig. 071');
eq(array_keys(Feriado::movibles(2028)), ['2028-02-28', '2028-02-29', '2028-04-13', '2028-04-14'],
   '2028 reproduce las fechas de la mig. 071 (año bisiesto)');

// Cada día derivado tiene que caer en su día de semana: es la comprobación que
// atrapa un offset equivocado, que de otro modo pasaría inadvertido.
echo "\n== Feriado::movibles — cada día cae en su día de semana ==\n";
$diaSemanaOk = true; $anioFalla = null;
foreach (range(2026, 2060) as $a) {
    $esperado = ['Lunes de Carnaval' => 'Mon', 'Martes de Carnaval' => 'Tue',
                 'Jueves Santo' => 'Thu', 'Viernes Santo' => 'Fri'];
    foreach (Feriado::movibles($a) as $fecha => $nombre) {
        if (date('D', strtotime($fecha)) !== $esperado[$nombre]) { $diaSemanaOk = false; $anioFalla = $a; break 2; }
    }
}
check($diaSemanaOk, 'en 2026-2060 cada feriado cae en su día de semana' . ($anioFalla ? " (falla en $anioFalla)" : ''));

echo "\n" . str_repeat('-', 48) . "\n";
echo "$tests pruebas ejecutadas, $fails fallo(s).\n";
exit($fails > 0 ? 1 : 0);
