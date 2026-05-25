# Módulo de Pasantes — Reglas de Negocio

**Última actualización:** 2026-05-22

## Contexto institucional

El proceso de pasantías lo gestiona el **Departamento de Formación y Capacitación**, con revisión de Talento Humano y firma/sello final de la Dirección General.

---

## RN-PS01 — Máquina de estados

```
Postulado → Aceptado → En Curso → Culminado
          ↘                    ↘
           Rechazado             Abandonado
```

- **Postulado:** El estudiante se presentó y entregó carta de postulación a IMATUR.
- **Aceptado:** Dirección firmó y selló la carta → se remite carta de aceptación al estudiante/institución. **Solo rol 1 (Administrador) puede ejecutar esta transición** (D-PS01 respondida).
- **Rechazado:** Dirección rechazó la solicitud (terminal).
- **En Curso:** Pasantía activa, con tutor asignado.
- **Culminado:** Pasantía finalizada, carta de culminación emitida.
- **Abandonado:** El pasante no completó sin acto formal de culminación.

---

## RN-PS02 — Pasante vinculado a Persona

Todo pasante tiene un `id_persona FK` a la tabla `personas` (migración 003). No hay campos cédula/nombre/apellido propios en `pasantes`; siempre se acceden por JOIN. Si la persona ya existe en el sistema (ej: ya fue participante de un taller), se reutiliza el registro.

---

## RN-PS03 — Documentos requeridos

La tabla `pasante_documentos` almacena flags de entrega. Los documentos requeridos son exactamente 3:
- Carta Institucional
- Copia de Cédula
- Planilla

Los documentos físicos se archivan en papel. No hay almacenamiento digital de archivos en el sistema.

---

## RN-PS04 — Tutor institucional

- El tutor es cualquier **empleado activo de IMATUR** (`id_tutor_institucional FK` a `empleados`).
- Lo asigna la Dirección según la necesidad del proyecto/pasante.
- El select en vistas lista todos los empleados con `is_active = TRUE` — sin filtro por cargo o departamento.
- No hay límite de pasantes por tutor ni por departamento.

---

## RN-PS05 — Carta de culminación

Al culminar la pasantía, IMATUR emite una carta de culminación firmada por la Dirección. Generada imprimible desde el sistema en `pasantes/carta_culminacion.php` (D-PS04 respondida). Incluye: período completo (fecha inicio → fecha fin) y total de días calculado.

---

## RN-PS06 — Evaluación

El sistema tiene campo `nota` (DECIMAL numérico) y campo `evaluacion` (texto cualitativo: Excelente/Bueno/Regular/Deficiente). Ambos campos existen en BD. La vista detalle muestra ambos valores (D-PS02 respondida).

---

## RN-PS07 — Cálculo de duración

La duración se calcula como `(fecha_fin - fecha_inicio)` mostrado en días en la vista detalle. No hay estándar de horas diarias definido (D-PS04 respondida).

---

## Estado de brechas

| ID | Descripción | Estado |
|----|-------------|--------|
| BPS-01 | Restricción de rol para Postulado→Aceptado | ✅ Resuelto — solo rol 1 en `PasantesController::editar()` |
| BPS-02 | Carta de culminación imprimible | ✅ Resuelto — `pasantes/carta_culminacion.php` |
| BPS-03 | Cálculo automático de duración | ✅ Resuelto — días calculados en vista detalle |
| BPS-04 | Checklist de documentos en UI | ✅ Resuelto — 3 documentos (carta, cédula, planilla) |
| BPS-05 | Límite de pasantes por tutor/departamento | ✅ Resuelto — D-PS07: sin límite |
| BPS-06 | Escala de evaluación definida | ✅ Resuelto — numérica + cualitativa (D-PS02) |
