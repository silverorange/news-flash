pipeline {
    agent any
    stages {
        stage('Install Composer Dependencies') {
            steps {
                sh 'rm -rf composer.lock vendor/'
                sh 'composer install'
            }
        }

        stage('Lint') {
            steps {
                sh '''
                    master_sha=$(git rev-parse origin/master)
                    newest_sha=$(git rev-parse HEAD)
                    ./vendor/bin/phpcs \
                    --standard=SilverorangeTransitional \
                    --tab-width=4 \
                    --encoding=utf-8 \
                    --warning-severity=0 \
                    --extensions=php \
                    $(git diff --diff-filter=ACRM --name-only $master_sha...$newest_sha)
                '''
            }
        }
    }
}
