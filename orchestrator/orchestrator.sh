#!/bin/bash

echo "[ORQ] Iniciando orquestador de microservicios..."

SERVICES=(
  "../services/redis/start.sh"
  "../services/postgres/start.sh"
  "../services/api/start.sh"
)

for service in "${SERVICES[@]}"; do
  echo "[ORQ] Levantando $(basename $service)..."
  sh "$service"
  sleep 3
done

echo "[ORQ] Todos los servicios se han intentado levantar."
