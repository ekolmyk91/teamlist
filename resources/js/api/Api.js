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

export const getUsersId = (id) => {
    return axios({
        method: 'get',
        url: `/api/members/${id}`,
        headers: {'Authorization' : 'Bearer ' + api_token},
    })
        .then(response => {
            return response.data;
        })
}

export const getCurrentUser = () => {
	return axios({
		method: 'get',
		url: '/api/user/current',
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

export const getLinks = () => {
    return axios({
        method: 'get',
        url: '/api/links',
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            return response.data;
        })
}

export const getOffTimeTipes = () => {
    return axios({
        method: 'get',
        url: '/api/off_time/types',
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            return response.data;
        })
}

export const getCalendar = (year,month) => {
    return axios({
        method: 'get',
        url: `/api/calendar/${year}/${month}`,
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            return response.data;
        })
}

export const getVacation = (year,month) => {
    return axios({
        method: 'get',
        url: `/api/members/vacation/${year}/${month}`,
        headers: {'Authorization' : 'Bearer ' + api_token}
    })
        .then(response => {
            let arr = []
            response.data.users.map(user => {
                Object.keys(user.off_time).map(key => {
                    user.off_time[key].map(pos => {
                        arr.push({
                            user_id: user.id,
                            title:`${user.fullname} - ${pos.type}`,
                            start: new Date(pos.start_day),
                            end: new Date(pos.end_day),
                        })
                    })
                })
            })
            return arr
        })
}

export const postRequest = (data) => {
    return axios({
        method: 'post',
        url: '/api/off_time_request',
        headers: {'Authorization' : 'Bearer ' + api_token},
        data: data

    })
        .then(response => {
            return response.data;
        })
}
