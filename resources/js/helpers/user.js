export async function getCityAviasalesInfo(countrySrc) {
    let re = /^[а-яА-ЯЁё -]{1,99}$/i;
    let found = countrySrc.match(re);
    if (!found) {
        countrySrc = 'Москва';
    }
    if (!countrySrc) {
        return null;
    }
    let arrayOfStrings = countrySrc.split('/');
    if (arrayOfStrings.length > 2) {
        countrySrc = arrayOfStrings.shift();
    }
    const url = 'https://autocomplete.travelpayouts.com/places2?term=' + countrySrc + '&locale=ru';
    let resp = await fetch(url, {
        method: 'GET'
    });
    const countryIATA = await resp.json();
    if (Array.isArray(countryIATA)) {
        return countryIATA.shift();
    } else {
        return null;
    }
}

export async function getCountryCode(countrySrc) {
    let res = await getCityAviasalesInfo(countrySrc);
    return res ? res.country_code : '';
}

export async function getCityCode(citySrc) {
    let res = await getCityAviasalesInfo(citySrc);
    return res ? res.code : '';
}
