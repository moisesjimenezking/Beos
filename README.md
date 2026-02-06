# Sistema de Microservicios

## Arquitectura del Sistema

El sistema está basado en **microservicios desacoplados**. 

Cada servicio (API, PostgreSQL, Redis) tiene su propio **Dockerfile** y puede ejecutarse de manera **autónoma**, sin depender de un Docker Compose.  

### Microservicios Principales

| Servicio          | Rol                               | Tecnologías           | Contenedor    | Puerto |
|-------------------|-----------------------------------|-----------------------|---------------|--------|
| **API (Laravel)** | Lógica de negocio y API RESTful   | Laravel + PHP 8.4 FPM | `api_ms`      | 8000   |
| **PostgreSQL**    | Base de datos relacional          | PostgreSQL            | `postgres_ms` | 5432   |
| **Redis**         | Sistema de Cache                  | Redis 7               | `redis_ms`    | 6379   |
| **Orquestador**   | Coordina arranque de servicios    | Bash scripts          | N/A           | N/A    |

> Nota: **Redis no se utiliza actualmente**, por lo que la API depende únicamente de la base de datos para almacenamiento de caché (opcionalmente en `database`).

---

## Arranque del Sistema

### 1. Arranque con el Orquestador

El orquestador facilita iniciar todos los servicios de forma coordinada:

```bash
cd orchestrator
sh orchestrator.sh
```

# El script realiza:

- Creación de la red Docker si no existe.
- Inicio del contenedor de PostgreSQL.
- Inicio del contenedor de la API (Laravel) y ejecución de migraciones si la base de datos está disponible.
- Notificación de errores si algún servicio falla, pero continúa con los demás servicios.

### 2. Arranque de Servicios Individuales

Si prefieres iniciar los servicios de manera independiente:

# Ejemplo 

**Servicio API (Laravel)**
```bash
cd services/api
sh start.sh
```

# El script realiza:

- Verifica que el puerto de la API esté libre.
- Construye la imagen api_img.
- Lanza el contenedor api_ms y ejecuta migraciones si la base de datos está disponible.

**Endpoints**

Están ubicados en el archivo Beos.json

**Acceso a la API:**
URL: [http://localhost:8000/api]
