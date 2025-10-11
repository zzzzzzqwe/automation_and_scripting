# Используем базовый образ Python
FROM python:3.12-slim

# Устанавливаем системные пакеты:
#  - cron — планировщик задач, чтобы запускать скрипт по расписанию
#  - tzdata — для настройки часового пояса (Europe/Chisinau)
RUN apt-get update && apt-get install -y --no-install-recommends \
      cron tzdata \
    && rm -rf /var/lib/apt/lists/*   # очищаем кэш apt для уменьшения размера образа

# Рабочая директория внутри контейнера
WORKDIR /app

# Копируем необходимые файлы внутрь контейнера:
#  - Python-скрипт (основная логика получения курсов)
#  - cronjob (расписание заданий)
#  - entrypoint.sh (точка входа, запускает cron и мониторинг логов)
COPY currency_exchange_rate.py /app/
COPY cronjob /etc/cron.d/lab03
COPY entrypoint.sh /entrypoint.sh

# Настраиваем права и формат переводов строк (Windows → Linux)
# 0644 для cronjob — читается системой; +x для entrypoint — делает его исполняемым
RUN chmod 0644 /etc/cron.d/lab03 && \
    chmod +x /entrypoint.sh && \
    sed -i 's/\r$//' /etc/cron.d/lab03 /entrypoint.sh

# Устанавливаем Python-библиотеку requests
RUN pip install --no-cache-dir requests

# Создаём лог cron и задаём права на запись
RUN touch /var/log/cron.log && chmod 666 /var/log/cron.log
# Переменная для вывода Python в
ENV PYTHONUNBUFFERED=1

# Запуск entrypoint.sh — он создаёт лог, запускает cron и следит за логами
ENTRYPOINT ["/entrypoint.sh"]
