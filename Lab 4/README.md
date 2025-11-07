# Лабораторная работа №4. Настройка Jenkins для автоматизации задач DevOps

**Студент:** Славов Константин, группа I2302

**Дата:** 05.11.2025

**Тема:** *CI/CD с использованием Jenkins и Docker Compose*

---

## 1. Описание лабораторной работы

### 1.1. Постановка задачи

Изучить основы настройки системы **Jenkins** для автоматизации DevOps-процессов, включая развертывание контроллера Jenkins, подключение SSH-агентов и создание конвейера CI/CD для PHP-проекта.

### 1.2. Цель

Научиться использовать Jenkins для реализации автоматизированных процессов сборки и тестирования в контейнерной среде Docker, а также освоить принципы взаимодействия между контроллером Jenkins и агентами.

### 1.3. Этапы выполнения

1. Развернуть **Jenkins Controller** через Docker Compose.
2. Настроить **SSH-агент** для удалённого выполнения.
3. Создать SSH-ключи и зарегистрировать их в Jenkins.
4. Добавить новый узел-агент.
5. Реализовать CI/CD конвейер.
6. Проверить работу пайплайна.

---

## 2. Практическая часть

### Шаг 1. Подготовка окружения

Так как до этого на компьютере был установлен Docker Desktop, то первым делом он был запущен и подготовлен к работе.

---

### Шаг 2. Создание Docker Compose и Dockerfile

Для запуска системы Jenkins в контейнерах был создан файл `docker-compose.yml`, который описывает оба сервиса: контроллер и агент.
Ниже приведена его структура:

```yaml
version: "3.8"

services:
  jenkins-controller:
    image: jenkins/jenkins:lts
    container_name: jenkins-controller
    user: "0:0"
    ports:
      - "8080:8080"
      - "50000:50000"
    volumes:
      - jenkins_home:/var/jenkins_home
    networks:
      - jenkins-network

  ssh-agent:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: ssh-agent
    user: "0:0"
    environment:
      - JENKINS_AGENT_SSH_PUBKEY=${JENKINS_AGENT_SSH_PUBKEY}
    volumes:
      - jenkins_agent_volume:/home/jenkins/agent
    depends_on:
      - jenkins-controller
    networks:
      - jenkins-network

volumes:
  jenkins_home:
  jenkins_agent_volume:

networks:
  jenkins-network:
    driver: bridge
```

Здесь создаются два контейнера: контроллер (управляет задачами Jenkins) и агент (выполняет конкретные сборки и тесты).
Они связаны через общую сеть, что позволяет Jenkins запускать сборки удалённо без лишних зависимостей.

Далее создаётся `Dockerfile`, в котором добавляются инструменты для работы с PHP-проектами и тестирования:

```dockerfile
FROM jenkins/ssh-agent:latest

USER root
RUN apt-get update && apt-get install -y --no-install-recommends \
      php-cli php-xml php-curl php-mbstring git curl unzip \
    && rm -rf /var/lib/apt/lists/*
```

Здесь к стандартному SSH-агенту добавляются необходимые инструменты для PHP-проектов — CLI, Git и библиотеки.
Это позволяет агенту выполнять тесты и устанавливать зависимости прямо внутри контейнера.

В ходе выполнения дальнейших шагов лабораторной работы стало ясно, что данный вариант файлов работает исправно и так, как было нужно.

Далее сборка контейнера и запуск:

```bash
docker compose up -d --build
```

