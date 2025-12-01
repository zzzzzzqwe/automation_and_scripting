pipeline {
    agent { label 'ansible-agent' }

    environment {
        GIT_REPO_URL = "https://github.com/zzzzzzqwe/automation_and_scripting.git"
        GIT_BRANCH   = "main"
        PROJECT_PATH = "Lab05/web"
        INVENTORY_PATH = "/ansible/inventory/hosts"
        PLAYBOOK_DEPLOY = "/ansible/deploy_php.yml"
    }

    stages {
        stage('Checkout PHP project') {
            steps {
                echo "Клонирую репозиторий PHP проекта"
                git branch: env.GIT_BRANCH, url: env.GIT_REPO_URL

                echo "Содержимое каталога:"
                sh "pwd && ls -la"
            }
        }

        stage('Prepare Project Directory') {
            steps {
                echo "Копирую PHP проект во временную директорию"
                sh """
                    rm -rf /ansible/project
                    mkdir -p /ansible/project
                    cp -r ${env.PROJECT_PATH}/* /ansible/project/
                """
            }
        }

        stage('Deploy to Test Server') {
            steps {
                echo "Запускаю Ansible playbook для деплоя"
                sh """
                    ansible-playbook ${env.PLAYBOOK_DEPLOY} -i ${env.INVENTORY_PATH}
                """
            }
        }
    }

    post {
        success { echo "PHP проект успешно развернут на тестовом сервере" }
        failure { echo "Ошибка при деплое PHP проекта" }
    }
}