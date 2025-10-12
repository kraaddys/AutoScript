#!/bin/sh
set -e

# Протаскиваем env (включая API_KEY и TZ) для cron-задач
env > /etc/environment

# Таймзона (по умолчанию Europe/Chisinau)
: "${TZ:=Europe/Chisinau}"
echo "$TZ" > /etc/timezone
ln -snf "/usr/share/zoneinfo/$TZ" /etc/localtime

create_log_file() {
  touch /var/log/cron.log
  chmod 666 /var/log/cron.log
  echo "Log at /var/log/cron.log"
}

install_cron_jobs() {
  cp /app/cronjob /etc/cron.d/lab03
  chmod 644 /etc/cron.d/lab03
  # для совместимости: и в /etc/cron.d, и crontab -u root
  crontab -u root /etc/cron.d/lab03 2>/dev/null || true
}

monitor_logs() { tail -f /var/log/cron.log & }

run_cron() {
  echo "Starting cron..."
  exec cron -f
}

mkdir -p /app/data
create_log_file
install_cron_jobs
monitor_logs
run_cron
