#!/usr/bin/env bash

# =========================================================================
# Lunar Base — Script de Instalação e Inicialização Interativa
# =========================================================================

set -e

# -----------------------------------------------------------------------------
# Cores
# -----------------------------------------------------------------------------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

# -----------------------------------------------------------------------------
# Helpers
# -----------------------------------------------------------------------------
ask() {
    local prompt="$1"
    local default="$2"
    local input
    if [ -n "$default" ]; then
        echo -en "${prompt} [${CYAN}${default}${NC}]: " >&2
        read -rp "" input
        input="${input:-$default}"
    else
        echo -en "${prompt}: " >&2
        read -rp "" input
    fi
    echo -e "  ${GREEN}→ ${input}${NC}" >&2
    echo "$input"
}

ask_required() {
    local prompt="$1"
    local input=""
    while [ -z "$input" ]; do
        echo -en "${prompt}: " >&2
        read -rp "" input
        if [ -z "$input" ]; then
            echo -e "${RED}  → Este campo é obrigatório.${NC}" >&2
        fi
    done
    echo -e "  ${GREEN}→ ${input}${NC}" >&2
    echo "$input"
}

ask_secret() {
    local prompt="$1"
    local input=""
    while [ -z "$input" ]; do
        printf "${CYAN}?${NC} %s: " "$prompt" >&2
        IFS= read -rsp "" input
        printf "\n" >&2
        if [ -z "$input" ]; then
            printf "${RED}  → Este campo é obrigatório.${NC}\n" >&2
        fi
    done
    echo "$input"
}

ask_secret_confirm() {
    local prompt="$1"
    local pass1=""
    local pass2=""
    while true; do
        pass1=$(ask_secret "$prompt")
        printf "${CYAN}?${NC} Confirme a senha: " >&2
        IFS= read -rsp "" pass2
        printf "\n" >&2
        if [ "$pass1" = "$pass2" ]; then
            echo "$pass1"
            return
        fi
        printf "${RED}  → As senhas não conferem. Tente novamente.${NC}\n" >&2
    done
}

confirm() {
    local prompt="$1"
    local default="$2"
    local input
    if [ "$default" = "y" ]; then
        echo -en "${prompt} [${CYAN}Y/n${NC}]: " >&2
        read -rp "" input
        input="${input:-Y}"
    else
        echo -en "${prompt} [${CYAN}y/N${NC}]: " >&2
        read -rp "" input
        input="${input:-N}"
    fi
    echo -e "  ${GREEN}→ ${input}${NC}" >&2
    case "${input,,}" in
        y|yes|s|sim) return 0 ;;
        *) return 1 ;;
    esac
}

# -----------------------------------------------------------------------------
# Ajuda
# -----------------------------------------------------------------------------
exibir_ajuda() {
    echo ""
    echo -e "${BOLD}${GREEN}Lunar Base — Script de Instalação${NC}"
    echo -e "${GREEN}===================================${NC}"
    echo ""
    echo -e "${BOLD}Uso:${NC}"
    echo "  ./install.sh --help     Exibe esta ajuda"
    echo "  ./install.sh --dry-run  Coleta os dados e gera o JSON de usuários, mas não instala nada"
    echo "  ./install.sh            Inicia a instalação interativa"
    echo ""
    echo -e "${BOLD}Roteiro de execução:${NC}"
    echo "  1. Coleta interativa: nome e URL do site"
    echo "  2. Coleta interativa: nome, e-mail e senha do administrador"
    echo "  3. Coleta interativa: senha dos usuários de demonstração"
    echo "  4. Coleta interativa: configuração do banco de dados"
    echo "  5. Gera arquivo temporário com dados dos usuários"
    echo "  6. Instala dependências do Composer"
    echo "  7. Configura ambiente (.env, APP_KEY, APP_NAME, APP_URL, DB_*, SQLite)"
    echo "  8. Executa migrações e seeds"
    echo "  9. Cria link simbólico do storage"
    echo " 10. Limpa caches do framework"
    echo ""
    exit 0
}

