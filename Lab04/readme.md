# Лабораторная работа 4
# Студент: Gachayev Dmitrii, I2302
# Дата выполнения: 11.11.2025
# Цель
Научиться настраивать Jenkins для автоматизации задач DevOps, включая создание и управление конвейерами CI/CD.
# Описание проекта
Данный проект представляет собой практическую лабораторную работу по автоматизации задач DevOps с использованием Jenkins. В рамках работы был развёрнут CI/CD-конвейер, состоящий из двух контейнеров - Jenkins Controller и SSH Agent. Контроллер отвечает за управление заданиями Jenkins и выполнением конвейеров, а SSH агент используется для удалённого исполнения этапов пайплайна, включая запуск модульных тестов PHP.

---

## Подготовка контроллера Jenkins
1. Создаю следующий `docker-compose.yml`:
```
services:
  jenkins-controller:
    image: jenkins/jenkins:lts
    container_name: jenkins-controller
    ports:
      - "8080:8080"
      - "50000:50000"
    volumes:
      - jenkins_home:/var/jenkins_home
    networks:
      - jenkins-network

volumes:
  jenkins_home:
  jenkins_agent_volume:

networks:
  jenkins-network:
    driver: bridge
```
и запускаю его:
```bash
docker compose up -d
```

2. Перехожу на localhost и настраиваю `Jenkins`:

![image](screenshots/Screenshot_1.png)

Получаю пароль:
```bash
docker exec -it jenkins-controller 
cat /var/jenkins_home/secrets/initialAdminPassword
```

Выбираю рекоммендованную установку  завершаю настройку:

![image](screenshots/Screenshot_2.png)

## Подготовка SSH агента

1. Создаю папку `secrets` и добавляю туда ssh ключи:
```bash
mkdir secrets
cd secrets
ssh-keygen -f jenkins_agent_ssh_key
```

2. Создаю  `Dockerfile` для SSH агента с следующим содержимым:
```dockerfile
FROM jenkins/ssh-agent

# install PHP-CLI
RUN apt-get update && apt-get install -y php-cli
```

3. Добавляю в `docker-compose.yml` следующее:
```
  ssh-agent:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: ssh-agent
    environment:
      - JENKINS_AGENT_SSH_PUBKEY=${JENKINS_AGENT_SSH_PUBKEY}
    volumes:
      - jenkins_agent_volume:/home/jenkins/agent
    depends_on:
      - jenkins-controller
    networks:
      - jenkins-network
```
4. Создаю `.env` файл и помещаю в него публичный ssh ключ.

## Подключение SSH агента к Jenkins
1. Устанавливаю плагин `SSH Agent`:

![image](screenshots/Screenshot_3.png)

2. Перехожу в Manage Jenkins > Manage Credentials, добавляю Credentials:

![image](screenshots/Screenshot_4.png)

Выбираю `SSH Username with private key`, добавляю приватный ключ и ввожу остальные поля:

![image](screenshots/Screenshot_5.png)

3. Добляю новый узел агента `Jenkins`:

![image](screenshots/Screenshot_6.png)

![image](screenshots/Screenshot_7.png)

![image](screenshots/Screenshot_8.png)

## Создание конвейера Jenkins для автоматизации задач DevOps

1. Копирую сайт из 8 лабораторной работы по контейнеризации в репозиторий.

2. Добавляю строку с запуском файла с тестами в `Jenkinsfile`:
```
pipeline {
    agent {
        label 'php-agent'
    }
    
    stages {        
        stage('Install Dependencies') {
            steps {
                // Подготовка проекта (установка зависимостей, если необходимо)
                echo 'Подготовка проекта...'
                // Добавьте здесь команды специфичные для вашего проекта
            }
        }
        
        stage('Test') {
            steps {
                // Запуск тестов
                echo 'Запуск тестов...'
                sh 'php web/tests/tests.php'
            }
        }
    }
    
    post {
        always {
            echo 'Конвейер завершен.'
        }
        success {
            echo 'Все этапы прошли успешно!'
        }
        failure {
            echo 'Обнаружены ошибки в конвейере.'
        }
    }
}
```

3. Создаю pipeline со следующей конфигурацией:

![image](screenshots/Screenshot_9.png)

![image](screenshots/Screenshot_10.png)

Затем запускаю Build, Jenkins файл будет подтягиваться из репозитория.

# Контрольные вопросы
> Какие преимущества использования Jenkins для автоматизации задач DevOps?

Обеспечивает автоматизацию тестирования и сборки, ускоряет выпуск обновлений, даёт повторяемость процессов, удобную оркестрацию задач и интеграцию со множеством инструментов DevOps

> Какие еще бывают агенты Jenkins?

SSH-агенты, JNLP-агенты, Docker-агенты, Kubernetes-агенты, Windows-агенты и локальные агенты на контроллере.

> Какие проблемы вы столкнулись при настройке Jenkins и как вы их решили?

Cтолкнулся с тем, что контейнеры конфликтовали по имени, агент не запускался из-за некорректного базового Dockerfile и неправильного SSH-ключа. Решил удалением старых контейнеров и сменой конфигурации.

# Вывод
В ходе выполнения лабораторной работы был развернут Jenkins в Docker-среде, настроен SSH-агент и создан рабочий конвейер для автоматизации тестов PHP-проекта. Удалось на практике понять структуру Jenkins, процесс подключения удалённых агентов и использование Jenkinsfile

