#!/bin/bash

SERVICE_NAME="api_ms"
IMAGE_NAME="api_img"
NETWORK_NAME="products-ms-network"
PORT=8000
SERVICE_DIR="$(dirname "$(realpath "$0")")"
DB_CONTAINER_NAME="postgres_ms"  # nombre del contenedor de Postgres
MAX_WAIT=15  # segundos para esperar DB

# Red
if ! docker network inspect $NETWORK_NAME >/dev/null 2>&1; then
  echo "🔧 Creando red $NETWORK_NAME"
  docker network create $NETWORK_NAME
fi

# Detener y borrar contenedor previo
docker stop $SERVICE_NAME 2>/dev/null
docker rm $SERVICE_NAME 2>/dev/null

# Puerto obligatorio
if ss -ltn | awk '{print $4}' | grep -q ":$PORT$"; then
  echo "❌ Puerto $PORT ocupado. La API requiere este puerto."
  exit 1
else
  echo "🔓 Puerto $PORT libre. API expondrá el puerto."
fi

# Build usando SERVICE_DIR
docker build -t $IMAGE_NAME "$SERVICE_DIR"

# Run usando rutas absolutas
docker run -d \
  --name $SERVICE_NAME \
  --network $NETWORK_NAME \
  --restart always \
  -p $PORT:8000 \
  --env-file "$SERVICE_DIR/src/.env" \
  --add-host=host.docker.internal:host-gateway \
  -v "$SERVICE_DIR/src":/var/www \
  $IMAGE_NAME

# Verificar que el contenedor esté corriendo
if docker ps --filter "name=$SERVICE_NAME" --filter "status=running" | grep $SERVICE_NAME >/dev/null; then
  # Comprobar DB (espera máximo 15 segundos)
  echo "⏳ Comprobando si la base de datos ($DB_CONTAINER_NAME) está lista..."
  WAITED=0
  DB_READY=false
  while [ $WAITED -lt $MAX_WAIT ]; do
      if docker exec "$DB_CONTAINER_NAME" pg_isready -U "$POSTGRES_USER" >/dev/null 2>&1; then
          DB_READY=true
          break
      fi
      sleep 1
      WAITED=$((WAITED + 1))
  done

  if [ "$DB_READY" = true ]; then
      echo "✅ Base de datos lista. Ejecutando migraciones..."
      docker exec "$SERVICE_NAME" php /var/www/artisan migrate --force
      echo "✅ Migraciones aplicadas."
  else
      echo "⚠️ Base de datos NO disponible después de $MAX_WAIT segundos. Continuando sin migraciones."
  fi
  echo "✅ API Laravel iniciada en http://localhost:$PORT"
else
  echo "❌ API Laravel NO pudo iniciar"
  exit 1
fi