# -----------------------------------------------------------------------------
# Parse de argumentos
# -----------------------------------------------------------------------------
while [[ "$#" -gt 0 ]]; do
    case "$1" in
        -h|--help)
            exibir_ajuda
            ;;
        -dr|--dry-run)
            DRY_RUN=true
            shift
            ;;
        *)
            echo -e "${RED}Opção inválida: $1${NC}"
            echo "Use ./install.sh --help para ver as opções."
            exit 1
            ;;
    esac
done

# -----------------------------------------------------------------------------
# Banner
# -----------------------------------------------------------------------------
echo ""
echo -e "${BOLD}${GREEN}    ╔════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}    ║          LUNAR BASE SETUP          ║${NC}"
echo -e "${BOLD}${GREEN}    ║     Instalação Interativa v1.0     ║${NC}"
echo -e "${BOLD}${GREEN}    ╚════════════════════════════════════╝${NC}"
echo ""

# -----------------------------------------------------------------------------
# Verificações prévias
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[CHECK] Verificando dependências do sistema...${NC}"

if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP não encontrado. Instale o PHP (>= 8.1) antes de continuar.${NC}"
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}  ✔ PHP ${PHP_VERSION} encontrado${NC}"

if ! command -v composer &> /dev/null; then
    if [ -x "./usecomposer.sh" ]; then
        # Detecta Windows (Git Bash, Cygwin, MSYS)
        if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "cygwin" || "$OSTYPE" == "win32" ]]; then
            echo -e "${RED}  ✗ Composer não encontrado.${NC}"
            echo -e "${RED}    O usecomposer.sh não é compatível com Windows.${NC}"
            echo -e "${RED}    Antes de executar este script, instale o Composer: https://getcomposer.org/download/${NC}"
            exit 1
        else
            echo -e "${YELLOW}  → Composer não encontrado, mas usecomposer.sh está disponível.${NC}"
        fi
    else
        echo -e "${YELLOW}  ⚠ Composer não encontrado e usecomposer.sh não disponível.${NC}"
        echo -e "${YELLOW}    Instale o Composer manualmente ou adicione usecomposer.sh ao projeto.${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}  ✔ Composer encontrado${NC}"
fi

echo ""

# -----------------------------------------------------------------------------
# 1. Coleta de dados — Informações do Site
# -----------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}┌─────────────────────────────────────────┐${NC}"
echo -e "${BOLD}${CYAN}│  Informações do Site                    │${NC}"
echo -e "${BOLD}${CYAN}└─────────────────────────────────────────┘${NC}"
echo ""

APP_NAME=$(ask "Nome do site" "Lunar Base")
APP_URL=$(ask "URL do site" "http://localhost")

# Extrai domínio da URL via PHP temporário
PHP_HOST=$(mktemp /tmp/lunar_host.XXXXXX.php)
cat > "$PHP_HOST" << 'PHPEOF'
<?php
$url = getenv('LUNAR_APP_URL');
$parsed = parse_url($url);
$host = $parsed['host'] ?? 'lunar.base';
echo $host;
PHPEOF
export LUNAR_APP_URL="$APP_URL"
MAIL_HOST=$(php "$PHP_HOST")
rm -f "$PHP_HOST"

echo ""

# -----------------------------------------------------------------------------
# 2. Coleta de dados — Administrador
# -----------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}┌─────────────────────────────────────────┐${NC}"
echo -e "${BOLD}${CYAN}│  Dados do Administrador Principal       │${NC}"
echo -e "${BOLD}${CYAN}└─────────────────────────────────────────┘${NC}"
echo ""

ADMIN_NAME=$(ask_required "Nome do administrador")
ADMIN_EMAIL=$(ask_required "E-mail do administrador")
ADMIN_PASSWORD=$(ask_secret_confirm "Senha do administrador")

