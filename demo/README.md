# Rediseño Web - David Herrero Terapeuta

## 📋 Descripción

Este es un rediseño completo y moderno de tu página web, optimizado para el sector de terapias naturales, PNL y astrología. El diseño sigue las últimas tendencias de 2025 para webs de salud y bienestar.

## 🎨 Características del Diseño

### Estilo Visual
- **Paleta de colores calmante**: Verde salvia, azul tranquilo, tonos tierra y crema
- **Tipografía profesional**: Combinación de Lora (títulos) y Montserrat (cuerpo)
- **Diseño minimalista**: Espacios en blanco, elementos limpios y fácil lectura
- **100% Responsive**: Perfecto en móvil, tablet y escritorio

### Tendencias 2025 Implementadas
- Enfoque holístico y personalización
- Diseño limpio con mucho espacio en blanco
- Colores naturales que transmiten calma
- Navegación intuitiva y clara
- Animaciones sutiles al hacer scroll
- Primera persona en todos los textos
- Enfoque en los beneficios para el cliente

## 📁 Archivos Incluidos

```
/
├── index.html          # Página principal
├── sobre-mi.html       # Página sobre ti y tu trayectoria
├── servicios.html      # Todos tus servicios detallados
├── blog.html           # Página de blog/artículos
├── contacto.html       # Formulario de contacto
├── styles.css          # Todos los estilos CSS
├── script.js           # JavaScript para interactividad
└── README.md           # Este archivo
```

## 🚀 Cómo Usar Esta Web

### 1. Subir los archivos
Todos los archivos HTML, CSS y JS deben estar en la carpeta raíz de tu servidor web.

### 2. Personalizar Contenido

#### Imágenes
Reemplaza los placeholders (las áreas con colores y emojis) con tus propias imágenes:
- Foto personal en "Sobre mí"
- Imágenes de tus servicios
- Imágenes de tu consulta
- Fotos para el blog

Recomendaciones de tamaño:
- Hero/principal: 800x800px
- Servicios: 600x450px
- Blog: 800x600px

#### Textos
Los textos están escritos en primera persona y son persuasivos, pero personalízalos:
1. Revisa la página "Sobre mí" y añade tu historia personal
2. Ajusta los precios en la página de Servicios
3. Actualiza datos de contacto (teléfono, email)
4. Añade tus artículos reales en el Blog

#### Colores (si quieres cambiarlos)
Edita el archivo `styles.css` en la sección de `:root`:
```css
:root {
    --primary-color: #4A7C59;     /* Verde principal */
    --secondary-color: #7BA7BC;   /* Azul secundario */
    --accent-color: #D4A574;      /* Dorado/accent */
    /* ... más colores */
}
```

### 3. Configurar el Formulario de Contacto

El formulario actualmente solo muestra un mensaje en consola. Necesitas conectarlo a tu backend o servicio de email:

**Opciones:**
1. **Formspree** (gratis): https://formspree.io/
2. **EmailJS** (gratis): https://www.emailjs.com/
3. **PHP mail()** si tu hosting lo soporta
4. **WordPress Contact Form 7** si migras a WordPress

En `script.js`, busca la función del formulario y conéctala a tu servicio elegido.

### 4. Añadir Google Analytics (opcional)
Añade tu código de seguimiento antes del cierre de `</head>` en cada HTML.

### 5. SEO Básico
Cada página tiene meta description. Personalízalas con palabras clave relevantes para tu zona:
- Terapeuta Mataró
- Medicina China Barcelona
- Acupuntura Maresme
- etc.

## 📱 Redes Sociales

En el footer hay un espacio para redes sociales. Añade tus iconos:
```html
<div class="footer-social">
    <a href="tu-instagram"><i class="fab fa-instagram"></i></a>
    <a href="tu-facebook"><i class="fab fa-facebook"></i></a>
    <!-- etc -->
</div>
```

No olvides añadir Font Awesome para los iconos:
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

## 🎯 Páginas Creadas

