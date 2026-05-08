# Módulo de Pasantes — Reglas de Negocio

## Contexto institucional

El proceso de pasantías lo gestiona el **Departamento de Formación y Capacitación**, con revisión de Talento Humano y firma/sello final de la Dirección General.

---

## RN-PS01 — Máquina de estados

```
Postulado → Aceptado → En Curso → Culminado
          ↘                    ↘
           Rechazado             Abandonado
```

- **Postulado:** El estudiante se presentó y entregó carta de postulación a la institución IMATUR.
- **Aceptado:** Dirección firmó y selló la carta → se remite carta de aceptación al estudiante/institución. El sistema debe registrar este cambio de estado.
- **Rechazado:** Dirección rechazó la solicitud (terminal).
- **En Curso:** Pasantía activa, con tutor asignado.
- **Culminado:** Pasantía finalizada, carta de culminación emitida.
- **Abandonado:** El pasante no completó sin acto formal de culminación.

---

## RN-PS02 — Pasante vinculado a Persona

Todo pasante tiene un `id_persona FK` a la tabla `personas` (migración 003). No hay campos cédula/nombre/apellido propios en `pasantes`; siempre se acceden por JOIN. Si la persona ya existe en el sistema (ej: ya fue participante de un taller), se reutiliza el registro.

---

## RN-PS03 — Documentos requeridos

La tabla `pasante_documentos` almacena flags de entrega de documentos. Los documentos exactos a confirmar (ver pregunta 158). La práctica actual:
- Se registra si el documento fue entregado (flag booleano).
- Los documentos físicos se archivan en papel.
- No hay almacenamiento digital de archivos en el sistema actual.

---

## RN-PS04 — Tutor institucional

- El tutor es un **empleado de IMATUR** (`id_tutor_institucional FK` a `empleados`).
- El tutor se asigna al pasar al estado "Aceptado" o "En Curso".
- **Pendiente confirmar:** si siempre debe ser el jefe del departamento o puede ser cualquier empleado (ver pregunta 145).
- No hay límite implementado de pasantes por tutor.

---

## RN-PS05 — Carta de culminación

Al culminar la pasantía, IMATUR emite una carta de culminación firmada por la Dirección. Esta carta sigue el mismo flujo: Formación → revisión → Talento Humano → Dirección (firma y sello).  
**Pendiente:** generación imprimible en el sistema (similar al oficio de rutas, ver pregunta 147).

---

## RN-PS06 — Evaluación

El sistema tiene un campo `nota` en `pasantes`. La escala exacta y si hay evaluaciones parciales están pendientes de confirmar (ver pregunta 146).

---

## RN-PS07 — Horas de pasantía

El cálculo de horas a partir de `fecha_inicio` y `fecha_fin` no está automatizado. Las horas totales podrían calcularse como `(fecha_fin - fecha_inicio) × horas_diarias_estándar`.  
**Pendiente:** confirmar si el sistema debe calcularlas (ver pregunta 161).

---

## Brechas identificadas (pendientes de implementación)

| ID | Descripción | Impacto |
|----|-------------|---------|
| BPS-01 | Flujo formal de cambio de estado (Postulado→Aceptado requiere acción de Dirección) | Alto |
| BPS-02 | Generación imprimible de carta de culminación | Alto |
| BPS-03 | Cálculo automático de horas de pasantía | Medio |
| BPS-04 | Lista de documentos requeridos con checklist en UI | Medio |
| BPS-05 | Límite de pasantes simultáneos por departamento o tutor | Bajo |
| BPS-06 | Escala y rubrica de evaluación definida | Medio |