echo ""

# -----------------------------------------------------------------------------
# 3. Coleta de dados — Usuários de Demonstração
# -----------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}┌─────────────────────────────────────────┐${NC}"
echo -e "${BOLD}${CYAN}│  Usuários de Demonstração               │${NC}"
echo -e "${BOLD}${CYAN}└─────────────────────────────────────────┘${NC}"
echo ""

PHP_PREVIEW=$(mktemp /tmp/lunar_preview.XXXXXX.php)
cat > "$PHP_PREVIEW" << 'PHPEOF'
<?php
$config = include 'config/rolesPermissions.php';
$appUrl = getenv('LUNAR_APP_URL');
$parsed = parse_url($appUrl);
$mailHost = $parsed['host'] ?? 'lunar.base';
if (empty($mailHost)) {
    $mailHost = 'lunar.base';
}

foreach ($config['roles'] as $slug => $roleData) {
    if ($slug === 'admin') continue;
    echo $slug . ':' . $roleData['name'] . ':' . $slug . '@' . $mailHost . "\n";
}
PHPEOF

DEMO_ROLES=$(php "$PHP_PREVIEW")
rm -f "$PHP_PREVIEW"

echo -e "${YELLOW}  Serão criados usuários para cada role:${NC}"
while IFS=: read -r slug name email; do
    [ -z "$slug" ] && continue
    echo -e "    ${CYAN}• ${email}${NC}  (${name})"
done <<< "$DEMO_ROLES"
echo ""

DEMO_PASSWORD=$(ask_secret_confirm "Senha dos usuários de demonstração")

echo ""

# -----------------------------------------------------------------------------
# 4. Opção de persistência
# -----------------------------------------------------------------------------
KEEP_DATA=false
if confirm "Deseja manter os dados informados para futuras instalações/seeds?" "n"; then
    KEEP_DATA=true
    echo -e "${GREEN}  → Dados serão preservados em storage/app/.install/${NC}"
else
    echo -e "${YELLOW}  → Dados serão removidos após a instalação${NC}"
fi

echo ""

# -----------------------------------------------------------------------------
# 5. Coleta de dados — Banco de Dados
# -----------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}┌─────────────────────────────────────────┐${NC}"
echo -e "${BOLD}${CYAN}│  Configuração do Banco de Dados         │${NC}"
echo -e "${BOLD}${CYAN}└─────────────────────────────────────────┘${NC}"
echo ""

DB_CONNECTION=$(ask "Banco de dados (sqlite/mysql/pgsql/sqlsrv)" "sqlite")

DB_HOST=""
DB_PORT=""
DB_DATABASE=""
DB_USERNAME=""
DB_PASSWORD=""

if [ "$DB_CONNECTION" = "sqlite" ]; then
    echo -e "${GREEN}  → SQLite selecionado. Não requer host, porta, usuário ou senha.${NC}"
else
    DB_HOST=$(ask "Host do banco" "127.0.0.1")
    DB_PORT=$(ask "Porta do banco" "3306")
    DB_DATABASE=$(ask "Nome do banco" "lunar_base")
    DB_USERNAME=$(ask "Usuário do banco" "root")
    DB_PASSWORD=$(ask_secret_confirm "Senha do banco")
fi

echo ""

# -----------------------------------------------------------------------------
# 6. Geração do JSON temporário
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[1/7] Preparando dados de usuários...${NC}"

INSTALL_DIR="storage/app/.install"
JSON_FILE="${INSTALL_DIR}/default_users_data.json"

mkdir -p "$INSTALL_DIR"

PHP_TMP=$(mktemp /tmp/lunar_setup.XXXXXX.php)
cat > "$PHP_TMP" << 'PHPEOF'
<?php
$config = include 'config/rolesPermissions.php';

