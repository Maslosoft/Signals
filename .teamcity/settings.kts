import jetbrains.buildServer.configs.kotlin.*
import jetbrains.buildServer.configs.kotlin.vcs.*

version = "2025.07"

project {
    val repo = GitVcsRoot {
        id("Maslosoft_Signals_VCS")
        name = "Maslosoft/Signals"
        url = "https://github.com/Maslosoft/Signals.git"
        branch = "refs/heads/main"
        branchSpec = "+:refs/heads/*"
        authMethod = anonymous()
    }
    vcsRoot(repo)

    val phpVersions = listOf("7.4", "8.0", "8.1", "8.2", "8.3", "8.4")
    phpVersions.forEach { v ->
        buildType(PhpCi("CI (PHP $v)", root = repo, php = v))
    }
}
