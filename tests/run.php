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
require_once APP_ROOT . '/app/models/BonoVacacional.php';
require_once APP_ROOT . '/app/models/Feriado.php';
require_once APP_ROOT . '/app/models/Liquidacion.php';
require_once APP_ROOT . '/app/models/CodificacionBienes.php';
require_once APP_ROOT . '/app/models/Inventario.php';
require_once APP_ROOT . '/app/models/Taller.php';
require_once APP_ROOT . '/app/core/Controller.php';
require_once APP_ROOT . '/app/helpers/session_helper.php';

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
// cliente documentada en docs/REGLAS_NEGOCIO_Nomina.md.
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

echo "\n== Nomina::cobraResponsabilidad (solo alto nivel, 2026-10-11) ==\n";
check(Nomina::cobraResponsabilidad($mk(['nivel_jerarquico' => 'Dirección'])), 'director fijo → cobra');
check(Nomina::cobraResponsabilidad($mk(['nivel_jerarquico' => 'Presidencia', 'tipo_contrato' => 'Contratado'])), 'alto nivel contratado → cobra');
check(Nomina::cobraResponsabilidad($mk(['institucion_origen' => 'Alcaldía', 'nivel_jerarquico' => 'Dirección'])), 'director EN COMISIÓN → cobra (va en la hoja de Comisión, pero es alto nivel)');
check(!Nomina::cobraResponsabilidad($mk(['institucion_origen' => 'Gobernación', 'nivel_jerarquico' => 'Coordinación'])), 'comisión de servicio que no es alto nivel → NO cobra (antes sí)');
check(!Nomina::cobraResponsabilidad($mk(['nivel_jerarquico' => 'Coordinación'])), 'coordinador de IMATUR → no cobra');
check(Nomina::cobraResponsabilidad((object)['nivel_cargo' => 'Dirección']), 'acepta el alias nivel_cargo de Empleado::find()');

echo "\n== BonoVacacional::condicion (N-1: los días van por fijo/contratado, mig. 085) ==\n";
eq(BonoVacacional::condicion($mk([])),                                   'Fijo',       'empleado fijo → Fijo (85)');
eq(BonoVacacional::condicion($mk(['clasificacion' => 'Obrero'])),        'Fijo',       'obrero fijo → Fijo (85)');
eq(BonoVacacional::condicion($mk(['nivel_jerarquico' => 'Dirección'])), 'Fijo',       'alto nivel fijo → Fijo (85)');
eq(BonoVacacional::condicion($mk(['nivel_jerarquico' => 'Dirección', 'tipo_contrato' => 'Contratado'])),
   'Contratado', 'alto nivel contratado → Contratado (45)');
eq(BonoVacacional::condicion($mk(['tipo_contrato' => 'Contratado'])),    'Contratado', 'contratado → Contratado (45)');

echo "\n== Nomina::calcular — días del bono en la alícuota (base de la condición + años) ==\n";
$cv = Nomina::calcular(['sueldo_base_mensual' => 376, 'anios_administracion' => 4,
                        'dias_bono_vac_base' => 85, 'semanas' => 4], $PN);
eq($cv['dias_habiles_bono_vac'], 89, 'fijo con 4 años → 85 + 4 = 89 días');
$cv = Nomina::calcular(['sueldo_base_mensual' => 376, 'anios_administracion' => 2,
                        'dias_bono_vac_base' => 45, 'semanas' => 4], $PN);
eq($cv['dias_habiles_bono_vac'], 47, 'contratado con 2 años → 45 + 2 = 47 días');

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

// =====================================================================
// Liquidación de Prestaciones Sociales (mig. 086, fase N-E)
// =====================================================================
// Los valores esperados son los que YA calculó Excel en la hoja INTERESES de
// `LIQUIDACION MES JULIO 2026.xls` (datos de prueba, fórmulas reales). Los
// parámetros se inyectan: se prueba la fórmula, no la configuración.
$PL = [
    'liq_dias_garantia_trimestre'     => 15,
    'liq_dias_adicionales_anio'       => 2,
    'liq_dias_adicionales_desde_anio' => 2,
    'liq_dias_adicionales_tope'       => 30,
    'liq_dias_retroactivo_anio'       => 30,
    'liq_meses_fraccion_retroactivo'  => 6,
    'nomina_dias_mes'                 => 30,
    'nomina_dias_base_anio'           => 360,
    'liq_trimestre_modo'              => 'calendario',
];
$cerca = fn($a, $b, $tol = 1e-6) => abs((float)$a - (float)$b) < $tol;

