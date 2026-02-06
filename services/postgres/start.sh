#!/bin/bash

SERVICE_NAME="postgres_ms"
IMAGE_NAME="postgres_ms_img"
NETWORK_NAME="products-ms-network"
PORT=5433

# Red
if ss -ltn | awk '{print $4}' | grep -q ":$PORT$"; then
  echo "🔧 Creando red $NETWORK_NAME"
  docker network create $NETWORK_NAME
fi

# Puerto
PORT_FLAG=""
if ss -ltn | awk '{print $4}' | grep -q ":$PORT$"; then
  echo "⚠️ Puerto $PORT ocupado. PostgreSQL se iniciará SIN exponer puerto."
else
  echo "🔓 Puerto $PORT libre. PostgreSQL expondrá el puerto."
  PORT_FLAG="-p $PORT:5433"
fi

docker build -t $IMAGE_NAME .

docker stop $SERVICE_NAME 2>/dev/null
docker rm $SERVICE_NAME 2>/dev/null

docker run -d \
  --name $SERVICE_NAME \
  --network $NETWORK_NAME \
  --restart always \
  -v pgdata:/var/lib/postgresql/data \
  $PORT_FLAG \
  $IMAGE_NAME

if docker ps --filter "name=$SERVICE_NAME" --filter "status=running" | grep $SERVICE_NAME >/dev/null; then
  echo "✅ PostgreSQL iniciado correctamente"
else
  echo "❌ PostgreSQL NO pudo iniciar"
  exit 1
fi