### 1. **index.html** - Página Principal
- Hero con tu propuesta de valor
- Sección de problemas que resuelves
- Tu enfoque terapéutico
- Servicios resumidos
- Casos que acompañas
- Testimonios
- CTA final

### 2. **sobre-mi.html** - Sobre Ti
- Introducción personal
- Tu historia y camino
- Tu filosofía de trabajo
- Formación y certificaciones

### 3. **servicios.html** - Servicios Detallados
- Terapias Manuales Orientales
- Medicina Tradicional China
- Coaching y PNL
- Carta Astral
- Beneficios de cada servicio
- Proceso de trabajo

### 4. **blog.html** - Blog
- Listado de artículos
- Categorías
- Artículos populares
- Suscripción newsletter
- (Los artículos son ejemplos, añade los tuyos reales)

### 5. **contacto.html** - Contacto
- Formulario completo
- Tu información de contacto
- Preguntas frecuentes
- Disponibilidad horaria

## 🛠 Mantenimiento

### Actualizar el Blog
Para añadir un nuevo artículo en `blog.html`, copia este código:
```html
<article class="blog-card">
    <div class="blog-image">
        <div class="blog-image-placeholder">🌿</div>
    </div>
    <div class="blog-info">
        <div class="blog-meta">
            <span class="blog-category">Categoría</span>
            <span>Fecha</span>
        </div>
        <h3>Título del artículo</h3>
        <p class="blog-excerpt">
            Resumen del artículo...
        </p>
        <a href="url-articulo.html" class="blog-link">
            Leer más →
        </a>
    </div>
</article>
```

### Actualizar Testimonios
En `index.html`, busca la sección `.testimonials-grid` y añade/modifica testimonios.

## ⚠️ Importante

### Antes de publicar:
- [ ] Reemplaza TODAS las imágenes placeholder
- [ ] Actualiza tu email y teléfono en contacto.html y footer
- [ ] Configura el formulario de contacto
- [ ] Revisa y personaliza todos los textos
- [ ] Añade tu propia foto en sobre-mi.html
- [ ] Actualiza los precios (o quita "Consultar" y pon precios reales)
- [ ] Añade tus redes sociales en el footer
- [ ] Prueba la web en móvil antes de publicar

### Recomendaciones:
- Usa imágenes propias y de calidad
- Optimiza el peso de las imágenes (máx 200KB por imagen)
- Actualiza el blog regularmente (mínimo 1 vez al mes)
- Responde rápido a los formularios de contacto
- Pide testimonios a tus clientes reales

## 🆘 Soporte Técnico

### Problemas comunes:

**El menú móvil no funciona:**
Asegúrate de que script.js está enlazado correctamente.

**Los estilos no se aplican:**
Verifica que styles.css está en la misma carpeta que los HTML.

**Las fuentes no cargan:**
Necesitas conexión a internet para Google Fonts.

## 📊 Próximos Pasos Recomendados

1. **SEO Local**: Registra tu negocio en Google My Business
2. **Blog Activo**: Publica 1-2 artículos al mes
3. **Redes Sociales**: Conecta Instagram/Facebook
4. **Email Marketing**: Usa la lista de newsletter
5. **Analítica**: Añade Google Analytics para ver visitas

## 💡 Consejos de Marketing

- Usa fotos reales tuyas y de tu consulta (genera confianza)
- Pide testimonios a clientes satisfechos
- Comparte artículos del blog en redes sociales
- Ofrece una primera consulta con descuento
- Actualiza tu web regularmente

## 📞 Migración desde WordPress

Si tu actual web es WordPress y quieres migrar a esta versión:
1. Exporta tus artículos del blog
2. Guarda todas tus imágenes
3. Copia testimonios actuales
4. Configura redirecciones 301 de URLs antiguas

---

**¡Tu nueva web está lista!** 🎉

Solo necesitas personalizarla con tu contenido e imágenes y estarás listo para atraer más clientes.

¿Dudas? Revisa este archivo o contacta a tu desarrollador web.
