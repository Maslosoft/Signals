// .teamcity/settings.kts
import jetbrains.buildServer.configs.kotlin.*
import jetbrains.buildServer.configs.kotlin.vcs.DslContext
import jetbrains.buildServer.configs.kotlin.buildSteps.script

version = "2025.07"

project {
    buildType(Sanity)
}

object Sanity : BuildType({
    name = "Sanity"

    vcs { root(DslContext.settingsRoot) }  // no custom VCS root
    steps {
        script {
            name = "Echo"
            scriptContent = "echo hello"
        }
    }
})
