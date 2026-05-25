<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Gestión de Personal</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Personal'; ?></h1>
        <p class="page__subtitle">Registro y administración del personal activo de la institución.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalEmpleado" onclick="nuevoEmpleado()">
            <i class="bi bi-person-plus"></i> Registrar Empleado
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Expediente</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['empleados'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No hay empleados registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['empleados'] ?? [] as $emp): ?>
                    <tr>
                        <td class="cell-strong"><?php echo $emp->nro_expediente ?? 'N/A'; ?></td>
                        <td><?php echo $emp->cedula ?? 'N/A'; ?></td>
                        <td><?php echo ($emp->nombre ?? 'N/A') . ' ' . ($emp->apellido ?? ''); ?></td>
                        <td><span class="sig-badge sig-badge--info"><?php echo $emp->cargo ?? 'Sin cargo'; ?></span></td>
                        <td><?php echo $emp->departamento ?? 'Sin dpto.'; ?></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarEmpleado(<?php echo htmlspecialchars(json_encode($emp), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/empleados/delete/<?php echo $emp->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i> Baja
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalEmpleado" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/empleados/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEmpleadoLabel">Nuevo Registro de Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="emp_id">
                <input type="hidden" name="id_persona" id="emp_id_persona">
                
                <h6 style="color:var(--brand-600);border-bottom:1px solid var(--border-subtle);padding-bottom:8px;margin-bottom:16px;">
                    <i class="bi bi-person-vcard"></i> Datos Personales
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Cédula <span class="req">*</span></label>
                            <input type="text" name="cedula" id="emp_cedula" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombres <span class="req">*</span></label>
                            <input type="text" name="nombre" id="emp_nombre" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Apellidos <span class="req">*</span></label>
                            <input type="text" name="apellido" id="emp_apellido" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Género</label>
                            <select name="genero" id="emp_genero" class="sig-select" required>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="emp_fecha_nac" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Teléfono</label>
                            <input type="text" name="telefono" id="emp_telefono" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Correo Electrónico</label>
                            <input type="email" name="correo" id="emp_correo" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Dirección de Habitación</label>
                            <textarea name="direccion" id="emp_direccion" class="sig-textarea" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <h6 style="color:var(--brand-600);border-bottom:1px solid var(--border-subtle);padding-bottom:8px;margin-bottom:16px;">
                    <i class="bi bi-building"></i> Datos Institucionales
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Cargo <span class="req">*</span></label>
                            <select name="id_cargo" id="emp_id_cargo" class="sig-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($data['cargos'] ?? [] as $c): ?>
                                    <option value="<?php echo $c->id; ?>"><?php echo $c->nombre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Departamento <span class="req">*</span></label>
                            <select name="id_departamento" id="emp_id_departamento" class="sig-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($data['departamentos'] ?? [] as $d): ?>
                                    <option value="<?php echo $d->id; ?>"><?php echo $d->nombre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Nro. Expediente <span class="req">*</span></label>
                            <input type="text" name="nro_expediente" id="emp_nro_expediente" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo de Contrato <span class="req">*</span></label>
                            <select name="tipo_contrato" id="emp_tipo_contrato" class="sig-select" required>
                                <option value="Fijo">Fijo</option>
                                <option value="Contratado">Contratado</option>
                                <option value="Suplente">Suplente</option>
                                <option value="Comisión de Servicio">Comisión de Servicio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Horario Asignado</label>
                            <select name="id_horario" id="emp_id_horario" class="sig-select">
                                <option value="">— Sin horario asignado —</option>
                                <?php foreach ($data['horarios'] ?? [] as $h): ?>
                                    <option value="<?php echo $h->id; ?>">
                                        <?php echo htmlspecialchars($h->nombre); ?>
                                        (<?php echo substr($h->hora_entrada,0,5); ?>–<?php echo substr($h->hora_salida,0,5); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha de Ingreso <span class="req">*</span></label>
                            <input type="date" name="fecha_ingreso" id="emp_fecha_ingreso" class="sig-input" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha de Egreso <small style="color:var(--text-secondary)">(si aplica)</small></label>
                            <input type="date" name="fecha_egreso" id="emp_fecha_egreso" class="sig-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoEmpleado() {
        document.getElementById('modalEmpleadoLabel').innerText = 'Nuevo Registro de Personal';
        document.getElementById('emp_id').value = '';
        document.getElementById('emp_id_persona').value = '';
        const form = document.querySelector('#modalEmpleado form');
        form.reset();
        document.getElementById('emp_fecha_ingreso').value = '<?php echo date('Y-m-d'); ?>';
    }
    function editarEmpleado(emp) {
        document.getElementById('modalEmpleadoLabel').innerText = 'Editar: ' + emp.nombre + ' ' + emp.apellido;
        document.getElementById('emp_id').value             = emp.id;
        document.getElementById('emp_id_persona').value     = emp.id_persona;
        document.getElementById('emp_cedula').value         = emp.cedula       || '';
        document.getElementById('emp_nombre').value         = emp.nombre       || '';
        document.getElementById('emp_apellido').value       = emp.apellido     || '';
        document.getElementById('emp_genero').value         = emp.genero       || 'M';
        document.getElementById('emp_fecha_nac').value      = emp.fecha_nacimiento || '';
        document.getElementById('emp_telefono').value       = emp.telefono     || '';
        document.getElementById('emp_correo').value         = emp.correo       || '';
        document.getElementById('emp_direccion').value      = emp.direccion    || '';
        document.getElementById('emp_id_cargo').value       = emp.id_cargo;
        document.getElementById('emp_id_departamento').value= emp.id_departamento;
        document.getElementById('emp_nro_expediente').value = emp.nro_expediente || '';
        document.getElementById('emp_tipo_contrato').value  = emp.tipo_contrato || 'Fijo';
        document.getElementById('emp_id_horario').value     = emp.id_horario   || '';
        document.getElementById('emp_fecha_ingreso').value  = emp.fecha_ingreso || '';
        document.getElementById('emp_fecha_egreso').value   = emp.fecha_egreso  || '';
        new bootstrap.Modal(document.getElementById('modalEmpleado')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