![image](https://i.imgur.com/4rFrAKp.png)

![image](https://i.imgur.com/79SwYfh.png)

Из-за самой "быстрой" скорости интернета, контейнер собирался очень долго, но, по-итогу, был успешно собран и запущен.

После запуска Jenkins доступен по адресу `http://localhost:8080`

![image](https://i.imgur.com/wdXdFn9.png)

---

### Шаг 3. Настройка Jenkins Controller

* Вход по паролю из `/var/jenkins_home/secrets/initialAdminPassword`

![image](https://i.imgur.com/PHV9O1z.png)

* Установка плагинов
* Создание администратора

![image](https://i.imgur.com/kGDHNgR.png)

![image](https://i.imgur.com/TVuQlhe.png)

---

### Шаг 4. Подготовка SSH-агента

На этом шаге нужно перейти в папку `secrets` и произвести действие по генерации уникального SSH-ключа.

После перехода в директорию, необходимо прописать в консоль следующую команду:

```bash
ssh-keygen -t ed25519 -f jenkins_agent_ssh_key
```

![image](https://i.imgur.com/6Z9Gj0D.png)

После чего, при успешном завершении, создадутся 2 новых файла: `jenkins_agent_ssh_key` со следующим содержимым:

```env
JENKINS_AGENT_SSH_PUBKEY=ssh-ed25519 ключ ключ ключ @остальной ключ
```

И файл `jenkins_agent_ssh_key.pub` со следующим содержимым:

```env
-----BEGIN OPENSSH PRIVATE KEY-----
содержимое ключа
содержимое ключа
содержимое ключа
содержимое ключа
содержимое ключа
-----END OPENSSH PRIVATE KEY-----
```

Что означают и для чего нужны эти файлы:

* `jenkins_agent_ssh_key` — приватный ключ, который хранится локально и используется Jenkins для подключения

* `jenkins_agent_ssh_key.pub` — публичный ключ, добавляемый в переменную окружения .env

---

### Шаг 5. Настройка SSH-агента в Jenkins

**Действия:**

1. Установить плагин *SSH Agents Plugin*, если он ранее не был установлен.
2. `Manage Jenkins → Manage Credentials → Add SSH Key`.
3. Добавить приватный ключ `jenkins_agent_ssh_key`.
4. `Manage Nodes and Clouds → New Node`.
5. Настройки:

| Параметр              | Значение              |
| --------------------- | --------------------- |
| Node name             | ssh-agent1            |
| Type                  | Permanent Agent       |
| Remote root directory | /home/jenkins/agent   |
| Launch method         | SSH                   |
| Host                  | ssh-agent             |
| Credentials           | jenkins_agent_ssh_key |
| Label                 | php-agent             |

![image](https://i.imgur.com/mLxQVYJ.png)

![image](https://i.imgur.com/XRPRrQd.png)

![image](https://i.imgur.com/r3ROCzX.png)

После этого был успешно создан новый SSH-агент.

---

### Шаг 6. Создание Jenkins Pipeline

При создании Pipeline был задействован проект из одной лабораторной работу по курсу PHP. При его помощи были созданы несколько новых файлов для unit-тестов и работы с Pipeline.

Для этого в папке с проектом были созданы следующие файлы:

**Jenkinsfile:**

```groovy
pipeline {
    agent { label 'php-agent' }

    stages {
        stage('Install Dependencies') {
            steps {
                dir('Lab 4/recipe-book') {
                    echo 'Подготовка проекта...'
                    sh '''
            set -e
            php -v
            # Ставим composer локально в проект
            curl -sS https://getcomposer.org/installer -o composer-setup.php
            php composer-setup.php --filename=composer.phar
            php composer.phar --version
            # Устанавливаем зависимости
            php composer.phar install --no-interaction --prefer-dist
          '''
                }
            }
        }

        stage('Test') {
            steps {
                dir('Lab 4/recipe-book') {
                    echo 'Запуск тестов...'
                    sh '''
            set -e
            ./vendor/bin/phpunit --configuration phpunit.xml
          '''
                }
            }
        }
    }

    post {
        always { echo 'Конвейер завершен.' }
        success { echo 'Все этапы прошли успешно!' }
        failure { echo 'Обнаружены ошибки в конвейере.' }
    }
}
```

Как работает данный **Jenkinsfile**:

1. Этап **Install Dependencies** скачивает **Composer** и устанавливает все зависимости проекта.
2. Директива dir() указывает Jenkins, в какой подпапке проекта выполнять команды — в данном случае это папка `Lab 4/recipe-book`, где лежит PHP-проект и тесты.
3. Этап Test запускает unit-тесты с помощью **PHPUnit**.
4. Jenkins автоматически логирует каждый шаг и останавливает конвейер при первой ошибке (set -e).
5. Секция **post** добавляет уведомления в зависимости от результата выполнения.

После были созданы следующие файлы `composer.json` и `phpunit.xml`.

Этот файл позволяет **Composer** автоматически подключать **PHPUnit** и правильно распознавать структуру проекта и тестов.

**composer.json**

```json
{
  "require": { "php": ">=8.0" },
  "require-dev": { "phpunit/phpunit": "^10.5" },
  "autoload": { "psr-4": { "App\\": "src/" } },
  "autoload-dev": { "psr-4": { "App\\Tests\\": "tests/" } },
  "scripts": { "test": "vendor/bin/phpunit --configuration phpunit.xml" }
}
```

**phpunit.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true">
  <testsuites>
    <testsuite name="Unit">
      <directory>tests</directory>
    </testsuite>
  </testsuites>
</phpunit>
```

После создания и настройки всех файлов - делается коммит на **GitHub**, чтобы можно было создать рабочий **Pipeline**.

![image](https://i.imgur.com/iyi36R7.png)

Далее создается новый **Item** с **Pipeline**.

![image](https://i.imgur.com/ttdPSde.png)

![image](https://i.imgur.com/J6B6ZL5.png)

![image](https://i.imgur.com/mdoBIdt.png)

Наконец, после большого количества попыток получается сделать **Build** проекта.

![image](https://i.imgur.com/sRh0CPi.png)

После всех шагов **Jenkins** был успешно настроен и смог работать с удалённым агентом.
Пайплайн выполнялся без ошибок, что показало, как можно реализовать простой пример **CI/CD** для PHP-приложения в Docker-среде.

---

## 3. Контрольные вопросы и ответы

> **1. Какие преимущества использования Jenkins для DevOps?**
> 
> Jenkins автоматизирует сборку, тестирование и развертывание приложений, снижая количество ручных действий.
Благодаря большому количеству плагинов он легко интегрируется с GitHub, Docker, AWS и другими сервисами.
Это ускоряет разработку, делает процесс CI/CD стабильным и позволяет быстро обнаруживать ошибки на ранних этапах.

> **2. Какие бывают агенты Jenkins?**
> 
> Jenkins поддерживает несколько типов агентов:
> 
>**SSH-агенты** — подключаются по SSH для выполнения задач;
> 
>**Docker-агенты** — запускают сборки в контейнерах;
> 
>**Kubernetes-агенты** — создаются динамически в кластере;
> 
>**JNLP-агенты** — подключаются к контроллеру изнутри.
> 
>Такой подход позволяет распределять нагрузку и выполнять задачи в разных средах.

> **3. С какими проблемами столкнулись и как решили?**
> 
> При подключении агента возникла ошибка `Permission denied (publickey)` — проблему решили пересозданием SSH-ключей и правильной регистрацией в Jenkins.
Также агент не выполнял PHP-команды, что исправили добавлением установки `php-cli` и необходимых модулей в `Dockerfile`.
После этого пайплайн стал выполняться без ошибок.

---

## 4. Вывод

В ходе лабораторной работы был развернут Jenkins в контейнерах Docker, настроен контроллер и SSH-агент, обеспечивающий удалённое выполнение задач.
Созданы и зарегистрированы SSH-ключи, установлены необходимые плагины и подготовлено окружение для PHP-проектов.
С помощью Jenkinsfile реализован простой конвейер CI/CD, включающий установку зависимостей и запуск модульных тестов.

В результате Jenkins успешно выполняет задачи автоматической сборки и тестирования, демонстрируя основы DevOps-подхода и работу CI/CD-процессов в контейнерной инфраструктуре.

---

## 5. Использованные источники
* [Официальная документация по настройке и использованию Jenkins.](https://www.jenkins.io/doc/)
* [Описание базового образа Jenkins Controller.](https://hub.docker.com/r/jenkins/jenkins)
* [Документация по образу SSH-агента.](https://hub.docker.com/r/jenkins/ssh-agent)
* [Справочник по синтаксису Jenkinsfile.](https://www.jenkins.io/doc/book/pipeline/syntax/)
* Методические материалы лабораторной работы №4 на Moodle USM и GitHub.
