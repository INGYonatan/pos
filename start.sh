#!/bin/bash

# --- Búsqueda de puertos ---

# Puerto para la App (inicia en 8000)
APP_PORT=8000
while sudo lsof -Pi :$APP_PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; do
    APP_PORT=$((APP_PORT+1))
done

# Puerto para phpMyAdmin (inicia en 8080)
PMA_PORT=8080
while sudo lsof -Pi :$PMA_PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; do
    PMA_PORT=$((PMA_PORT+1))
done

# Puerto para MariaDB (inicia en 33060 para evitar el 3306 estandar)
DB_PORT=33060
while sudo lsof -Pi :$DB_PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; do
    DB_PORT=$((DB_PORT+1))
done

# --- Exportar variables para Docker ---

export APP_PORT=$APP_PORT
export PMA_PORT=$PMA_PORT
export DB_PORT=$DB_PORT

# --- Iniciar Docker ---

docker compose up -d

# --- Mostrar URLs de acceso ---

echo "---------------------------------------------------"
echo "  UNIX ENVIRONMENT READY (Ubuntu/macOS)"
echo "  Web App URL:      http://localhost:$APP_PORT"
echo "  phpMyAdmin URL:   http://localhost:$PMA_PORT"
echo "  DB Host (DBeaver):localhost (Port: $DB_PORT)"
echo "---------------------------------------------------"
