//Changes the colour of the button reffering to the active page
function addActiveClassToSidebarLinks() {
    //Gets the end of the URL, which is the name of the active page
    const currentUrl = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll('.sidebar-button a');
    
    //Loops through links of all sidebar buttons
    sidebarLinks.forEach(sidebarLink => {
        const linkHref = sidebarLink.getAttribute('href');

        //Compares sidebar button link to active page link
        if (currentUrl === linkHref) {
            //Adds active label so we can refer to the active button in css
            sidebarLink.classList.add('active');
        }
    });
}

//Prevents page refreshing when the button reffering to the active page is clicked
function preventPageRefresh(event) {
    const links = document.querySelectorAll('.stayOnPageLink');

    links.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            history.pushState({}, '', window.location.href);
        });
    });
}

//Limit text length in profile box
function truncateProfileBoxText(selector, maxLength) {
    const elements = document.querySelectorAll(selector);
    elements.forEach(element => {
        if (element.textContent.length > maxLength) {
            element.textContent = element.textContent.slice(0, maxLength) + '...';
        }
    });
}

//Apply truncation
truncateProfileBoxText('.name', 30);
truncateProfileBoxText('.role', 30);

//Runs function when all Document Object Model's have loaded
document.addEventListener('DOMContentLoaded', truncateProfileBoxText);
document.addEventListener('DOMContentLoaded', addActiveClassToSidebarLinks);