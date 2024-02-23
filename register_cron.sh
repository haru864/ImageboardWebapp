#!/bin/bash

# スクリプトの実際のパスを取得（シンボリックリンクを解決）
SCRIPT_PATH=$(readlink -f "$0")

# スクリプトが存在するディレクトリのパスを取得
SCRIPT_DIR=$(dirname "$SCRIPT_PATH")

echo "*/1 * * * * /usr/bin/php ${SCRIPT_DIR}/src/Batch/DeleteInactiveThreads.php > ${SCRIPT_DIR}/log/batch.log 2>&1" >> /tmp/mycron
crontab /tmp/mycron