echo "\n== Liquidacion::calcularMes — fila 17 de INTERESES ==\n";
$f17 = Liquidacion::calcularMes([
    'bono_transporte' => 12.5, 'dias_alic_vac' => 79, 'dias_alic_fin' => 120,
    'dias_deposito' => 15, 'anticipo' => 91,
], 0.0, 56.18 / 12, $PL);
check($cerca($f17['salario_diario'], 0.4166666666666667),  'H17 salario diario = G/30');
check($cerca($f17['alic_vac'], 0.0914351851851852),         'I17 alícuota bono vacacional = H×79/360');
check($cerca($f17['alic_fin'], 0.1388888888888889),         'J17 alícuota fin de año = H×120/360');
check($cerca($f17['salario_integral'], 0.6469907407407407), 'K17 salario integral = H+I+J');
check($cerca($f17['monto_deposito'], 9.70486111111111),     'M17 depósito = K×15 días');
check($cerca($f17['saldo_antes_interes'], -81.29513888888889), 'N17 = saldo anterior + M − anticipo');
check($cerca($f17['interes'], -3.8059674189814814),         'P17 interés = N × tasa BCV mensual %');
check($cerca($f17['saldo'], -85.10110630787037),            'Q17 saldo = N + P');

echo "\n== Liquidacion::calcularMeses — encadenado filas 15-18 ==\n";
$fila = fn($mes, $transp) => ['mes' => $mes, 'bono_transporte' => $transp, 'dias_alic_vac' => 79,
                              'dias_alic_fin' => 120, 'dias_deposito' => 0, 'anticipo' => 0];
$cadena = [$fila('2022-01-01', 1.75), $fila('2022-02-01', 1.75), $fila('2022-03-01', 12.5), $fila('2022-04-01', 12.5)];
$cadena[2]['dias_deposito'] = 15; $cadena[2]['anticipo'] = 91;
$tasasPlantilla = ['2022-01' => 58.35, '2022-02' => 57.99, '2022-03' => 56.18, '2022-04' => 55.95];
$led = Liquidacion::calcularMeses($cadena, $tasasPlantilla, $PL);
check($cerca($led['meses'][3]['saldo'], -89.06894538947482), 'Q18 sale igual que en Excel (saldo encadenado sin redondear)');
check($cerca($led['totales']['anticipos'], 91), 'total de anticipos = 91');
check($led['totales']['sin_tasa'] === [], 'sin meses faltos de tasa');
// Renglones como los devuelve la BD: ya traen las derivadas del cálculo anterior.
$conViejos = array_map(fn($r) => $r + ['total_mensual' => 0, 'salario_integral' => 0, 'monto_deposito' => 0,
                                       'interes' => 0, 'saldo' => 0, 'tasa_mensual' => null, 'id' => 1], $cadena);
$ledV = Liquidacion::calcularMeses($conViejos, $tasasPlantilla, $PL);
check($cerca($ledV['meses'][2]['monto_deposito'], 9.70486111111111), 'lo calculado reemplaza a lo leído de la BD (no quedan los 0 viejos)');
check($cerca($ledV['meses'][2]['tasa_mensual'], 56.18 / 12), 'la tasa del mes se toma de la tabla, no del valor viejo del renglón');
check($ledV['meses'][2]['id'] === 1, 'conserva el id del renglón para poder guardarlo');
unset($tasasPlantilla['2022-04']);
eq(Liquidacion::calcularMeses($cadena, $tasasPlantilla, $PL)['totales']['sin_tasa'], ['2022-04'],
   'un mes con saldo y sin tasa se reporta (no se inventa la tasa)');

echo "\n== Liquidacion::calcularMes — cambios de días de la plantilla ==\n";
$f24 = Liquidacion::calcularMes(['bono_transporte' => 12.5, 'dias_alic_vac' => 80, 'dias_alic_fin' => 120, 'dias_deposito' => 6], 0.0, null, $PL);
check($cerca($f24['salario_integral'], 0.6481481481481481), 'K24 con 80 días de bono vacacional');
check($cerca($f24['monto_deposito'], 3.888888888888889),    'M24 = K × 6 días adicionales');
check($f24['interes'] === 0.0,                              'sin tasa → interés 0 (lo reporta calcularMeses)');
$f27 = Liquidacion::calcularMes(['bono_transporte' => 12.5, 'dias_alic_vac' => 80, 'dias_alic_fin' => 150], 0.0, null, $PL);
check($cerca($f27['alic_fin'], 0.1736111111111111),         'J27 con 150 días de aguinaldo');

