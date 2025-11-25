const refreshedAt = Date.now();

////////////////////////////////////////////////////////////////////////////////
// Handle settings

// Footer
document.getElementById('settings-footer').style.display = 'block';
const contentElement = document.getElementById('content');
const settingsElement = document.getElementById('settings');
const openSettingsButton = document.getElementById('open-settings');
openSettingsButton.addEventListener('click', function () {
    contentElement.style.display = 'none';
    settingsElement.style.display = 'block';

    document.getElementById('close-settings').addEventListener('click', function() {
        settingsElement.style.display = 'none';
        contentElement.style.display = 'flex';
    });
});

// Load settings from localStorage (or initialize them to their default value)

let autoRefresh = Number.parseInt(localStorage.getItem('jka-server-status_auto-refresh'));
if (isNaN(autoRefresh) || autoRefresh < 0) {
    autoRefresh = 0;
}

// Now, let's take care of the UI

// DOM Elements
const autoRefreshSelect = document.getElementById('auto-refresh-select');

// Auto-refresh
autoRefreshSelect.value = autoRefresh;
autoRefreshSelect.addEventListener('change', function () {
    autoRefresh = Number.parseInt(autoRefreshSelect.value);
    if (!isNaN(autoRefresh)) {
        localStorage.setItem('jka-server-status_auto-refresh', autoRefresh);
        const refreshedMinutesAgo = (Date.now() - refreshedAt) / 60000;
        if (autoRefresh > 0 && autoRefresh <= refreshedMinutesAgo) {
            location.reload();
        }
        // See also: updateDuration()
    }
});

////////////////////////////////////////////////////////////////////////////////
// Handle "Updated X minutes ago" info + auto-refresh

// The label hasn't been updated yet
let nbMinutesInFooter = -1;

let updateInterval;
const updatedAtElement = document.getElementById('refreshed-footer');
if (updatedAtElement) {
    updatedAtElement.style.display = 'block';
    updateInterval = setInterval(updateDuration, 1000); // Update the duration every second
    updateDuration(); // Update it now
}

function updateDuration() {
    const nbMinutes = Math.floor((Date.now() - refreshedAt) / 60000);
    if (nbMinutesInFooter >= nbMinutes) { // The footer is up to date
        // Don't update anything
        return;
    }

    nbMinutesInFooter = nbMinutes;

    if (nbMinutes < 1) {
        updatedAtElement.textContent = 'Updated just now';
        return;
    }
    
    if (nbMinutes < 2) {
        updatedAtElement.textContent = 'Updated a minute ago'
        if (autoRefresh === 1) {
            location.reload();
        }
        return;
    }
    
    if (nbMinutes < 60) {
        updatedAtElement.textContent = 'Updated ' + nbMinutes + ' minutes ago';
        if (autoRefresh > 0 && autoRefresh <= nbMinutes) {
            location.reload();
        }
        return;
    }
    
    // More than 1 hour
    updatedAtElement.textContent = 'Updated over an hour ago';
    updatedAtElement.classList.add('red');
    clearInterval(updateInterval); // Stop refreshing the "Updated X minutes ago" message
    if (autoRefresh === 60) {
        location.reload();
    }
}

////////////////////////////////////////////////////////////////////////////////
// Handle "Server info" (raw cvars)
const cvarsElement = document.getElementById('cvars');
const openCvarsButton = document.getElementById('open-cvars');
if (openCvarsButton) {
    openCvarsButton.addEventListener('click', function() {
        contentElement.style.display = 'none';
        cvarsElement.style.display = 'block';
    });
}
const closeCvarsButton = document.getElementById('close-cvars');
if (closeCvarsButton) {
    closeCvarsButton.addEventListener('click', function() {
        cvarsElement.style.display = 'none';
        contentElement.style.display = 'flex';
    });
}
