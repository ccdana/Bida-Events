# Conectar Google (botón «Continuar con Google»)

Con esto aparece el botón de entrar y crear cuenta con Google. La persona entra con su cuenta de
Google, el sitio recibe su nombre, correo y foto, y la cuenta queda con el correo ya confirmado (no
hace falta que le llegue un correo). **Sin las dos variables del paso 5 el botón no se muestra.**

**Tiempo:** unos 20 minutos.
**Necesitas:** tu cuenta de Google y acceso al `.env` del servidor.
**Costo:** gratis. Solo se piden los permisos básicos (nombre, correo, foto), por lo que **Google no
exige revisión de la app**.

---

## Paso 1. Crear el proyecto

1. Entra a [console.cloud.google.com](https://console.cloud.google.com) con tu cuenta de Google
   (acepta los términos si es la primera vez).
2. Arriba a la izquierda, junto al logo, abre el **selector de proyecto** → **Proyecto nuevo**.
3. Nombre: `Bida Events` → **Crear**.
4. Espera unos segundos y elige ese proyecto en el selector.

## Paso 2. Configurar la pantalla de consentimiento

Es lo que ve la persona cuando Google le pregunta si deja entrar a Bida Events.

1. Menú ☰ → **APIs y servicios** → **Pantalla de consentimiento de OAuth** (en algunas cuentas se
   llama **Google Auth Platform**). Pulsa **Comenzar**.
2. **Información de la aplicación:** nombre `Bida Events`; correo de asistencia: el tuyo →
   **Siguiente**.
3. **Público:** **Externo** → **Siguiente**.
4. **Información de contacto:** tu correo → **Siguiente**.
5. Acepta la política de datos de usuario → **Continuar** → **Crear**.

### Marca (Branding)

En el menú lateral → **Marca**:

| Campo | Valor |
| --- | --- |
| Página principal de la aplicación | `https://bida-events.com` |
| Vínculo a la política de privacidad | `https://bida-events.com/legal/privacidad` |
| Vínculo a las condiciones del servicio | `https://bida-events.com/legal/terminos` |
| Dominios autorizados | `bida-events.com` |

Guarda. **No subas logo**: con logo, Google pide una verificación que con la app básica no hace falta.

### Permisos (Data Access)

En el menú lateral → **Acceso a los datos** → **Agregar o quitar permisos** y deja solo estos tres:

- `openid`
- `.../auth/userinfo.email`
- `.../auth/userinfo.profile`

Guarda. No agregues ninguno más.

## Paso 3. Crear las credenciales

1. Menú lateral → **Clientes** (o *APIs y servicios › Credenciales*) → **+ Crear cliente** (*Crear
   credenciales › ID de cliente de OAuth*).
2. **Tipo de aplicación:** **Aplicación web**. **Nombre:** `Bida Events web`.
3. **Orígenes autorizados de JavaScript:** déjalo vacío.
4. **URI de redireccionamiento autorizados** → **+ Agregar URI** y escribe, **exactamente así**:

   ```text
   https://bida-events.com/auth/google/callback
   ```

   Sin barra final, con `https` y sin `www`. Una diferencia mínima da el error `redirect_uri_mismatch`.
   Para probar en tu computador, agrega también la del entorno local (por ejemplo
   `https://tu-subdominio.ngrok-free.app/auth/google/callback`); la guía está en
   [`../pruebas-locales.md`](../pruebas-locales.md).
5. **Crear**.
6. Aparece una ventana con el **ID de cliente** y el **Secreto del cliente**. **Cópialos ahora** (o
   *Descargar JSON*). Si pierdes el secreto, en el cliente pulsa **Agregar secreto** y crea otro.

## Paso 4. Publicar la app

Mientras la app esté «En prueba», **solo pueden entrar las cuentas que agregues como usuarios de
prueba**. Para que entre cualquier persona:

1. Menú lateral → **Público** (*Audience*).
2. **Estado de publicación** → **Publicar aplicación** → confirma.
3. Debe quedar en **En producción**.

Como solo usas los permisos básicos, no te pedirá verificación. (Puede mostrar una pantalla de
«aplicación no verificada» solo si agregaste permisos extra o un logo.)

## Paso 5. Poner las variables en el servidor

En el `.env` del servidor:

```dotenv
GOOGLE_CLIENT_ID=123456789-xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxx
```

`APP_URL` debe ser `https://bida-events.com`: de ahí sale la dirección de regreso.

Aplica el cambio:

```bash
alias dc='docker compose -f compose.prod.yaml'
dc up -d
```

Los cambios hechos en Google pueden tardar unos minutos en aplicarse.

## Paso 6. Probar

1. Abre una ventana privada → `https://bida-events.com/login` (o la página de registro).
2. Debe aparecer el botón **Continuar con Google**. Púlsalo, elige una cuenta y acepta.
3. Vuelves al sitio con la sesión iniciada. En Admin › Revendedores, si la persona era nueva, la cuenta
   aparece con el correo ya confirmado.

Cómo se comporta (para que no te sorprenda):

- Si ya existe una cuenta con ese correo, se **vincula** a Google y entra.
- Si no existe, **se crea** — solo si el registro está abierto (`AUTH_REGISTRATION=true`). Con `false`
  dice «No hay ninguna cuenta con ese correo».
- Si Google no tiene el correo confirmado, no deja entrar y pide usar correo y contraseña.

---

## Si falla

| Error | Causa y solución |
| --- | --- |
| No aparece el botón | `GOOGLE_CLIENT_ID` o `GOOGLE_CLIENT_SECRET` vacíos, o falta `dc up -d` tras editar el `.env` |
| `redirect_uri_mismatch` (Error 400) | La URI del paso 3 no es idéntica a `https://bida-events.com/auth/google/callback` |
| `access_blocked` / «app no verificada» / solo entran tus cuentas | La app sigue «En prueba»: paso 4 |
| `invalid_client` | ID o secreto mal copiados (sin espacios), o corresponden a otro proyecto |
| «No hay ninguna cuenta con ese correo» | `AUTH_REGISTRATION=false`: ábrelo para permitir cuentas nuevas con Google |

Siguiente: [03-lemon-squeezy.md](03-lemon-squeezy.md).
