import axios from 'axios'
import { ApiKey } from './config'

export const getUsers = () => {
    return axios({
        method: 'get',
        url: '/api/members',
        headers: {'Api-Key': ApiKey},
    })
        .then(response => {
            return response.data;
        })
}

export const getDepartments = () => {
    return axios({
        method: 'get',
        url: '/api/departments',
        headers: {'Api-Key': ApiKey},
    })
        .then(response => {
            return response.data;
        })
}

export const getPositions = () => {
    return axios({
        method: 'get',
        url: '/api/positions',
        headers: {'Api-Key': ApiKey},
    })
        .then(response => {
            return response.data;
        })
}

export const getBirthExpPeople = monthID => {
    return axios({
        method: 'get',
        url: '/api/members?month='+ (+(monthID) + 1),
        headers: {'Api-Key': ApiKey},
    })
        .then(response => {
            return response.data;
        })
}
