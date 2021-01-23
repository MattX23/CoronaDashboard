export function getSymbol(num) {
    if (num > 0) {
        return '+';
    }
}

export function getPercentage(x, y) {
    return (y / x * 100).toFixed(2);
}

export function getDifference(x, y) {
    return x - y;
}

export function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",").replace('-', '- ');
}