echo "\n== Liquidacion::diasDeposito (art. 142 a/b) ==\n";
eq(Liquidacion::diasDeposito('2020-01-10', '2020-01-01', '2025-06-30', $PL), 0.0,  'enero (no cierra trimestre) → 0');
eq(Liquidacion::diasDeposito('2020-01-10', '2020-03-01', '2025-06-30', $PL), 15.0, 'marzo cierra trimestre → 15');
eq(Liquidacion::diasDeposito('2020-02-10', '2020-03-01', '2025-06-30', $PL), 10.0, 'trimestre incompleto → 5 por mes trabajado');
eq(Liquidacion::diasDeposito('2020-01-10', '2021-01-01', '2025-06-30', $PL), 0.0,  'primer aniversario → sin días adicionales');
eq(Liquidacion::diasDeposito('2020-01-10', '2022-01-01', '2025-06-30', $PL), 2.0,  'segundo aniversario → 2 días adicionales');
eq(Liquidacion::diasDeposito('2020-01-10', '2023-01-01', '2025-06-30', $PL), 4.0,  'tercer aniversario → 4');
eq(Liquidacion::diasDeposito('2000-01-10', '2020-01-01', '2025-06-30', $PL), 30.0, 'año 20 → tope de 30');
eq(Liquidacion::diasDeposito('2020-01-10', '2025-05-01', '2025-05-20', $PL), 10.0, 'mes del egreso → lo que falta del trimestre');
eq(Liquidacion::diasDeposito('2020-01-10', '2025-07-01', '2025-05-20', $PL), 0.0,  'después del egreso → 0');
eq(Liquidacion::diasDeposito('2020-01-10', '2020-03-01', '2025-06-30', ['liq_trimestre_modo' => 'ingreso'] + $PL), 15.0,
   'modo «ingreso»: el 3.er mes desde el ingreso → 15');
eq(Liquidacion::diasDeposito('2020-01-10', '2022-01-01', '2025-06-30', ['liq_dias_adicionales_anio' => 3] + $PL), 3.0,
   'configurable: 3 días adicionales por año si cambia la regla');
eq(Liquidacion::diasDeposito('2020-01-10', '2020-03-01', '2025-06-30', ['liq_dias_garantia_trimestre' => 30] + $PL), 30.0,
   'configurable: 30 días por trimestre si cambia la regla');

echo "\n== Liquidacion::diasRetroactivo (art. 142 c) ==\n";
eq(Liquidacion::diasRetroactivo(3, 6, 0, $PL), 90.0,  '3 años y 6 meses justos → 90 (la fracción no supera 6 meses)');
eq(Liquidacion::diasRetroactivo(3, 6, 1, $PL), 120.0, '3 años, 6 meses y 1 día → 120');
eq(Liquidacion::diasRetroactivo(3, 7, 0, $PL), 120.0, '3 años y 7 meses → 120');
eq(Liquidacion::diasRetroactivo(3, 4, 0, ['liq_meses_fraccion_retroactivo' => 3] + $PL), 120.0,
   'configurable: umbral de la fracción');

echo "\n== Liquidacion::calcularResumen (hoja FORMATO, art. 142 d) ==\n";
$res = Liquidacion::calcularResumen([
    'sueldo_base' => 300, 'dias_alic_vac' => 85, 'dias_alic_fin' => 150,
    'anios' => 3, 'meses' => 7, 'dias' => 0,
    'monto_garantia' => 2000, 'intereses' => 150, 'anticipos' => 100,
    'dias_vacaciones' => 10, 'dias_bono_vac' => 20, 'dias_fin_anio' => 50,
], $PL);
eq($res['salario_normal_diario'], 10.0,  'H11 salario normal diario = mensual / 30');
eq($res['salario_integral_diario'], 16.52, 'H14 = TRUNC(10 + 2,3611 + 4,1666; 2) = 16,52');
eq($res['dias_retroactivo'], 120.0,      '3 años y 7 meses → 120 días de retroactivo');
eq($res['monto_retroactivo'], 1982.4,    'retroactivo = 120 × 16,52');
eq($res['antiguedad_aplicada'], 'garantia', 'la garantía (2.000) supera al retroactivo → se paga la garantía');
eq($res['monto_antiguedad'], 2000.0,     'antigüedad = la mayor de las dos');
eq($res['subtotal'], 2000 + 100 + 200 + 500 + 150 - 100.0, 'subtotal = antigüedad + fracciones + intereses − anticipos');
$res2 = Liquidacion::calcularResumen(['sueldo_base' => 300, 'dias_alic_vac' => 85, 'dias_alic_fin' => 150,
                                       'anios' => 3, 'meses' => 7, 'monto_garantia' => 1000], $PL);
