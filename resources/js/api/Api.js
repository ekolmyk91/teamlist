import axios from 'axios'
import { ApiKey } from './config'

export const getUsers = () => {
    return axios({
        method: 'get',
        url: '/api/members',
        headers: {'Api-Key': ApiKey},
    })
        .then(responce => {
            return responce.data;
        })
}

export const getDepartments = () => {
    return axios({
        method: 'get',
        url: '/api/departments',
        headers: {'Api-Key': ApiKey},
    })
        .then(responce => {
            return responce.data;
        })
}

export const getPositions = () => {
    return axios({
        method: 'get',
        url: '/api/positions',
        headers: {'Api-Key': ApiKey},
    })
        .then(responce => {
            return responce.data;
        })
}
