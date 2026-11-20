# Mantener Before / After

El paquete posee `BeforeAfterBlock` y su vista de comparación. Core posee registro,
activación, validación, URLs seguras, traducciones, consentimiento, Alpine y render
compartido. No hay colas, credenciales ni tablas propias.

La composición base requiere únicamente core, default-clean y este candidato.
Verifica catálogo activo/inactivo, recuperación de definición, esquema, ejemplos
es/en, comparación accesible, URLs ejecutables rechazadas y protección de imágenes
externas sin autorización. El smoke instala el ZIP y alterna disponibilidad real.

Al ampliar payload o vista conserva las rutas traducibles y el rango accesible.
Una integración opcional nueva necesita su propio perfil, sin convertir otro
producto en requisito del perfil base.

Los procedimientos comunes tienen una sola fuente pública:

- [Desarrollo Docker](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/DEVELOPMENT.md)
- [Pruebas, perfiles y locks](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/TESTING.md)
- [Publicación por tag con SSH local](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/PUBLICATION.md)
- [Arquitectura y contratos](https://github.com/dbravoan/dbcms-core/tree/main/docs)

Tras cambiar dependencias o composición, regenera y revisa sus locks desde Docker.
Conserva el tooling local necesario para contribuir sin repositorios privados;
las actualizaciones de la plantilla se incorporan y prueban en este propietario.

`tests/Browser/comparison.spec.mjs` verifica el slider con teclado en tres tamaños.
Exporta `BeforeAfterTest` mediante `DBCMS_COMPARISON_FIXTURE_DIR`, conserva su build
y ejecuta Playwright desde Node 22 tras `npm --prefix tests/Browser ci`, con
`DBCMS_COMPARISON_FIXTURE_DIR` y `DBCMS_COMPARISON_ASSET_ROOT` apuntando al HTML y
al directorio público reales. `DBCMS_BROWSER_CHROME` selecciona Chromium y
`DBCMS_BROWSER_RESULTS` dirige la evidencia fuera del repositorio.
