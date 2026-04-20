# Módulo Técnico: Turismo

**Ruta Lógica:** `/rutas`, `/actividadesruta`.
**Nivel de Acceso Sugerido:** Turismo, Administración.

## Objetivo del Módulo
Eje neurálgico del instituto, se encarga de catalogar rutas patrimoniales y asignar eventos culturales (ferias locales).

## Tablas en Juego
- `rutas`: Entidades genéricas con descripción e historial de apertura.
- `puntos_ruta`: Lista en cadena de monumentos, plazas y atracciones.
- `actividades_ruta`: Registros de movilizaciones adscritas a rutas, usualmente con fecha.

## Lógica de Negocio (Business Rules)
1. **Arquitectura Escalón:**
   A través de un ID padre (`id_ruta`), se permiten insertar infinitos subpuntos patrimoniales mediante `Puntos_ruta`. 
2. **Comportamiento Recursivo:**
   Al invocar la "Papelera de Reciclaje" y ejecutar una eliminación sobre la ruta padre, se propaga temporalmente y elimina visualmente sus subpuntos. 
   Por la misma vía, en el controlador avanzado `AuditoriaController::restaurar`, si se revive la Ruta, los Puntos de la ruta asociados son recuperados masivamente.