$adminName = getenv('LUNAR_ADMIN_NAME');
$adminEmail = getenv('LUNAR_ADMIN_EMAIL');
$adminPass = getenv('LUNAR_ADMIN_PASSWORD');
$demoPass = getenv('LUNAR_DEMO_PASSWORD');
$appUrl = getenv('LUNAR_APP_URL');

$mailHost = preg_replace("#^https?://([^/:]+).*$#", "$1", $appUrl);
if (empty($mailHost)) {
    $mailHost = 'lunar.base';
}

$users = [];

$users[] = [
    'name' => $adminName,
    'email' => $adminEmail,
    'password' => $adminPass,
    'role' => 'admin'
];

foreach ($config['roles'] as $slug => $roleData) {
    if ($slug === 'admin') continue;
    $users[] = [
        'name' => $roleData['name'],
        'email' => $slug . '@' . $mailHost,
        'password' => $demoPass,
        'role' => $slug
    ];
}

$jsonFile = getenv('LUNAR_JSON_FILE');
file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";
PHPEOF

export LUNAR_ADMIN_NAME="$ADMIN_NAME"
export LUNAR_ADMIN_EMAIL="$ADMIN_EMAIL"
export LUNAR_ADMIN_PASSWORD="$ADMIN_PASSWORD"
export LUNAR_DEMO_PASSWORD="$DEMO_PASSWORD"
export LUNAR_JSON_FILE="$JSON_FILE"

php "$PHP_TMP"
rm -f "$PHP_TMP"

echo -e "${GREEN}  ✔ Dados salvos em ${JSON_FILE}${NC}"
echo ""

# Interromper antes de executar
if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  → Modo dry-run ativado. Instalação interrompida.${NC}"
    exit 0
fi

# -----------------------------------------------------------------------------
# 7. Composer install
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[2/7] Instalando dependências do Composer...${NC}"
if command -v composer &> /dev/null; then
    composer install --no-interaction --prefer-dist
else
    echo -e "${YELLOW}  → Composer não encontrado. Usando usecomposer.sh...${NC}"
    ./usecomposer.sh install --no-interaction --prefer-dist
fi
echo -e "${GREEN}  ✔ Dependências instaladas.${NC}"
echo ""

# -----------------------------------------------------------------------------
# 8. Ambiente (.env)
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[3/7] Configurando ambiente...${NC}"

if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
    echo -e "${GREEN}  ✔ Arquivo .env criado e APP_KEY gerada.${NC}"
else
    echo -e "${YELLOW}  → Arquivo .env já existe. Pulando criação.${NC}"
fi

# Atualiza APP_NAME, APP_URL e DB_* no .env
export LUNAR_APP_NAME="$APP_NAME"
export LUNAR_DB_CONNECTION="$DB_CONNECTION"
export LUNAR_DB_HOST="$DB_HOST"
export LUNAR_DB_PORT="$DB_PORT"
export LUNAR_DB_DATABASE="$DB_DATABASE"
export LUNAR_DB_USERNAME="$DB_USERNAME"
export LUNAR_DB_PASSWORD="$DB_PASSWORD"

php -r "
\$env = file_get_contents('.env');
\$env = preg_replace('/^APP_NAME=.*/m', 'APP_NAME=\"' . str_replace('\"', '\\\\\"', getenv('LUNAR_APP_NAME')) . '\"', \$env);
\$env = preg_replace('/^APP_URL=.*/m', 'APP_URL=' . getenv('LUNAR_APP_URL'), \$env);
\$env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=' . getenv('LUNAR_DB_CONNECTION'), \$env);
\$env = preg_replace('/^#?DB_HOST=.*/m', 'DB_HOST=' . getenv('LUNAR_DB_HOST'), \$env);
\$env = preg_replace('/^#?DB_PORT=.*/m', 'DB_PORT=' . getenv('LUNAR_DB_PORT'), \$env);
\$env = preg_replace('/^#?DB_DATABASE=.*/m', 'DB_DATABASE=' . getenv('LUNAR_DB_DATABASE'), \$env);
\$env = preg_replace('/^#?DB_USERNAME=.*/m', 'DB_USERNAME=' . getenv('LUNAR_DB_USERNAME'), \$env);
\$env = preg_replace('/^#?DB_PASSWORD=.*/m', 'DB_PASSWORD=' . getenv('LUNAR_DB_PASSWORD'), \$env);
file_put_contents('.env', \$env);
"

