const axios = require('axios').default;
const jsonp = require('jsonp');
const querystring = require('querystring');

export function requestVkApiGet(method,
                                params = {},
                                accessToken = 'dc23d677dc23d677dc23d67752dc4dc5f4ddc23dc23d67781f080bc544ea3e49a35d666') {
    return new Promise((resolve, reject) => {
        let paramStr = 'v=5.103&access_token=' + accessToken;
        let q = querystring.encode(params);
        jsonp(`https://api.vk.com/method/${method}?${paramStr}&${q}`,
            null
            , function (err, data) {
                if (err) {
                    reject(err);
                } else {
                    resolve(data);
                }
            });
    })

}

export function requestVkApiPost(method, params = {},
                                 accessToken = 'dc23d677dc23d677dc23d67752dc4dc5f4ddc23dc23d67781f080bc544ea3e49a35d666') {
    let paramStr = 'v=5.103&access_token=' + accessToken;
    return axios.post(`https://api.vk.com/method/${method}?${paramStr}`, {
        params: params
    })
        .then(function (response) {
            return response;
        })
        .catch(function (error) {
            console.log('axioserr ', error);
        });
}

export function requestServerPost(method, params = {}) {
    console.log('params', params);
    return axios.post(process.env.MIX_SITE_URL + `/${method}`,
        params)
        .then(function (response) {
            return response;
        })
        .catch(function (error) {
            console.log('axioserr ', error);
        });
}
