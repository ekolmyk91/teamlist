import axios from 'axios'

export const getUsers = () => {
    return axios({
        method: 'get',
        url: '/api/members',
        headers: {'Authorization' : 'Bearer MpyfMEuc5vO4PKD6o9IizfUTySpW0TpoHaqbNUIdhVkazMjpjDw5woepSOJZ'},
    })
        .then(response => {
            return response.data;
        })
}

export const getDepartments = () => {
    return axios({
        method: 'get',
        url: '/api/departments',
        headers: {'Authorization' : 'Bearer MpyfMEuc5vO4PKD6o9IizfUTySpW0TpoHaqbNUIdhVkazMjpjDw5woepSOJZ'}
    })
        .then(response => {
            return response.data;
        })
}

export const getPositions = () => {
    return axios({
        method: 'get',
        url: '/api/positions',
        headers: {'Authorization' : 'Bearer MpyfMEuc5vO4PKD6o9IizfUTySpW0TpoHaqbNUIdhVkazMjpjDw5woepSOJZ'}
    })
        .then(response => {
            return response.data;
        })
}

export const getBirthExpPeople = monthID => {
    return axios({
        method: 'get',
        url: '/api/members?month='+ (+(monthID) + 1),
        headers: {'Authorization' : 'Bearer MpyfMEuc5vO4PKD6o9IizfUTySpW0TpoHaqbNUIdhVkazMjpjDw5woepSOJZ'}
    })
        .then(response => {
            return response.data;
        })
}
