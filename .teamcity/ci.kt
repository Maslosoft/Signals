import jetbrains.buildServer.configs.kotlin.*
import jetbrains.buildServer.configs.kotlin.buildSteps.script
import jetbrains.buildServer.configs.kotlin.vcs.GitVcsRoot
import jetbrains.buildServer.configs.kotlin.vcs.VcsRoot

class PhpCi(
    name: String,
    repoId: String,
    php: String
) : BuildType({
    this.name = name

    // Map a Travis-like variable for your script
    params {
        param("env.PHP_VERSION", php)
        // pick your images; switch to a custom PHP image with Xdebug if you need coverage
        param("env.PHP_IMAGE", "php:${php}-cli")
        param("env.COMPOSER_IMAGE", "composer:2")
        param("env.COMPOSER_CACHE", "%teamcity.agent.work.dir%/composer-cache")
    }

    vcs { root(DslContext.projectId.toString() + "_" + repoId) }

    steps {
        // Composer install (Docker)
        script {
            name = "Composer install"
            scriptContent = """
                set -e
                composer --version
                composer install --no-interaction --prefer-dist
            """.trimIndent()
            dockerImage = "%env.COMPOSER_IMAGE%"
            dockerRunParameters = "-e COMPOSER_CACHE_DIR=/c -v %env.COMPOSER_CACHE%:/c"
        }

        // Ci runner (Docker)
        script {
            name = "run-ci"
            scriptContent = """
                set -e
                php -v
                bash run-ci
            """.trimIndent()
            dockerImage = "%env.PHP_IMAGE%"
            dockerRunParameters = "-e COMPOSER_CACHE_DIR=/c -v %env.COMPOSER_CACHE%:/c"
        }
    }

    triggers { vcs {} }

    requirements {
        // Make sure the agent has Docker available
        exists("docker.server.version")
    }

    // Publish coverage if present
    artifactRules = """
        tests/_output/coverage => coverage_html
        coverage.clover
        tests/_output/coverage.clover
    """.trimIndent()
})
