# Before / After

Bloque opcional para comparar dos imágenes con un deslizador accesible, para
`dbravoan/dbcms-core:~0.0.1`. Las flechas/rango se ejecutan con Alpine del núcleo;
no requiere una biblioteca de terceros ni servicios externos.

```bash
composer require dbravoan/dbcms-blocks-before-after:~0.0.1
```

Activa `before_after` en Administración → Paquetes de bloques. Al desactivarlo,
las instancias guardadas se conservan y dejan de renderizar o aparecer en el
catálogo activo. Reactivarlo restaura disponibilidad. No posee tablas de negocio.

El payload incluye `title`, `image_url`, `after_image_url`, `before_label` y
`after_label`. El esquema, validación, traducción, URLs seguras y consentimiento
se resuelven mediante los contratos compartidos del core. Los temas pueden
sobrescribir su vista; las dos imágenes deben describirse con etiquetas claras.

- [Mantenimiento específico](https://github.com/dbravoan/dbcms-blocks-before-after/blob/main/docs/MAINTENANCE.md)
- [Desarrollo y pruebas](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/TESTING.md)
- [Publicación](https://github.com/dbravoan/dbcms-module-template/blob/main/docs/PUBLICATION.md)
- [Contratos públicos del core](https://github.com/dbravoan/dbcms-core/tree/main/docs)

Licencia MIT.
