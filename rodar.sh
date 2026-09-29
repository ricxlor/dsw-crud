#!/usr/bin/env bash
# Sobe o CRUD de alunos usando o PHP e o MySQL já instalados na máquina (Linux/macOS).
# Uso: ./rodar.sh   (ou: bash rodar.sh)
set -e
cd "$(dirname "$0")"

PORTA=${PORTA:-8080}

if ! command -v php >/dev/null; then
    echo "ERRO: PHP não encontrado. Instale com: sudo apt install php-cli php-mysql"
    exit 1
fi

# Usa a extensão mysqli; se não estiver habilitada no php.ini, tenta carregá-la
EXT=()
if ! php -r 'exit(extension_loaded("mysqli") ? 0 : 1);'; then
    if php -d extension=mysqli -r 'exit(extension_loaded("mysqli") ? 0 : 1);' 2>/dev/null; then
        EXT=(-d extension=mysqli)
    else
        echo "ERRO: extensão mysqli do PHP não encontrada. Instale com: sudo apt install php-mysql"
        exit 1
    fi
fi

echo "Dados de acesso ao MySQL (Enter aceita o valor entre colchetes)"
read -r -p "Host [localhost]: " DB_HOST
read -r -p "Usuário [root]: " DB_USER
read -r -s -p "Senha (Enter se não tiver): " DB_PASS
echo
export DB_HOST=${DB_HOST:-localhost}
export DB_USER=${DB_USER:-root}
export DB_PASS

php "${EXT[@]}" preparar_banco.php "$@"

echo
echo "Sistema rodando em http://localhost:$PORTA  (Ctrl+C para parar)"
php "${EXT[@]}" -S "localhost:$PORTA"
