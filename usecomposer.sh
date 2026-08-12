#!/usr/bin/env bash

# usecomposer.sh
# Usar o composer em hosts compartilhados e consoles PHP

exibir_ajuda() {
    echo ""
    echo -e "\033[1;32mScript para usar o composer em hosts compartilhados\033[0m"
    echo -e "\033[1;32m===================================================\033[0m"
    echo ""
    echo -e "\033[1mUso:\033[0m"
    echo "  ./usecomposer.sh [opções] [comando do composer]"
    echo ""
    echo -e "\033[1mOpções:\033[0m"
    echo "  -h, --help         Exibe esta ajuda"
    echo "  -i, --install-only Apenas instala o Composer, não executa comando"
    echo ""
    exit 0
}

INSTALL_ONLY=false

while [[ "$#" -gt 0 ]]; do
    case "$1" in
        -h|--help)
            exibir_ajuda
            ;;
        -i|--install-only)
            INSTALL_ONLY=true
            shift
            ;;
        *)
            break
            ;;
    esac
done

set -e

# 1. FORÇA A CRIAÇÃO E EXPORTAÇÃO DAS VARIÁVEIS DE AMBIENTE (Exigido pelo Composer)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [[ -z "$HOME" || "$HOME" == "/" ]]; then
    export HOME="$SCRIPT_DIR"
else
    export HOME="$HOME"
fi

# Define a pasta de trabalho do Composer para cache e configurações
export COMPOSER_HOME="$SCRIPT_DIR/bin/cache"

COMPOSER_DIR="$SCRIPT_DIR/bin"
COMPOSER_BIN="$COMPOSER_DIR/composer"
PHP_BIN="php"

# 2. DETECÇÃO E INSTALAÇÃO
if [[ -f "$COMPOSER_BIN" ]]; then
    COMPOSER_CMD="$PHP_BIN $COMPOSER_BIN"
    echo -e "\033[0;32m✔ Composer local encontrado em $COMPOSER_BIN.\033[0m"
else
    echo "📦 Composer local não encontrado. Instalando em $COMPOSER_DIR..."
    mkdir -p "$COMPOSER_DIR"
    mkdir -p "$COMPOSER_HOME"

    # Baixa e instala o Composer garantindo que as variáveis de ambiente foram exportadas
    curl -sS https://getcomposer.org/installer | "$PHP_BIN" -- --install-dir="$COMPOSER_DIR" --filename=composer

    if [[ -f "$COMPOSER_BIN" ]]; then
        chmod +x "$COMPOSER_BIN"
        echo -e "\033[0;32m✅ Instalação concluída em $COMPOSER_BIN.\033[0m"
        COMPOSER_CMD="$PHP_BIN $COMPOSER_BIN"
    else
        echo "❌ Falha ao baixar/instalar o Composer." >&2
        exit 1
    fi
fi

if [[ "$INSTALL_ONLY" == true ]]; then
    echo -e "\033[0;32m✔ Composer pronto para uso: $COMPOSER_CMD\033[0m"
    exit 0
fi

# 3. EXECUTA O COMANDO SOLICITADO
$COMPOSER_CMD "$@"
