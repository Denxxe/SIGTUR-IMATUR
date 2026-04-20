# Módulo Técnico: Inventarios

**Ruta Lógica:** `/inventario`, `/categorias`, `/ubicaciones`, `/actividadesinventario`.
**Nivel de Acceso Sugerido:** Inventario, Administración.

## Objetivo del Módulo
Lograr control exhaustivo y auditable de los Bienes Nacionales y consumibles bajo responsabilidad institucional de IMATUR.

## Tablas en Juego
- `categorias`: Segmentación (Mobiliario, Informática, Limpieza, Vehículos).
- `ubicaciones`: Posicionamiento físico en terreno.
- `inventario`: Tabla de elementos, conteniendo el código de patrimonio.
- `actividades_inventario`: Tabla pivote y de trazabilidad. Relaciona el estado del bien ("Asignado a...", "Reparación", "Mudanza") en el tiempo.

## Lógica de Negocio (Business Rules)
1. **Identificadores Únicos (BN):**
   Los bienes poseen un atributo inalterable (Su Código de Bien Nacional) por medio de constricción `UNIQUE` en la DB, para prevenir escaneos de inventario solapados.
2. **Ciclo de Vida del Activo:**
   Los cambios de estatus ("Operativo" a "Dañado", etc) no eliminan el objeto de la base, sino que insertan un movimiento de bitácora particular indicando por qué cambió de estado a través de `actividades_inventario`.
3. **Restricción Referencial:**
   Un "Taller de Formación" puede tomar prestado inventario mediante la tabla `taller_inventario`. Es imposible dar de baja de manera abrupta un bien si en el momento ha sido entregado en préstamo a un facilitador, previniendo fuga de capital.
