let competition = {
    adminUsername: undefined,
}

window.setCompetitionAdmin = username => competition.adminUsername = username

export const getCurrentCompetition = () => competition
