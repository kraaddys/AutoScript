FROM jenkins/ssh-agent:latest

USER root
RUN apt-get update && apt-get install -y --no-install-recommends \
      php-cli php-xml php-curl php-mbstring git curl unzip \
    && rm -rf /var/lib/apt/lists/*