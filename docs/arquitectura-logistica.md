# Arquitectura inicial de Boxping

## Alcance implementado

El núcleo se organiza en migraciones Laravel y modelos Eloquent. `contactos` representa remitentes y destinatarios; `agencias` conserva alias y datos de contacto, una central asignada, un código numérico de cinco dígitos y el consecutivo de folios; las tarifas se versionan y sus reglas registran el cálculo por zona y servicio. La guía guarda el precio cotizado y un `snapshot_tarifa`, para que cambios de tarifas futuras no modifiquen importes históricos.

Los códigos de agencia se asignan secuencialmente en una transacción y no se editan desde el panel. La creación requiere una central activa. Las tarifas base globales (sin agencia asociada) se pueden clonar a una agencia con central activa; la copia conserva sus reglas independientemente para permitir ajustes sin cambiar la tarifa de origen.

## Catálogo geográfico e importación

Las tablas `paises`, `estados` y `ciudades` forman el catálogo geográfico. Las ciudades se asocian a una zona tarifaria; la migración crea las zonas A, B, C y D y las nuevas ciudades se asignan por defecto a A. En la administración, edite una ciudad para cambiarla de zona. Al crear una guía se elige país y ciudad destino; el sistema obtiene automáticamente ISO-2 y zona desde el catálogo, y conserva ciudad y zona en la guía. En una tarifa, agregue una regla con componente `cargo_zona` y la zona B/C/D correspondiente para calcular un importe extra; no se añade ningún cargo de zona si no existe esa regla.

El importador recibe un CSV UTF-8 con encabezados `country_iso2,country_name,state_code,state_name,city`; `city_id` y `zone_code` son opcionales. Cada fila representa una ciudad. Si no se indica `zone_code`, se asigna A. Los países, estados y ciudades se actualizan de forma idempotente usando ISO-2, código de estado y `city_id` (o nombre cuando no hay identificador). Las zonas mencionadas deben existir previamente en Administración → Zonas.

```csv
country_iso2,country_name,state_code,state_name,city,city_id,zone_code
MX,México,CMX,Ciudad de México,Cuauhtémoc,MX-CMX-001,
MX,México,JAL,Jalisco,Guadalajara,MX-JAL-001,B
US,United States,CA,California,Los Angeles,US-CA-001,C
```

Importe con `php artisan boxping:importar-ciudades ruta/al/ciudades.csv`. Antes de importar, aplique las migraciones con `php artisan migrate`. El proyecto incluye el mecanismo y el formato de importación, no un dataset mundial precargado; se debe proporcionar el CSV geográfico que se quiera usar.

