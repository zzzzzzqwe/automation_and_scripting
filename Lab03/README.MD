# Лабораторная работа 3
# Студент: Gachayev Dmitrii, I2302
# Дата выполнения: 11.10.2025
# Цель
Научиться настраивать планировщик задач (cron) для автоматизации выполнения скриптов.

# Выполнение
В директории `lab03` создаю файл `cronjob` со следующим содержимым:
```bash
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
CRON_TZ=Europe/Chisinau
HOME=/app

# Для теста, каждую минуту
# * * * * * root . /etc/environment && DATE=$(date -d "yesterday" +\%F) && python /app/currency_exchange_rate.py --from MDL --to EUR --date "$DATE" --key "$API_KEY" --api-url "$API_URL" >> /var/log/cron.log 2>&1

# Для теста, каждую минуту
# DAYS=6
# * * * * * root . /etc/environment && START=$(date -d 'last week monday' +\%F) && for i in $(seq 0 $((DAYS))); do D=$(date -d "$START +$i day" +\%F); echo "[WEEKLY_TEST] $D"; python /app/currency_exchange_rate.py --from MDL --to USD --date "$D" --key "$API_KEY" --api-url "$API_URL"; done >> /var/log/cron.log 2>&1

# Ежедневно в 06:00 — курс MDL/EUR за предыдущий день
0 6 * * * root . /etc/environment && DATE=$(date -d 'yesterday' +\%F) && python /app/currency_exchange_rate.py --from MDL --to EUR --date "$DATE" --key "$API_KEY" --api-url "$API_URL" >> /var/log/cron.log 2>&1

# По пятницам в 17:00 — курс MDL/USD за предыдущую неделю
0 17 * * 5 root . /etc/environment && START=$(date -d 'last week monday' +\%F) && for i in 0 1 2 3 4 5 6; do D=$(date -d "$START +$i day" +\%F); python /app/currency_exchange_rate.py --from MDL --to USD --date "$D" --key "$API_KEY" --api-url "$API_URL"; done >> /var/log/cron.log 2>&1

```

Данный файл выполняет следующие задачи:
- Ежедневно в 6:00 получает курс MDL к EUR за предыдущий день.
- Еженедельно в пятницу в 17:00 для получает курс MDL к USD за предыдущую неделю.

Также в нем присутствуют откомментированные задачи которые использовались для тестирования:
- Выполнение первой задачи, указанной выше, каждую минуту
- Выполнение второй задачи, указанной выше, каждую минуту, с возможностью указания количества дней, для которых нужно получить курс. (переменная `DAYS`)

В случае необходимости можно разкомментировать их и проверить работу cron