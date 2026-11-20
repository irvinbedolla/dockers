#!/bin/sh
# Pantalla de mantenimiento de SíConcilio.
#
# Uso, desde el servidor:
#   sh /var/www/dockers/mantenimiento.sh on            Activa el mantenimiento
#   sh /var/www/dockers/mantenimiento.sh on CLAVE      Activa y deja pasar a quien abra /CLAVE
#   sh /var/www/dockers/mantenimiento.sh off           Lo quita
#   sh /var/www/dockers/mantenimiento.sh estado        Dice si está activo
#
# Mientras está activo, todos ven resources/views/errors/503.blade.php y la
# página se recarga sola cada 60 s, así que cuando se quita la gente regresa
# al sistema sin hacer nada.
#
# Con CLAVE, quien abra https://<dominio>/CLAVE una vez recibe una cookie y
# puede usar el sistema normalmente para revisar que todo quedó bien antes
# de abrirlo a todos. Use una clave difícil de adivinar.

set -e

CONTENEDOR=siconcilio-app
ARCHIVO_DOWN=storage/framework/down  # relativo a /var/www (WORKDIR del contenedor)

case "$1" in
    on)
        if [ -n "$2" ]; then
            docker exec "$CONTENEDOR" php artisan down --render="errors::503" --refresh=60 --retry=60 --secret="$2"
            echo "==> Mantenimiento ACTIVO. Acceso para revisar: /$2"
        else
            docker exec "$CONTENEDOR" php artisan down --render="errors::503" --refresh=60 --retry=60
            echo "==> Mantenimiento ACTIVO."
        fi
        ;;
    off)
        docker exec "$CONTENEDOR" php artisan up
        echo "==> Mantenimiento desactivado. El sistema está en línea."
        ;;
    estado)
        if docker exec "$CONTENEDOR" test -f "$ARCHIVO_DOWN"; then
            echo "Mantenimiento: ACTIVO"
        else
            echo "Mantenimiento: inactivo (sistema en línea)"
        fi
        ;;
    *)
        echo "Uso: sh $0 on [CLAVE] | off | estado"
        exit 1
        ;;
esac
