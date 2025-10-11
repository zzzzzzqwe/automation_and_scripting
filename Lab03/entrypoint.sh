#!/bin/sh

# Скрипт entrypoint запускается автоматически при старте контейнера.
# Он подготавливает лог, сохраняет окружение и запускает cron и tail.

create_log_file() {
    # Создаём файл лога cron и даём права на запись всем пользователям
    echo "Creating log file..."
    touch /var/log/cron.log
    chmod 666 /var/log/cron.log
    echo "Log file created at /var/log/cron.log"
}

monitor_logs() {
    # Параллельно отслеживаем изменения в cron.log,
    # чтобы вывод было видно в логах контейнера
    echo "=== Monitoring cron logs ==="
    tail -f /var/log/cron.log
}

run_cron() {
    # Запускаем демон cron в foreground-режиме,
    # чтобы контейнер не завершался после запуска
    echo "=== Starting cron daemon ==="
    exec cron -f
}

# Сохраняем все переменные окружения (API_URL, API_KEY и т.д.)
# чтобы cron мог их подгружать через `. /etc/environment`
env > /etc/environment

# Создаём лог и запускаем наблюдение в фоне
create_log_file
monitor_logs &

# Запускаем cron как основной процесс
run_cron
