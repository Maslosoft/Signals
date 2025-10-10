import jetbrains.buildServer.configs.kotlin.*
import jetbrains.buildServer.configs.kotlin.buildSteps.script
import jetbrains.buildServer.configs.kotlin.vcs.VcsRoot

class PhpCi(
    name: String,
    private val root: VcsRoot,
    private val php: String
) : BuildType({
    this.name = name

    params {
        param("env.PHP_IMAGE", "php:${php}-cli")
        param("env.COMPOSER_IMAGE", "composer:2")
        param("env.COMPOSER_CACHE", "%teamcity.agent.work.dir%/composer-cache")
        // Optional: make steps explicitly use the checkout dir
        param("env.WORKDIR", "%teamcity.build.checkoutDir%")
    }

    vcs {
        root(root)
        cleanCheckout = true
    }

    steps {
        script {
            name = "Composer install"
            workingDir = "%env.WORKDIR%"
            scriptContent = """
                set -e
                composer --version
                composer install --no-interaction --prefer-dist
            """.trimIndent()
            dockerImage = "%env.COMPOSER_IMAGE%"
            dockerRunParameters = "-e COMPOSER_CACHE_DIR=/c -v %env.COMPOSER_CACHE%:/c"
        }

        script {
            name = "run-ci"
            workingDir = "%env.WORKDIR%"
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
        exists("docker.server.version")
    }

    artifactRules = """
        tests/_output/coverage => coverage_html
        coverage.clover
        tests/_output/coverage.clover
    """.trimIndent()
})
