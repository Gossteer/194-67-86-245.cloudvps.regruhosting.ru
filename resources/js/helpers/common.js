export function shortCurrencyTranslate(currency) {
    let currenciesShortTranslate = {
        'rub': 'руб',
    };

    return currenciesShortTranslate[currency]?currenciesShortTranslate[currency]:currency;
}

export function isMobile() {
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return true
    } else {
        return false
    }
}
