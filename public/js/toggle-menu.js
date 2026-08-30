
document.addEventListener('DOMContentLoaded', () => {

    const closeSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="lucid-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-chevron-left-icon lucide-square-chevron-left"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m14 16-4-4 4-4"/></svg>';
    const openSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="lucid-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-chevron-right-icon lucide-square-chevron-right"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m10 8 4 4-4 4"/></svg>';

    const buttonToggle = document.createElement('button');
    buttonToggle.className = 'admin-btn transparent-btn';
    buttonToggle.type = 'button';
    buttonToggle.style.cssText = 'position: fixed; top: 95px; left: 267px; z-index: 999; transition: left 200ms ease-in-out 0s;';
    buttonToggle.innerHTML = closeSvg;

    buttonToggle.addEventListener('click', event => {
        const menu = document.querySelector('.admin-sidebar');
        if (menu.classList.contains('closed')) {
            buttonToggle.innerHTML = closeSvg;
            menu.style.width = '';
            menu.style.transform = 'scaleX(1)';
            menu.classList.remove('closed');
            buttonToggle.style.left = '267px';
        } else {
            buttonToggle.innerHTML = openSvg;
            menu.style.width = '0';
            menu.style.transform = 'scaleX(0.001)';
            menu.classList.add('closed');
            buttonToggle.style.left = '8px';
        }
    });

    const menu = document.querySelector('.admin-sidebar');
    menu.style.transition = 'width 170ms ease-in-out 0s, transform 200ms ease-in-out 0s';

    document.body.append(buttonToggle);
});
