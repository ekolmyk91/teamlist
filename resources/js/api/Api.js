import axios from 'axios'

export const getUsers = () => {
    return axios.get('/api/members').then(responce => {
        return responce.data;
    })
}