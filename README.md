### Tienda-Impresoras API 🖨️🛒
### SLIM Framework PHP + MySQL + JWT

Este proyecto utiliza Slim Framework para gestionar una tienda virtual de impresoras y cartuchos, permitiendo operaciones como alta de productos, consultas de stock y ventas, junto con autenticación de usuarios mediante JWT.🔐
Basado en la consigna del Segundo Parcial de la materia Programación III.

#### Parte 1: Gestión de Productos y Ventas

1. **Alta de Productos**
   - **index.php**: Recibe todas las peticiones y define las rutas a través de Slim.
   - **/tienda/alta** (POST): Permite ingresar productos con Marca, Precio, Tipo ("Impresora" o "Cartucho"), Modelo, Color y Stock. Las imágenes se guardan en /ImagenesDeProductos/2024. 📸

2. **Consulta de Productos**
   - **/tienda/consultar** (POST): Permite consultar la existencia de productos por Marca, Tipo y Color. 🔍

3. **Alta de Ventas**
   - **/ventas/alta** (POST): Registra ventas asociadas a productos existentes en la tienda. Guarda imágenes en /ImagenesDeVenta/2024. 💼

4. **Consulta de Ventas**
   - **/ventas/consultar** (GET):
     - **/productos/vendidos**: Cantidad de productos vendidos en un día específico.
     - **/ventas/porUsuario**: Listado de ventas de un usuario.
     - **/ventas/porProducto**: Listado de ventas por tipo de producto.
     - **/productos/entreValores**: Listado de productos cuyo precio está entre dos valores.
     - **/ventas/ingresos**: Listado de ingresos por día.
     - **/productos/masVendido**: Producto más vendido. 📊

5. **Modificación de Ventas**
   - **/ventas/modificar** (PUT): Modifica ventas existentes según el número de pedido y otros detalles. 🛠️

#### Parte 2: Gestión de Usuarios

6. **Tabla de Usuarios**
   - **/registro** (POST): Agrega usuarios con datos como mail, usuario, contraseña, perfil y foto en /ImagenesDeUsuarios/2024/. 🧑‍💻

   - **/login** (POST): Realiza el login y devuelve un token JWT para autenticar al usuario junto con su perfil. 🔑

#### Parte 3: Middleware y Seguridad

7. **Confirmación de Perfil**
   - **ConfirmarPerfil**: Middleware que confirma el perfil del usuario mediante JWT. 🛡️

   - **Restricciones de Acceso**:
     - Las rutas /tienda/alta y /tienda/consultar/ventas/ingresos están limitadas a usuarios administradores.
     - Las rutas de consulta y ventas (/tienda/consultar/* y /ventas/*) están limitadas a usuarios administradores y empleados.
     
#### Parte 4: Funcionalidad Adicional

8. **Descarga de Ventas**
   - **/ventas/descargar**: Descarga un CSV con el listado de ventas 📥
