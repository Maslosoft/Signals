import jetbrains.buildServer.configs.kotlin.*
import jetbrains.buildServer.configs.kotlin.vcs.DslContext
import jetbrains.buildServer.configs.kotlin.buildSteps.script
import jetbrains.buildServer.configs.kotlin.triggers.vcs

version = "2025.07"

project {
    buildType(Php85)
}

object Php85 : BuildType({
    name = "PHP 8.5 checks"
    vcs { root(DslContext.settingsRoot) }
    steps {
        script {
            name = "Install and check"
            scriptContent = """
                set -eu
                php -r 'exit(PHP_VERSION_ID >= 80500 ? 0 : 1);'
                composer validate --strict
                composer install --no-interaction --prefer-dist
                composer check
                composer audit
            """.trimIndent()
        }
    }
    triggers { vcs {} }
    artifactRules = "tests/_output/** => test-results"
})