eq($res2['antiguedad_aplicada'], 'retroactivo', 'si el retroactivo es mayor, se paga el retroactivo');

echo "\n== Liquidacion::calcularMora (hoja INTERES DE MORA) ==\n";
$mora = Liquidacion::calcularMora(1000, '2026-07-15', '2026-09-20', ['2026-08' => 58.35, '2026-09' => 60]);
eq($mora['total'], 98.6, 'agosto 4,86 % (truncado) + septiembre 5,00 % sobre 1.000');
eq(count($mora['meses']), 2, 'cuenta desde el mes siguiente al egreso hasta el del pago');
eq(Liquidacion::calcularMora(1000, '2026-07-15', null, [])['total'], 0.0, 'sin fecha de pago no hay mora');
eq(Liquidacion::calcularMora(1000, '2026-07-15', '2026-08-10', [])['sin_tasa'], ['2026-08'], 'mes sin tasa → se reporta');

echo "\n== Liquidacion::mesesCompletos / tiempoServicio ==\n";
eq(Liquidacion::mesesCompletos('2026-01-01', '2026-06-30'), 6, 'enero a fin de junio = 6 meses completos');
eq(Liquidacion::mesesCompletos('2026-01-01', '2026-06-29'), 5, 'un día antes de cerrar junio = 5');
eq(Liquidacion::tiempoServicio('2020-01-10', '2023-08-25'), ['anios' => 3, 'meses' => 7, 'dias' => 15], 'tiempo laboral: años, meses y días');

// =====================================================================
// Bienes — codificación propia (mig. 087, R-12 · C-1)
// =====================================================================
echo "\n== CodificacionBienes::calcularSiguiente ==\n";
eq(CodificacionBienes::calcularSiguiente([], 171, 3), '172', 'sin bienes: el punto de partida + 1');
eq(CodificacionBienes::calcularSiguiente(['084', '171', '131'], 150, 3), '172', 'el mayor registrado manda si supera al punto de partida');
eq(CodificacionBienes::calcularSiguiente(['084', '131'], 171, 3), '172', 'el punto de partida manda si supera a lo registrado');
eq(CodificacionBienes::calcularSiguiente(['084', '131', '141'], null, 3), '142', 'compara como entero, no por el orden de la lista (B-72)');
eq(CodificacionBienes::calcularSiguiente(['007'], null, 3), '008', 'rellena con ceros a 3 dígitos');
eq(CodificacionBienes::calcularSiguiente(['S/C', '', ' 090 '], null, 3), '091', 'ignora lo no numérico («S/C») y los espacios');
eq(CodificacionBienes::calcularSiguiente(['999'], null, 3), '1000', 'al pasar de 999 escribe el número completo');
eq(CodificacionBienes::calcularSiguiente(['999'], null, 4), '1000', 'con 4 dígitos configurados');
eq(CodificacionBienes::calcularSiguiente(['5'], null, 4), '0006', 'los dígitos son configurables (B-74)');
eq(CodificacionBienes::formatear(84, 3), '084', 'formatear 84 → 084');

