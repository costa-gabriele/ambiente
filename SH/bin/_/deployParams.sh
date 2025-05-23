#!/bin/sh

# WEB

export WEB_REPOSITORY_SERVER="localhost"
export WEB_REPOSITORY_DIR="/web/repository/dir"
export WEB_DEPLOYMENT_DIR="/web/deployment/dir"

# DB

export DB_COMMAND="mysql"
export DB_USERNAME="root"
export DB_PASSWORD=""
export DB_HOST="localhost"
export DB_SCHEMA=""
export DB_CONNECTION="\"${DB_COMMAND}\" -u ${DB_USERNAME} -p${DB_PASSWORD} -h ${DB_HOST} ${DB_SCHEMA}"
export DB_REPOSITORY_SERVER="localhost"
export DB_REPOSITORY_DIR="../../DB"
export DB_INSTALLATION_FILE="${DB_REPOSITORY_DIR}/install.txt"
export DB_DEPLOYMENT_DIR="/db/deployment/dir"