El archivo `database/seeders/data/ciudades-america-geonames.csv` contiene la carga inicial de GeoNames para los países y territorios de Norteamérica, Centroamérica, el Caribe y Sudamérica. Incluye localidades de su conjunto `cities15000`, asignadas inicialmente a zona A. Fuente: [GeoNames export](https://download.geonames.org/export/dump/), archivos `cities15000.zip`, `countryInfo.txt` y `admin1CodesASCII.txt`. GeoNames se distribuye bajo [Creative Commons Attribution 4.0](https://creativecommons.org/licenses/by/4.0/); conservar esta atribución al reutilizar o redistribuir los datos. Para repetir la carga: `php artisan boxping:importar-ciudades database/seeders/data/ciudades-america-geonames.csv`.

En Administración → Paquetería → Países se pueden activar o desactivar destinos. Los países inactivos no aparecen al crear ciudades ni guías; el servidor también rechaza crear una guía hacia un país inactivo. Desactivar es preferible a borrar, porque conserva el catálogo y el historial de guías.

Las guías hijas tienen folio independiente y pertenecen a una guía madre. Estatus, pesajes e interacciones conservan sus propios registros con fecha y usuario opcional. Los consolidados operativos (`consolidados`) agrupan guías y se mantienen dentro del alcance actual.

Los importes se expresan en la moneda de la tarifa. El peso se maneja en libras y las dimensiones en pulgadas. `divisor_volumetrico` representa pulg³ por libra facturable (por ejemplo 139) y debe configurarse por versión de tarifa; se calcula el peso volumétrico como largo × ancho × alto × piezas / divisor.

## Generación de folios

`App\Services\Logistica\GuiaFolioService` genera `IATA(3) + código de agencia(5) + consecutivo(5)`. Bloquea la fila de agencia dentro de una transacción para serializar solicitudes concurrentes. El consecutivo se agota en 99 999; el servicio falla explícitamente en vez de truncarlo. Como en cualquier secuencia transaccional con reservas separadas de la creación, puede haber huecos si la creación de la guía falla después de reservar el folio; los folios no se deben reutilizar.

La creación de la guía y la persistencia del resultado de `TarifaCalculator` deben ocurrir juntas en una transacción de aplicación. Guarde la salida completa del cálculo en `snapshot_tarifa` además de los importes y el `tarifa_id`; no recalcule guías emitidas al editar tarifas.

## Cálculo de tarifas

Las guías se cotizan con `App\Services\Tarifas\CotizadorServicio`:

- **Servicios** (catálogo global): tipo de cobro `caja` (por pieza; las medidas estándar se registran en el servicio), `libra` (peso facturable = mayor entre real y volumétrico) o `pie_cubico`.
- **Servicios de la tarifa** (`tarifa_servicios`): cada tarifa da de alta los servicios con su `precio_agencia` y `precio_central` por unidad. El precio al remitente es la suma de ambos. El desglose queda en `snapshot_tarifa`.
- **Seguro por país** (`seguros_pais`): opcional u obligatorio, porcentaje y mínimo para agencia y para central.
- **Impuestos** (módulo propio, opcional por tarifa): catálogo de fórmulas `VALORDECLARADO(umbral,%Age:%FFW por debajo,%Age:%FFW por encima)`. %Age es el porcentaje de la agencia y %FFW el de la central sobre el valor declarado; hasta el umbral aplica el primer par y por encima el segundo. La tarifa elige un impuesto o ninguno. Ejemplo: `VALORDECLARADO(2000,0:0,0:30)`.
- **Libras mínimas** (por tarifa): si el peso facturable es menor, se cobra el mínimo; por encima se cobra según el peso. El divisor volumétrico convierte largo × ancho × alto (pulg³) en libras volumétricas.
- Las zonas A-D siguen en la base de datos pero ya no intervienen en el cálculo ni en el menú.

`TarifaCalculator` y `tarifa_reglas` se conservan solo para tarifas históricas; las guías nuevas no los usan.

Para conservar el histórico, trate una versión de tarifa usada por una guía como inmutable. Al cambiar precios, cree una nueva versión con sus propias reglas; las tarifas de agencia se pueden clonar desde una tarifa base sin alterar esa base.

## Relaciones principales

- `Agencia` → `Central`, `Guia`, `Tarifa` y `Consolidado`.
- `Guia` → remitente/destinatario (`Contacto`), agencia, tarifa, hijas, eventos y consolidados.
- `Tarifa` → reglas (`TarifaRegla`), zona opcional, agencia opcional y tarifa de origen de clonación.

El módulo de logística transfronteriza (bodegas, escaneos de almacén, manifiestos, importación masiva, reempaques, transportistas, choferes, vehículos y consolidados logísticos) fue retirado. Una migración elimina sus tablas y datos; las tablas históricas de creación se conservan para no alterar migraciones ya ejecutadas.

## Estructura recomendada al crecer

```text
app/
  Filament/Resources/          # Recursos Filament por módulo
  Http/Requests/               # Validación y autorización de entradas
  Jobs/                        # Procesamiento asíncrono, si se incorpora
  Models/
  Policies/                    # Alcance por agencia, central y operador
  Services/
    Logistica/                 # Folios, tracking y consolidados operativos
    Tarifas/                   # Cotización, clonación y versionado
  Enums/                       # Estados, componentes y servicios
```

## Panel administrativo

El proyecto usa Filament 4 y expone el panel protegido en `/admin`; la raíz `/` redirige al acceso del panel. Los formularios y recursos están en español para agencias, centrales, contactos, guías, tarifas, zonas y consolidados operativos. El alta de guías genera folio, calcula el precio con la tarifa elegida y registra el estatus inicial.

Solo los usuarios con `is_admin = true` pueden entrar al panel. Después de aplicar migraciones, cree una cuenta propia desde la terminal (pedirá el password de forma oculta):

```bash
php artisan migrate --force
php artisan boxping:crear-administrador
```

En local, arranque Laravel con `php artisan serve` y visite `http://127.0.0.1:8000/admin`. En XAMPP bajo una subcarpeta, configure `APP_URL` con esa ruta pública, por ejemplo `http://localhost/boxping/public`, y visite `http://localhost/boxping/public/admin`. La aplicación registra también las rutas de Livewire bajo el prefijo de `APP_URL`, necesario para que los formularios funcionen en esa subcarpeta. Si el document root apunta directamente a `public/`, `APP_URL` debe ser la URL raíz del host, sin `/public`. No se crea un usuario ni una contraseña predeterminada.

## Despliegue en SiteGround

- Verifique que el plan de hosting use PHP 8.2 o superior y habilite las extensiones requeridas por Laravel, incluyendo PDO para el motor elegido.
- Configure MySQL mediante las variables `DB_*` del entorno de producción; no suba `.env` al repositorio ni use SQLite como base operativa.
- En desarrollo local, `.env.example` configura `DB_CONNECTION=mysql`, `DB_DATABASE=boxping` y el host/usuario local de MySQL. Cree la base `boxping` en MySQL antes de ejecutar `php artisan migrate --force`; si cambió variables de conexión con configuración en caché, ejecute antes `php artisan config:clear`.
- Apunte el dominio a `public/`, proteja el resto del proyecto fuera del document root cuando el plan lo permita y configure permisos de escritura para `storage/` y `bootstrap/cache/`.
- Ejecute `php artisan migrate --force` en el despliegue controlado, y programe `php artisan schedule:run` cada minuto si se habilitan tareas programadas.
- Confirme soporte de cron y procesos en segundo plano del plan antes de depender de workers persistentes; si no están disponibles, use cron/colas administradas y trabajos de duración acotada para importaciones CSV.
- Instale las dependencias con Composer durante el despliegue para que Filament y sus recursos estén disponibles; la dependencia está declarada en `composer.json`.
