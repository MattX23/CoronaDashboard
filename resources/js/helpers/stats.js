export const ACTIVE_BTN_CLASS = 'btn-primary';
export const SUCCESS_BTN_CLASS = 'btn-success';
export const SECONDARY_BTN_CLASS = 'btn-dark';

const STATS_BTNS = [
    'recovered',
    'deaths',
    'currentlyInfected',
    'totalInfected',
];

export function setMainStats(selected = null) {
    document.querySelectorAll('.main-stats')
        .forEach(el => {
            el.classList.remove(SUCCESS_BTN_CLASS);
            if (!el.classList.contains(SECONDARY_BTN_CLASS)) el.classList.add(SECONDARY_BTN_CLASS);
        });

    if (selected) {
        this.toggleClass(
            document.querySelector(`[data-${selected}]`),
            SECONDARY_BTN_CLASS,
            SUCCESS_BTN_CLASS
        );
        this.setStatsText(selected)
    } else {
        let set = false;

        [...STATS_BTNS].forEach(btn => {
            if (this.current[btn] !== null) {
                if (!set) {
                    const activeBtn = document.querySelector(`[data-${btn}]`);
                    if (activeBtn) {
                        this.toggleClass(activeBtn, SECONDARY_BTN_CLASS, SUCCESS_BTN_CLASS);
                        this.setStatsText(btn);
                        set = true;
                    }
                }
            }
        });
    }
}

export function toggleClass(element, classToRemove, classToAdd) {
    element.classList.remove(classToRemove);
    element.classList.add(classToAdd);
}

export function setStatsText(stat) {
    const alert = document.getElementById('stat-text');
    alert.innerText = '';
    const countryPrefix = this.countryName !== 'World Wide' ? 'in ' : '';

    switch (stat) {
        case 'recovered':
            alert.innerText =
                `Of the ${this.formatNumber(this.current.totalCases)} confirmed covid-19 cases ${countryPrefix}${this.countryName}, ${this.getPercentage(this.current.totalCases, this.current.recovered)}% (${this.formatNumber(this.current.recovered)}) have now recovered.`;
            break;
        case 'deaths':
            alert.innerText =
                `Of the ${this.formatNumber(this.current.totalCases)} people infected with covid-19 ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.totalDeaths)} (${this.getPercentage(this.current.totalCases, this.current.totalDeaths)}%) have died.
                            (${this.getPercentage(this.current.population, this.current.totalDeaths)}% of the total population)`;
            break;
        case 'currentlyInfected':
            alert.innerText =
                `Of the ${this.formatNumber(this.current.totalCases)} covid-19 cases ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.activeCases)} are still active.
                            (${this.getPercentage(this.current.population, this.current.activeCases)}% of the total population)`;
            break;
        case 'totalInfected':
            alert.innerText =
                `Of the ${this.formatNumber(this.current.population)} people living ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.totalCases)} have been infected.
                            (${this.getPercentage(this.current.population, this.current.totalCases)}% of the total population)`;
            break;
        default:
            alert.innerText = `No additional information could be found for ${this.countryName}.`;
    }
}