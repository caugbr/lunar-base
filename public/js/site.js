
document.addEventListener('DOMContentLoaded', () => {
    const lightThemeLink = document.querySelector('.switch-theme a.theme-light');
    const darkThemeLink = document.querySelector('.switch-theme a.theme-dark');
    lightThemeLink.addEventListener('click', event => {
        event.preventDefault();
        document.body.dataset.theme = 'light';
        const widget = document.querySelector('div#lunar-widget .lunar-widget');
        if (widget) {
            widget.dataset.theme = 'light';
        }
        localStorage.setItem('savedTheme', 'light');
    });
    darkThemeLink.addEventListener('click', event => {
        event.preventDefault();
        document.body.dataset.theme = 'dark';
        const widget = document.querySelector('div#lunar-widget .lunar-widget');
        if (widget) {
            widget.dataset.theme = 'dark';
        }
        localStorage.setItem('savedTheme', 'dark');
    });
});
