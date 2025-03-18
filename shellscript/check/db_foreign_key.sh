#!/bin/bash

# 環境変数の設定（適宜変更）
DB_NAME="Boot-Build"
MYSQL_USER="root"
MYSQL_PASSWORD="${DB_ROOT_PASSWORD}"  # または適切な値
MYSQL_CONTAINER="ssd_storage_mysql"

QUERY="SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='${DB_NAME}' AND REFERENCED_TABLE_NAME IS NOT NULL;"

docker exec -it ${MYSQL_CONTAINER} mysql -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -e "$QUERY"
