import jetbrains.buildServer.configs.kotlin.*

version = "2025.07.3"

project {
    val repo = vcsRoot(GitVcsRoot {
        id("Repo")
        url = "https://github.com/Maslosoft/Signals.git"
        branch = "refs/heads/main"
        branchSpec = "+:refs/heads/*"
        authMethod = anonymous()
    })

    val phpVersions = listOf("7.4", "8.0", "8.1", "8.2", "8.3", "8.4")

    // Reuse the template for each PHP version
    phpVersions.forEach { v ->
        buildType(PhpCi("CI (PHP $v)", repoId = repo.id!!.value, php = v))
    }
}