echo -e "${GREEN}  ✔ APP_NAME, APP_URL e DB_* atualizados no .env${NC}"

chmod 600 .env 2>/dev/null || echo -e "${YELLOW}  ⚠ Não foi possível aplicar chmod 600 no .env.${NC}"

if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    echo -e "${GREEN}  ✔ Banco SQLite criado.${NC}"
fi

echo ""

# -----------------------------------------------------------------------------
# 9. Migrações e Seeds
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[4/7] Executando migrações e seeds...${NC}"
php artisan migrate --force
php artisan db:seed --force
echo -e "${GREEN}  ✔ Banco de dados atualizado e populado.${NC}"
echo ""

# -----------------------------------------------------------------------------
# 10. Storage link
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[5/7] Verificando link do Storage...${NC}"
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo -e "${GREEN}  ✔ Link simbólico criado.${NC}"
else
    echo -e "${YELLOW}  → Link simbólico já existe.${NC}"
fi
echo ""

# -----------------------------------------------------------------------------
# 11. Permissões e Cache
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[6/7] Ajustando permissões e limpando caches...${NC}"
chmod -R 775 storage bootstrap/cache 2>/dev/null || echo -e "${YELLOW}  ⚠ Permissões de pasta puladas.${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}  ✔ Caches limpos.${NC}"
echo ""

# -----------------------------------------------------------------------------
# 12. Limpeza do JSON
# -----------------------------------------------------------------------------
echo -e "${YELLOW}[7/7] Finalizando...${NC}"

if [ "$KEEP_DATA" = false ]; then
    rm -f "$JSON_FILE"
    echo -e "${GREEN}  ✔ Arquivo temporário de usuários removido.${NC}"
else
    echo -e "${GREEN}  ✔ Arquivo temporário preservado em: ${JSON_FILE}${NC}"
fi

echo ""

# -----------------------------------------------------------------------------
# Resumo final
# -----------------------------------------------------------------------------
echo -e "${BOLD}${GREEN}=========================================${NC}"
echo -e "${BOLD}${GREEN}    Lunar Base instalado com sucesso!    ${NC}"
echo -e "${BOLD}${GREEN}=========================================${NC}"
echo ""

PHP_TMP=$(mktemp /tmp/lunar_summary.XXXXXX.php)
cat > "$PHP_TMP" << 'PHPEOF'
<?php
$jsonFile = getenv('LUNAR_JSON_FILE');
if (!file_exists($jsonFile)) {
    echo "⚠ Arquivo de dados não encontrado.\n";
    exit(0);
}

$users = json_decode(file_get_contents($jsonFile), true);
if (!$users) {
    echo "⚠ Erro ao ler dados dos usuários.\n";
    exit(0);
}

echo "Usuários criados:\n\n";

foreach ($users as $u) {
    $isAdmin = ($u['role'] === 'admin');
    $label = $isAdmin ? 'sua senha de administrador' : 'senha de demonstração';
    echo "     {$u['name']}\n";
    echo "     E-mail: {$u['email']}\n";
    echo "     Senha:  ({$label})\n";
    echo "     Role:   {$u['role']}\n\n";
}
PHPEOF

export LUNAR_JSON_FILE="$JSON_FILE"
php "$PHP_TMP"
rm -f "$PHP_TMP"

echo -e "${BOLD}Próximos passos:${NC}"
echo -e "  ${CYAN}→${NC} php artisan serve"
echo ""