// =====================================================================
// Documentos jurídicos: montos y fechas en letras (mig. 075)
// =====================================================================
// Las dos frases de referencia están copiadas del documento de donación
// firmado (2026-02-18): el sistema debe escribirlas IGUAL que el papel.
echo "\n== Util::numeroALetras / montoALetras / fechaEnLetras ==\n";
eq(Util::numeroALetras(0), 'cero', '0 → cero');
eq(Util::numeroALetras(16), 'dieciséis', '16 → dieciséis (con tilde)');
eq(Util::numeroALetras(100), 'cien', '100 → cien (no «ciento»)');
eq(Util::numeroALetras(101), 'ciento uno', '101 → ciento uno');
eq(Util::numeroALetras(1000), 'mil', '1000 → mil (no «un mil»)');
eq(Util::numeroALetras(21000), 'veintiún mil', '21000 → veintiún mil (con tilde; antes «veintiun»)');
eq(Util::numeroALetras(31000), 'treinta y un mil', '31000 → treinta y un mil');
eq(Util::numeroALetras(1000000), 'un millón', '1.000.000 → un millón');
eq(Util::numeroALetras(21000000), 'veintiún millones', '21.000.000 → veintiún millones');
eq(Util::montoALetras(19025.28), 'DIECINUEVE MIL VEINTICINCO BOLÍVARES CON VEINTIOCHO CÉNTIMOS', 'monto del documento de donación, igual al papel');
eq(Util::montoALetras(1000), 'MIL BOLÍVARES', 'sin céntimos, los omite');
eq(Util::fechaEnLetras('2026-02-18'), 'a los Dieciocho (18) días del mes de Febrero de Dos Mil Veintiséis (2026)', 'fecha del documento de donación');

echo "\n== Util::dispositivo (bitácora de accesos, mig. 089) ==\n";
// Los dos primeros son los User-Agent reales del 2026-10-13 (teléfono y PC).
eq(Util::dispositivo('Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36'),
   'Teléfono · Android · Chrome', 'teléfono Android con Chrome');
eq(Util::dispositivo('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0'),
   'Computadora · Windows · Edge', 'Edge dice también «Chrome»: debe ganar Edge');
eq(Util::dispositivo('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'),
   'Teléfono · iPhone · Safari', 'iPhone no se confunde con macOS');
eq(Util::dispositivo('Mozilla/5.0 (Linux; Android 13; SM-X200) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/23.0 Chrome/115.0 Safari/537.36'),
   'Tableta · Android · Samsung Internet', 'Android sin «Mobile» = tableta');
eq(Util::dispositivo('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:128.0) Gecko/20100101 Firefox/128.0'),
   'Computadora · Windows · Firefox', 'Firefox en Windows');
eq(Util::dispositivo(null), '—', 'sin User-Agent (registros anteriores a la mig. 089)');
eq(Util::dispositivo('', ''), '', 'vacío con marcador propio (export)');

// =====================================================================
// Validaciones del servidor (Controller)
// =====================================================================
echo "\n== Controller::emailValido / telefonoValido / rifValido / normalizarRif ==\n";
$ctl = new class extends Controller {
    public function __construct() {}
    public function email($x) { return $this->emailValido($x); }
    public function tel($x)   { return $this->telefonoValido($x); }
    public function rif($x)   { return $this->rifValido($x); }
    public function nrif($x)  { return $this->normalizarRif($x); }
};
check($ctl->email('nombre@dominio.com'), 'correo válido');
check(!$ctl->email('nombre apellido@dominio.com'), 'correo con espacio → inválido');
check(!$ctl->email('ñandú@dominio.com'), 'correo con símbolos especiales → inválido');
check(!$ctl->email(''), 'correo vacío → inválido');
check($ctl->tel('0414-1234567'), 'teléfono VE con guion → válido');
check(!$ctl->tel('0414123'), 'teléfono corto → inválido');
check(!$ctl->tel('00000000000'), 'relleno de datos de arranque (todo ceros) → inválido');
check($ctl->tel('02934311234'), 'teléfono fijo (02xx) → válido');
check(!$ctl->tel('01234567890'), 'código de área inexistente (01xx) → inválido');
check($ctl->rif('G-20008498-7'), 'RIF institucional → válido');
check(!$ctl->rif('X-12345678-9'), 'RIF con letra no venezolana → inválido');
eq($ctl->nrif('g200084987'), 'G-20008498-7', 'normaliza el RIF a L-NNNNNNNN-N');

// =====================================================================
// Bienes: código compuesto y descripción oficial (mig. 062, 075)
// =====================================================================
echo "\n== Inventario::componerCodigo / descripcionOficial ==\n";
eq(Inventario::componerCodigo('2', '01', '108', '084'), '2-01-108-084', 'compone el código como el BM-1');
check(Inventario::componerCodigo('2', '01', '', '084') === null, 'si falta una parte, no hay código');
eq(Inventario::descripcionOficial((object)['nombre' => 'Aire acondicionado', 'descripcion' => 'color beige',
        'marca' => 'Hyundai', 'modelo' => 'Piso techo', 'serial' => 'SN1']),
   'AIRE ACONDICIONADO, COLOR BEIGE, MARCA HYUNDAI, MODELO PISO TECHO, SERIAL SN1',
   'marca, modelo y serial van dentro de la descripción, en mayúsculas');

