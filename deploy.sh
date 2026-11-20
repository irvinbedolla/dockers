#!/bin/sh
# Uso, desde el servidor:
#   sh /var/www/dockers/deploy.sh

set -e

CONTENEDOR=siconcilio-app
cd "$(dirname "$0")"

echo "==> Guardando cambios locales del servidor (git stash)"
HAY_CAMBIOS=$(git status --porcelain --untracked-files=no)
if [ -n "$HAY_CAMBIOS" ]; then
    git stash push -m "deploy $(date '+%Y-%m-%d %H:%M:%S')"
    HICE_STASH=1
else
    echo "    No hay cambios locales que guardar."
    HICE_STASH=0
fi

COMMIT_ANTES=$(git rev-parse HEAD)

echo "==> Bajando cambios (git pull)"
# --ff-only: si el servidor tiene commits propios, no inventa un merge;
# mejor se detiene y avisa.
if ! git pull --ff-only; then
    echo "!!  git pull falló. Regresando los cambios locales y deteniendo el deploy."
    if [ "$HICE_STASH" = 1 ]; then
        git stash pop
    fi
    exit 1
fi

if [ "$HICE_STASH" = 1 ]; then
    echo "==> Regresando cambios locales del servidor (git stash pop)"
    if ! git stash pop; then
        echo ""
        echo "!!  Conflicto al regresar los cambios locales. El deploy se detuvo"
        echo "!!  ANTES de reconstruir las cachés. Los cambios siguen guardados en"
        echo "!!  'git stash list'. Resuelva los archivos en conflicto ('git status'),"
        echo "!!  luego 'git stash drop' y vuelva a correr este script."
        exit 1
    fi
fi

# Solo instalar dependencias si composer.lock cambió con este pull.
if ! git diff --quiet "$COMMIT_ANTES" HEAD -- src/composer.lock; then
    echo "==> composer.lock cambió: instalando dependencias"
    docker exec "$CONTENEDOR" composer install --no-interaction --optimize-autoloader
fi

echo "==> Reconstruyendo cachés de Laravel"
docker exec "$CONTENEDOR" php artisan optimize:clear
docker exec "$CONTENEDOR" php artisan optimize
docker exec "$CONTENEDOR" php artisan permission:cache-reset

echo ""
echo "==> Deploy terminado: $(git log -1 --format='%h %s')"
