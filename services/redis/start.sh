#!/bin/bash

SERVICE_NAME="redis_ms"
IMAGE_NAME="redis_ms_img"
NETWORK_NAME="products-ms-network"
PORT=6379

# Red
if ! docker network inspect $NETWORK_NAME >/dev/null 2>&1; then
  echo "🔧 Creando red $NETWORK_NAME"
  docker network create $NETWORK_NAME
fi

# Puerto
PORT_FLAG=""
if ss -ltn | awk '{print $4}' | grep -q ":$PORT$"; then
  echo "⚠️ Puerto $PORT ocupado. Redis se iniciará SIN exponer puerto."
else
  echo "🔓 Puerto $PORT libre. Redis expondrá el puerto."
  PORT_FLAG="-p $PORT:6379"
fi

docker build -t $IMAGE_NAME .

docker stop $SERVICE_NAME 2>/dev/null
docker rm $SERVICE_NAME 2>/dev/null

docker run -d \
  --name $SERVICE_NAME \
  --network $NETWORK_NAME \
  --restart always \
  $PORT_FLAG \
  $IMAGE_NAME

# Validación real
if docker ps --filter "name=$SERVICE_NAME" --filter "status=running" | grep $SERVICE_NAME >/dev/null; then
  echo "✅ Redis iniciado correctamente"
else
  echo "❌ Redis NO pudo iniciar"
  exit 1
fi