// =====================================================================
// RRHH: elegible a fijo (3C) y anti-inyección de columnas (deuda técnica §5.2)
// =====================================================================
echo "\n== Empleado::elegibleParaFijo ==\n";
$cont = fn(array $x) => (object)($x + ['tipo_contrato' => 'Contratado', 'institucion_origen' => 'IMATUR',
                                        'fecha_ingreso' => diasAtras('-6 years'), 'fecha_ingreso_administracion' => null, 'fecha_egreso' => null]);
check(Empleado::elegibleParaFijo($cont([])), 'contratado de IMATUR con 6 años → elegible (umbral 5)');
check(!Empleado::elegibleParaFijo($cont(['fecha_ingreso' => diasAtras('-4 years')])), 'IMATUR con 4 años → no');
check(Empleado::elegibleParaFijo($cont(['institucion_origen' => 'Gobernación', 'fecha_ingreso' => diasAtras('-3 years -1 day')])), 'Gobernación con 3 años → elegible (umbral 3)');
check(!Empleado::elegibleParaFijo($cont(['tipo_contrato' => 'Fijo'])), 'un fijo no es «elegible a fijo»');
check(!Empleado::elegibleParaFijo($cont(['fecha_egreso' => '2026-01-01'])), 'un egresado no');

echo "\n== Taller::actualizarPersona — lista blanca de columnas ==\n";
try { Taller::actualizarPersona(1, ['cedula' => '123'], 1); check(false, 'debió rechazar «cedula»'); }
catch (InvalidArgumentException $e) { check(true, 'rechaza «cedula» (la identidad no se cambia por esta vía)'); }
try { Taller::actualizarPersona(1, ['nombre = nombre; --' => 'x'], 1); check(false, 'debió rechazar una clave inyectada'); }
catch (InvalidArgumentException $e) { check(true, 'rechaza una clave que intenta inyectar SQL'); }
check(Taller::COLUMNAS_PERSONA_EDITABLES === ['nombre', 'apellido', 'telefono', 'correo', 'genero', 'fecha_nacimiento', 'parroquia_id', 'direccion'],
      'las columnas permitidas son exactamente las que usan talleres y rutas');

echo "\n== Taller::cuerpoOficioPropuesto (oficio de formación, D-NEW01) ==\n";
$tOf = (object)['nombre' => 'Atención al turista', 'tipo_actividad' => 'Charla', 'fecha_inicio' => '2026-10-20',
                'hora_inicio' => '09:00:00', 'ubicacion' => 'U.E. Antonio José de Sucre', 'es_interna' => false, 'tipo_ente' => 'Escuela'];
$cOf = Taller::cuerpoOficioPropuesto($tOf);
check(strpos($cOf, 'la charla «Atención al turista» el día martes 20 de octubre de 2026 a las 09:00') !== false, 'usa tipo, nombre, fecha en letras y hora');
check(strpos($cOf, 'en U.E. Antonio José de Sucre, dirigida a estudiantes de educación primaria') !== false, 'lugar y público de una actividad externa');
check(count(preg_split('/\n\s*\n/', $cOf)) === 3, 'tres párrafos separados por línea en blanco');
$tOf->tipo_actividad = 'Taller'; $tOf->es_interna = true;
$cOf2 = Taller::cuerpoOficioPropuesto($tOf);
check(strpos($cOf2, 'el taller «') !== false, 'artículo masculino para «taller»');
check(strpos($cOf2, 'dirigida a') === false, 'una actividad interna no menciona público externo');

echo "\n== sigtur_uid (ids únicos para label[for] en bucles) ==\n";
$u1 = sigtur_uid('am fecha'); $u2 = sigtur_uid('am fecha');
check($u1 !== $u2, 'dos llamadas dan ids distintos');
check((bool)preg_match('/^[A-Za-z0-9_-]+$/', $u1), 'el id solo lleva caracteres válidos (quita espacios)');

echo "\n" . str_repeat('-', 48) . "\n";
echo "$tests pruebas ejecutadas, $fails fallo(s).\n";
exit($fails > 0 ? 1 : 0);
