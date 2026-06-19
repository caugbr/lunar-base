#!/usr/bin/env bash

# usecomposer.sh
# Usar o composer em hosts compartilhados
# Executa o comando, instalando o composer antes, se não existir

# Função para exibir o roteiro detalhado da instalação por tópicos
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
    echo -e "\033[1mExemplos:\033[0m"
    echo "  ./usecomposer.sh --install-only   # Apenas instala o Composer"
    echo "  ./usecomposer.sh install          # Instala Composer (se necessário) e roda 'composer install'"
    echo "  ./usecomposer.sh update           # Instala Composer (se necessário) e roda 'composer update'"
    echo ""
    exit 0
}

INSTALL_ONLY=false

# Lendo os argumentos do usuário
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

# Configurações
COMPOSER_DIR="$HOME/bin"
COMPOSER_BIN="$COMPOSER_DIR/composer"

# Em alguns casos, 'php' pode não ser o CLI.
# Ajuste aqui se necessário: php-cli, php82, php81, etc.
PHP_BIN="php"

# 1. Detecta ou instala o Composer
if command -v composer &>/dev/null; then
    COMPOSER_CMD="composer"
    echo -e "\033[0;32m✔ Composer encontrado globalmente.\033[0m"
elif [[ -x "$COMPOSER_BIN" ]]; then
    COMPOSER_CMD="$COMPOSER_BIN"
    echo -e "\033[0;32m✔ Composer encontrado em $COMPOSER_BIN.\033[0m"
else
    echo "📦 Composer não detectado. Instalando em $COMPOSER_DIR..."
    mkdir -p "$COMPOSER_DIR"

    # Instalação oficial
    curl -sS https://getcomposer.org/installer | "$PHP_BIN" -- --install-dir="$COMPOSER_DIR" --filename=composer

    if [[ -f "$COMPOSER_BIN" ]]; then
        chmod +x "$COMPOSER_BIN"
        echo -e "\033[0;32m✅ Instalação concluída.\033[0m"
        COMPOSER_CMD="$COMPOSER_BIN"
    else
        echo "❌ Falha ao baixar/instalar o Composer." >&2
        exit 1
    fi
fi

# 2. Se --install-only, termina aqui
if [[ "$INSTALL_ONLY" == true ]]; then
    echo -e "\033[0;32m✔ Composer pronto para uso: $COMPOSER_CMD\033[0m"
    exit 0
fi

# 3. Executa o comando passado pelo usuário, mantendo todos os argumentos
"$COMPOSER_CMD" "$@"
