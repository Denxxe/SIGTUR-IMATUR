<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-people"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEmpleado" onclick="nuevoEmpleado()">
            <i class="bi bi-person-plus"></i> Registrar Empleado
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Expediente</th>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['empleados'])): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No hay empleados registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['empleados'] as $emp): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo $emp->nro_expediente; ?></td>
                                <td><?php echo $emp->cedula; ?></td>
                                <td><?php echo $emp->nombre . ' ' . $emp->apellido; ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo $emp->cargo; ?></span></td>
                                <td><?php echo $emp->departamento; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick='editarEmpleado(<?php echo json_encode($emp); ?>)'>Editar</button>
                                        <a href="<?php echo URL_ROOT; ?>/empleados/delete/<?php echo $emp->id; ?>" class="btn btn-outline-danger delete-btn">Baja</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalEmpleado" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/empleados/store" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEmpleadoLabel">Nuevo Registro de Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="emp_id">
                <input type="hidden" name="id_persona" id="emp_id_persona">
                
                <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="bi bi-person-vcard"></i> Datos Personales</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cédula</label>
                        <input type="text" name="cedula" id="emp_cedula" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nombres</label>
                        <input type="text" name="nombre" id="emp_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Apellidos</label>
                        <input type="text" name="apellido" id="emp_apellido" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Género</label>
                        <select name="genero" id="emp_genero" class="form-select" required>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="emp_fecha_nac" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" name="telefono" id="emp_telefono" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Correo Electrónico</label>
                        <input type="email" name="correo" id="emp_correo" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Dirección de Habitación</label>
                        <textarea name="direccion" id="emp_direccion" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="bi bi-building"></i> Datos Institucionales</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cargo</label>
                        <select name="id_cargo" id="emp_id_cargo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['cargos'] as $c): ?>
                                <option value="<?php echo $c->id; ?>"><?php echo $c->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Departamento</label>
                        <select name="id_departamento" id="emp_id_departamento" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['departamentos'] as $d): ?>
                                <option value="<?php echo $d->id; ?>"><?php echo $d->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nro. Expediente</label>
                        <input type="text" name="nro_expediente" id="emp_nro_expediente" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de Ingreso</label>
                        <input type="date" name="fecha_ingreso" id="emp_fecha_ingreso" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Datos del Empleado</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoEmpleado() {
        document.getElementById('modalEmpleadoLabel').innerText = 'Nuevo Registro de Personal';
        document.getElementById('emp_id').value = '';
        document.getElementById('emp_id_persona').value = '';
        // Reset campos
        const form = document.querySelector('#modalEmpleado form');
        form.reset();
        document.getElementById('emp_fecha_ingreso').value = '<?php echo date('Y-m-d'); ?>';
    }

    function editarEmpleado(emp) {
        document.getElementById('modalEmpleadoLabel').innerText = 'Editar Empleado: ' + emp.nombre;
        document.getElementById('emp_id').value = emp.id;
        document.getElementById('emp_id_persona').value = emp.id_persona;
        document.getElementById('emp_cedula').value = emp.cedula;
        document.getElementById('emp_nombre').value = emp.nombre;
        document.getElementById('emp_apellido').value = emp.apellido;
        document.getElementById('emp_genero').value = emp.genero;
        document.getElementById('emp_fecha_nac').value = emp.fecha_nacimiento;
        document.getElementById('emp_telefono').value = emp.telefono;
        document.getElementById('emp_correo').value = emp.correo;
        document.getElementById('emp_direccion').value = emp.direccion;
        document.getElementById('emp_id_cargo').value = emp.id_cargo;
        document.getElementById('emp_id_departamento').value = emp.id_departamento;
        document.getElementById('emp_nro_expediente').value = emp.nro_expediente;
        document.getElementById('emp_fecha_ingreso').value = emp.fecha_ingreso;
        
        new bootstrap.Modal(document.getElementById('modalEmpleado')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
