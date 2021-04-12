import axios from 'axios'

export const getUsers = () => {
    return axios({
        method: 'get',
        url: '/api/members',
        headers: {"Authorization" : `Bearer ${api_token}`},
    })
        .then(response => {
            return response.data;
        })
}

export const getDepartments = () => {
    return axios({
        method: 'get',
        url: '/api/departments',
    })
        .then(response => {
            return response.data;
        })
}

export const getPositions = () => {
    return axios({
        method: 'get',
        url: '/api/positions',
    })
        .then(response => {
            return response.data;
        })
}

export const getBirthExpPeople = monthID => {
    return axios({
        method: 'get',
        url: '/api/members?month='+ (+(monthID) + 1),
    })
        .then(response => {
            return response.data;
        })
}
