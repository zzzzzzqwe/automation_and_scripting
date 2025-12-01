pipeline {
    agent { label 'ansible-agent' }

    environment {
        GIT_REPO_URL = "https://github.com/zzzzzzqwe/automation_and_scripting.git"
        GIT_BRANCH   = "main"
        PLAYBOOK_PATH = "Lab05/ansible/setup_test_server.yml"
        INVENTORY_PATH = "/ansible/inventory/hosts"
    }

    stages {
        stage('Checkout playbook') {
            steps {
                echo "Клонирую репозиторий с Ansible playbook"
                git branch: env.GIT_BRANCH, url: env.GIT_REPO_URL

                echo "Содержимое каталога:"
                sh "pwd && ls -la"
            }
        }

        stage('Run Ansible playbook') {
            steps {
                echo "Запускаю Ansible playbook ${env.PLAYBOOK_PATH}"
                sh """
                    ansible-playbook ${env.PLAYBOOK_PATH} -i ${env.INVENTORY_PATH}
                """
            }
        }
    }

    post {
        success { echo "Настройка тестового сервера успешно выполнена" }
        failure { echo "Ошибка при настройке тестового сервера" }
    }
}
