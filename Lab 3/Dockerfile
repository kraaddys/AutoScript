# lab03/Dockerfile
FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    TZ=Europe/Chisinau

# cron + tzdata + coreutils + procps (ps/pgrep/top)
RUN apt-get update && apt-get install -y --no-install-recommends \
    cron tzdata ca-certificates coreutils procps \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Зависимости Python (если файла нет/пустой — шаг пропускается)
COPY requirements.txt /app/requirements.txt
RUN [ -s requirements.txt ] && pip install --no-cache-dir -r requirements.txt || true

# Код и служебные файлы
COPY currency_exchange_rate.py cronjob entrypoint.sh /app/

# Папка данных (сохраняем .gitkeep, если есть в репо)
# Копируем data в промежуточное место, переносим .gitkeep (если существует), чистим временное
COPY data /.tmpdata
RUN mkdir -p /app/data \
 && [ -e "/.tmpdata/.gitkeep" ] && touch /app/data/.gitkeep || true \
 && rm -rf /.tmpdata \
 && chmod +x /app/entrypoint.sh

# Логи cron
RUN touch /var/log/cron.log && chmod 666 /var/log/cron.log

ENTRYPOINT ["/app/entrypoint.sh"]
