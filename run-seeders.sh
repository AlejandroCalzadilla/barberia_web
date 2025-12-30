#!/bin/bash
# Script para ejecutar seeders en Render
# Ejecutar con: ./run-seeders.sh

echo "🚀 Ejecutando seeders en Render..."
php artisan app:run-seeders

echo "✅ Proceso completado"