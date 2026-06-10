<?php require_once '../app/views/inc/header.php';
/*
 * Vista genérica de reporte tabular. Espera en $data:
 *  - titulo, subtitulo (opcional), eyebrow (opcional)
 *  - columnas: array de encabezados
 *  - filas: array de filas; cada fila = array de celdas. Una celda string se
 *    escapa; una celda array ['raw'=>'<html>'] se imprime tal cual (para badges).
 *  - resumen: array asociativo etiqueta=>valor para tiles (opcional)
 *  - filtros: array de campos [['name','label','type'=>select|date|text,'options'=>[v=>lbl],'value']] (opcional)
 *  - accion: action del form de filtros (GET) — requerido si hay filtros
 *  - export_url: URL de exportación CSV (opcional)
 *  - buscador: bool (default true) — buscador + paginación cliente
 *  - vacio: texto cuando no hay filas
 */
$columnas  = $data['columnas']  ?? [];
$filas     = $data['filas']     ?? [];
$resumen   = $data['resumen']   ?? [];
$filtros   = $data['filtros']   ?? [];
$accion    = $data['accion']    ?? '';
$exportUrl = $data['export_url'] ?? '';
$buscador  = $data['buscador']  ?? true;
$vacio     = $data['vacio']     ?? 'Sin registros para mostrar.';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow"><?php echo htmlspecialchars($data['eyebrow'] ?? 'Análisis · Reporte'); ?></div>
        <h1 class="page__title"><?php echo htmlspecialchars($data['titulo'] ?? 'Reporte'); ?></h1>
        <?php if (!empty($data['subtitulo'])): ?><p class="page__subtitle"><?php echo htmlspecialchars($data['subtitulo']); ?></p><?php endif; ?>
    </div>
    <div class="page__actions">
        <?php if ($exportUrl): ?>
            <a href="<?php echo $exportUrl; ?>" class="btn-sig btn-sig--success"><i class="bi bi-filetype-csv"></i> Exportar CSV</a>
        <?php endif; ?>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<?php if ($resumen): ?>
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-4);">
    <?php foreach ($resumen as $lbl => $val): ?>
        <div class="col">
            <div class="sig-card"><div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                <div style="font-size:1.7rem;font-weight:800;color:var(--text-primary);line-height:1.1;"><?php echo htmlspecialchars((string)$val); ?></div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.04em;"><?php echo htmlspecialchars((string)$lbl); ?></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($filtros): ?>
<form method="GET" action="<?php echo $accion; ?>" class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);display:flex;align-items:flex-end;gap:var(--sp-3);flex-wrap:wrap;">
        <?php foreach ($filtros as $f): $tipo = $f['type'] ?? 'text'; $val = $f['value'] ?? ''; ?>
            <div<?php echo $tipo === 'text' ? ' style="flex:1;min-width:200px;"' : ''; ?>>
                <label class="sig-field__label" style="font-size:11px;"><?php echo htmlspecialchars($f['label']); ?></label>
                <?php if ($tipo === 'select'): ?>
                    <select name="<?php echo $f['name']; ?>" class="sig-input" style="min-width:150px;">
                        <?php foreach (($f['options'] ?? []) as $ov => $ol): ?>
                            <option value="<?php echo htmlspecialchars((string)$ov); ?>" <?php echo (string)$val === (string)$ov ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$ol); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($tipo === 'date'): ?>
                    <input type="date" name="<?php echo $f['name']; ?>" class="sig-input" style="max-width:150px;" value="<?php echo htmlspecialchars((string)$val); ?>">
                <?php else: ?>
                    <div class="tabla-search"><i class="bi bi-search"></i>
                        <input type="text" name="<?php echo $f['name']; ?>" class="sig-input" style="padding-left:32px;width:100%;" placeholder="<?php echo htmlspecialchars($f['placeholder'] ?? 'Buscar…'); ?>" value="<?php echo htmlspecialchars((string)$val); ?>">
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn-sig btn-sig--primary" style="height:42px;"><i class="bi bi-funnel"></i> Filtrar</button>
    </div>
</form>
<?php endif; ?>

<div class="sig-table-wrap anim-slide-up"<?php echo $buscador ? ' data-tabla-buscable data-por-pagina="15"' : ''; ?>>
    <table class="sig-table">
        <thead><tr><?php foreach ($columnas as $c): ?><th><?php echo htmlspecialchars($c); ?></th><?php endforeach; ?></tr></thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="<?php echo max(1, count($columnas)); ?>" class="sig-table-empty"><?php echo htmlspecialchars($vacio); ?></td></tr>
            <?php else: foreach ($filas as $fila): ?>
                <tr>
                    <?php foreach ($fila as $celda): ?>
                        <td><?php echo is_array($celda) ? ($celda['raw'] ?? '') : htmlspecialchars((string)$celda); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
