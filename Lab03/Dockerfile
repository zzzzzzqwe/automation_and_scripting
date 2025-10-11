FROM python:3.12-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
      cron tzdata \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY currency_exchange_rate.py /app/
COPY cronjob /etc/cron.d/lab03
COPY entrypoint.sh /entrypoint.sh

RUN chmod 0644 /etc/cron.d/lab03 && \
    chmod +x /entrypoint.sh && \
    sed -i 's/\r$//' /etc/cron.d/lab03 /entrypoint.sh

RUN pip install --no-cache-dir requests

RUN touch /var/log/cron.log && chmod 666 /var/log/cron.log
ENV PYTHONUNBUFFERED=1

ENTRYPOINT ["/entrypoint.sh"]
