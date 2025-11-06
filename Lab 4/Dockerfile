FROM jenkins/ssh-agent:latest

USER root
RUN apt-get update && apt-get install -y --no-install-recommends \
      php-cli git curl unzip \
    && rm -rf /var/lib/apt/lists/*
