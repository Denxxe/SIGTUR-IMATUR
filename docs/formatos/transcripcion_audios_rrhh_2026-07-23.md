# Transcripción — audios de Talento Humano, 2026-07-23

**Origen:** 4 notas de voz de WhatsApp (4,3 min en total) respondiendo las preguntas de nómina del
`BACKLOG.md` §3.1.
**Método:** transcripción local con Whisper `large-v3` (español), doble pasada independiente — una con
sesgo de vocabulario del dominio y otra sin él — para corroborar las cifras. Las tres cantidades que
aparecen coincidieron en ambas pasadas.

> ⚠️ **Esto es material de origen, no una fuente de verdad para programar.** Donde la plantilla real
> (`INSTITUTO IMATUR JULIO 2026.xlsx`) contradice al audio, **manda la plantilla**. Ver las correcciones
> al final.

---

## Audio 1 — 5:15 PM (23 s)

> Hola, cómo estás, buenas tardes, mira esta mañana vi que me escribiste […] me imagino que me estás
> escribiendo para el tema de las preguntas, yo ahorita voy a salir a trotar el monumento, pero como
> mañana es día feriado, voy a estar disponible y te prometo que te respondo esas preguntas.

Sin contenido técnico.

---

## Audio 2 — 5:17 PM (99 s)

> Para responderte esta, **un mes de bono vacacional ya calculado con números reales**, ok, eso te lo
> puedo pasar.
>
> Si los días 75, 75, 85 tienen tope máximo al sumar años… esos días de 75, 85, **esos son días que
> calculan ellos con una tabla que se creó de contrataciones colectivas para la administración pública**.
> Existen esas tablas que dicen que un trabajador con tantos años se gana 85 días y así. O sea, **eso lo
> determinan ellos, no nosotros**.
>
> ¿Monto vigente de cesta ticket? El monto vigente de cesta ticket es **28.388**. Es el actual monto
> porque **eso se actualiza mensualmente**, que **lo manda la UNAPRE**.
>
> ¿De dónde sacan hoy la tasa BCV y los días adicionales para los intereses de prestaciones? […]
> **no sé a qué te refieres**, eso también va por contratación colectiva, pero **no entendí lo de los días
> adicionales** […] eso me imagino que es del formato […] **déjame revisar y te respondo esa pregunta**.

---

## Audio 3 — 5:19 PM (56 s)

> Respecto a la **caja de ahorro**: tú te inscribes en la caja de ahorro de la administración pública,
> pero **la gobernación no paga caja de ahorro**. Eso lo pagan… ahorita eso **no suma nada**.
>
> El **bono de transporte** es el mismo para todos: **12,50**.
>
> La **prima por discapacidad** es una prima que se paga, **no varía**, es una prima que le pagan al
> **personal fijo**.

---

## Audio 4 — 5:21 PM (82 s)

> El **monto por hijo** es para todos, o sea, por cada hijo **12,5**; si tú tienes dos hijos son 25 y así.
>
> La **prima profesional**: sí, todos los que tienen títulos universitarios, y existe **un porcentaje por
> escala** de TSU, de profesional, que es el licenciado, ya el magíster, el doctorado — todo eso tiene un
> porcentaje, también lo puedes conseguir en internet.
>
> La **prima de responsabilidad está atada al cargo**, todo coordinador cobra lo mismo […] a todos por
> igual. La prima profesional no es un monto fijo, como ya te dije.
>
> Mañana te paso lo de la nómina […] *(tramo final confuso, transcripción poco fiable)*: existe una tabla
> de escala salarial por grado que vimos en el formato de bono vacacional… yo te comparto eso.

> **Pendiente de escucha humana:** el tramo desde 1:02 de este audio. La transcripción se degrada y
> parece ofrecer una **tabla de escala salarial por grado** que aún no hemos recibido.

---

## Correcciones que impuso la plantilla real

| Dijo el audio | Dice la plantilla | Veredicto |
|---|---|---|
| Prima por hijo = 12,50 | `nº hijos × 6,50` quincenal (**13,00 mensual**) | **Manda la plantilla.** Probablemente lo confundió con el bono de transporte |
| Bono de transporte = 12,50 | `12,50 / 2 = 6,25` quincenal (**12,50 mensual**) | ✅ Correcto |
| Prima de responsabilidad "atada al cargo", monto igual para todos | `(cantidad_divisas × tasa_dólar) / 2` — **varía por persona** (80 divisas en una hoja, 70 en otra) | **Manda la plantilla** |
| Prima profesional = % por escala académica | `BACH 0 · TSU 20 % · PROF 25 % · ESP 30 % · MAEST 35 % · DR 40 %` | ✅ Correcto, y ya tenemos los porcentajes |
| Tasa BCV "va por contratación colectiva" | Es el **tipo de cambio del dólar**: el bono de responsabilidad se pacta en divisas y se paga al cambio | **Corregido por el cliente en conversación posterior** |
| Cesta ticket = 28.388 | La plantilla de julio trae **22.907** | Ambos válidos: **cambia cada mes** (lo publica la UNAPRE), tal como él dijo |
| Días base "los determinan ellos con una tabla por años" | La plantilla usa `75 + años de servicio` en todas las hojas | Coincide con lo implementado en el v1; queda por aclarar el 85/45 (pregunta N‑1) |

**Conclusión metodológica:** de 7 afirmaciones con contenido numérico, **3 estaban equivocadas**. Sirvió
para orientar y para saber qué preguntar, pero ningún número del audio entró al código.
