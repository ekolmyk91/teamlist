import axios from 'axios'

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

const api_token = getCookie('tml-cookie');

export const getUsers = () => {
    return axios({
        method: 'get',
        url: '/api/members',
        headers: {'Authorization' : 'Bearer ' + api_token},
    })
        .then(response => {
            return response.data;
        })
}

export const getDepartments = () => {
    return axios({
        method: 'get',
        url: '/api/departments',
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            return response.data;
        })
}

export const getPositions = () => {
    return axios({
        method: 'get',
        url: '/api/positions',
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            return response.data;
        })
}

export const getBirthExpPeople = monthID => {
    return axios({
        method: 'get',
        url: '/api/members?month='+ (+(monthID) + 1),
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            return response.data;
        })
}
