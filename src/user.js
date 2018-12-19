let user = {
    username: undefined,
    isAdmin: false,
}

export const setUser = (username, isAdmin) => {
    user.username = username;
    user.isAdmin = isAdmin;
}

export const getCurrentUser = () => user
