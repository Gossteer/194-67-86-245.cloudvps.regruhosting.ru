const USER_ROLE_ADMIN = 'admin';

const DAYS_OF_WEEK = [
    'вс',
    'пн',
    'вт',
    'ср',
    'чт',
    'пт',
    'сб'
];

const COST_MIN = 100;
const COST_MAX = 500000;

const SEASONS_TRANSCRIPTION = {
    'all': 'любое',
    'winter': 'зима',
    'spring': 'весна',
    'summer': 'лето',
    'fall': 'осень',
};
const MONTHS_TRANSCRIPTION = {
    0: 'любой',
    1: 'январь', 2: 'февраль', 3: 'март', 4: 'апрель',
    5: 'май', 6: 'июнь', 7: 'июль', 8: 'август',
    9: 'сентябрь', 10: 'октябрь', 11: 'ноябрь', 12: 'декабрь',
};

const CONTINENTS = {
    'random': 'любая',
    'E': 'Европа',
    'F': 'Африка',
    'O': 'Океания',
    'A': 'Азия',
    'S': 'Южная Америка',
    'N': 'Северная Америка',
};

const ADMIN_ID = '382960669';
const URL_PARAMS = new URLSearchParams(window.location.search);
const USER_ID = URL_PARAMS.get('vk_user_id');
const APP_ID = URL_PARAMS.get('vk_app_id');
const SIGN = URL_PARAMS.get('sign');
const USER_ROLE = URL_PARAMS.get('vk_viewer_group_role');

console.log('APP_ID ', APP_ID);

export const constants = {
    USER_ROLE_ADMIN: USER_ROLE_ADMIN,

    ADMIN_ID: ADMIN_ID,
    URL_PARAMS: URL_PARAMS,
    USER_ID: USER_ID,
    APP_ID: APP_ID,
    SIGN: SIGN,
    USER_ROLE: USER_ROLE,

    SEASONS_TRANSCRIPTION: SEASONS_TRANSCRIPTION,
    MONTHS_TRANSCRIPTION: MONTHS_TRANSCRIPTION,

    DAYS_OF_WEEK: DAYS_OF_WEEK,

    CONTINENTS: CONTINENTS,

    COST_MIN: COST_MIN,
    COST_MAX: COST_MAX,
};
