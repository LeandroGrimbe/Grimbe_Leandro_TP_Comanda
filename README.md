# La Comanda - Sistema de Gestión para un Restaurante

## Descripción
Este proyecto es una API desarrollada en **PHP** utilizando **Slim v4**, con base de datos en **MySQL** y arquitectura **API Rest**, diseñada para la gestión de todo el sistema de un restaurante, administrando sus pedidos, mesas, empleados y facturacion.

## Funcionalidades
- **Gestión de Pedidos:**
  - Creación de comandas con asignación de código único.
  - Seguimiento de estados: "pendiente", "en preparación", "listo para servir".
  - Registro de tiempo estimado y finalización de cada pedido.
  - Consulta de estado de pedidos por parte del cliente.
  - Encuestas de satisfacción tras la finalización del servicio.
- **Gestión de Mesas:**
  - Asignación de código único por mesa.
  - Estados de mesa: "con cliente esperando pedido", "con cliente comiendo", "con cliente pagando", "cerrada".
  - Administración de facturación y análisis de uso de mesas.
- **Gestión de Empleados:**
  - Roles diferenciados: mozos, cocineros, cerveceros, bartenders, socios.
  - Registro de días y horarios de ingreso.
  - Control de operaciones por sector y empleado.
  - Alta, suspensión y baja de empleados.
- **Reportes y Estadísticas:**
  - Productos más y menos vendidos.
  - Pedidos retrasados y cancelados.
  - Mesas con mayor y menor facturación.
  - Comentarios de clientes y calificaciones del servicio.

