# storage/certs

## `bcv-sectigo-ca.pem`

Certificado **intermedio** de la autoridad que firma el certificado de
`bcv.org.ve`, usado por `TasaBcv` para consultar la tasa oficial del dólar
**con verificación TLS activada**.

- Sujeto: `Sectigo Public Server Authentication CA DV R36`
- Vence: **21/03/2036**
- SHA-256: `8C:54:C3:34:B6:6B:A4:E4:26:77:2A:F4:A3:F9:13:6C:19:A1:AE:C7:29:FD:B2:8C:53:5C:07:A5:A4:EF:22:E0`

### Por qué existe este archivo

El servidor del BCV está mal configurado: **envía la cadena de certificados
equivocada.** Su certificado lo firma `Sectigo Public Server Authentication CA
DV R36`, pero el intermedio que el servidor entrega es otro distinto
(`Sectigo RSA Domain Validation Secure Server CA`). La cadena no cierra.

Los navegadores y el `curl` de Windows no lo notan porque **van a buscar el
intermedio que falta** por su cuenta (AIA fetching). OpenSSL —que es lo que usa
PHP— no hace eso, así que la verificación falla con
`unable to get local issuer certificate`.

Ese es el motivo por el que casi todos los ejemplos de PHP que circulan en
internet para leer la tasa del BCV traen `CURLOPT_SSL_VERIFYPEER => false`.
**Aquí no se hace eso:** desactivar la verificación dejaría que cualquier
intermediario en la red dictara la tasa con la que se paga la nómina. En vez de
apagar la verificación, se aporta el intermedio correcto y la verificación
queda **activa y además anclada** a la CA que se espera.

### Cuándo hay que tocarlo

Solo si el BCV cambia de proveedor de certificados o si este intermedio vence
(2036). El síntoma sería que el botón «Consultar BCV» empiece a fallar con un
mensaje de certificado. Mientras tanto, la nómina nunca se bloquea: la tasa
siempre se puede cargar a mano.

Para regenerarlo, la URL sale del propio certificado del sitio
(campo *Authority Information Access → CA Issuers*):

```sh
echo | openssl s_client -connect www.bcv.org.ve:443 -servername www.bcv.org.ve 2>/dev/null \
  | openssl x509 -noout -text | grep -A2 "Authority Information Access"

curl -s -o inter.crt http://crt.sectigo.com/<archivo>.crt
openssl x509 -inform DER -in inter.crt -out bcv-sectigo-ca.pem
```
