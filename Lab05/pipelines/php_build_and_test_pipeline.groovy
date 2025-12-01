pipeline {
    agent { label 'php-agent' }

    environment {
        GIT_REPO_URL = 'https://github.com/zzzzzzqwe/automation_and_scripting.git'
        GIT_BRANCH   = 'main'
        PHP_APP_PATH = 'Lab05/web/tests'
    }

    stages {
        stage('Checkout') {
            steps {
                echo "Клонирую репозиторий ${env.GIT_REPO_URL} (${env.GIT_BRANCH})"
                git branch: env.GIT_BRANCH, url: env.GIT_REPO_URL

                echo "Содержимое рабочего каталога после checkout:"
                sh 'pwd && ls -la'
            }
        }

        stage('Install dependencies') {
            steps {
                echo "Пробую установить зависимости через Composer (если есть composer.json)"
                dir(env.PHP_APP_PATH) {
                    sh '''
                        if [ -f "composer.json" ]; then
                          echo "Найден composer.json — выполняю composer install"
                          composer install --no-interaction --no-progress --prefer-dist
                        else
                          echo "composer.json не найден — пропускаю шаг установки зависимостей"
                        fi
                    '''
                }
            }
        }

        stage('Run tests (PHPUnit или tests.php)') {
            steps {
                echo "Запускаю тесты проекта"
                dir(env.PHP_APP_PATH) {
                    sh '''
                        if [ -x "vendor/bin/phpunit" ]; then
                          echo "Запускаю vendor/bin/phpunit"
                          ./vendor/bin/phpunit --testdox
                          exit $?
                        fi

                        if command -v phpunit >/dev/null 2>&1; then
                          echo "Запускаю глобальный phpunit"
                          phpunit --testdox
                          exit $?
                        fi

                        if [ -f "tests.php" ]; then
                          echo "PHPUnit не найден, запускаю tests.php"
                          php tests.php
                          exit $?
                        fi

                        echo "Не найден ни PHPUnit, ни tests.php — нечего запускать"
                        exit 1
                    '''
                }
            }
        }
    }

    post {
        success {
            echo "Сборка и тесты прошли успешно"
        }
        failure {
            echo "Сборка или тесты завершились с ошибкой"
        }
    }
}
