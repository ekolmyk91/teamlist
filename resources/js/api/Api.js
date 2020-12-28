import axios from 'axios'
import { API_KEY } from './config'

export const getUsers = () => {
    return axios({
        method: 'get',
        url: '/api/members',
        headers: {'API_KEY': API_KEY},
    })
        .then(responce => {
            return responce.data;
        })
}

export const getDepartments = () => {
    return axios({
        method: 'get',
        url: '/api/departments',
        headers: {'API_KEY': API_KEY},
    })
        .then(responce => {
            return responce.data;
        })
}

export const getPositions = () => {
    return axios({
        method: 'get',
        url: '/api/positions',
        headers: {'API_KEY': API_KEY},
    })
        .then(responce => {
            return responce.data;
        })
}
