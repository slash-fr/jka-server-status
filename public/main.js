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
        contentElement.style.display = 'block';
    });
});

// DOM Elements
const autoRefreshSelect = document.getElementById('auto-refresh-select');
const backgroundDiv = document.createElement('div');
backgroundDiv.id = 'background-image';
document.body.prepend(backgroundDiv);
const currentBackgroundImage = document.getElementById('current-background-image').value;
const defaultBackgroundImage = document.getElementById('default-background-image').value;
const backgroundImageSelect = document.getElementById('background-image-select');
const backgroundImageBlurSlider = document.getElementById('background-image-blur-slider');
const backgroundImageOpacitySlider = document.getElementById('background-image-opacity-slider');
const backgroundColorInput = document.getElementById('background-color-input');

// Auto-refresh
let autoRefresh = Number.parseInt(localStorage.getItem('jka-server-status_auto-refresh'));
if (isNaN(autoRefresh) || autoRefresh < 0) {
    autoRefresh = 0;
}
autoRefreshSelect.value = autoRefresh;
autoRefreshSelect.addEventListener('change', function () {
    autoRefresh = Number.parseInt(autoRefreshSelect.value);
    if (!isNaN(autoRefresh)) {
        localStorage.setItem('jka-server-status_auto-refresh', autoRefresh);
        const refreshedMinutes = Math.round((Date.now() - refreshedAt) / 1000) / 60;
        if (autoRefresh > 0 && autoRefresh <= refreshedMinutes) {
            autoRefresh = 0; // Don't call location.reload() multiple times, if the response takes more than 1 second.
            location.reload();
        }
        // See also: updateDuration()
    }
});

// Background image
let backgroundImage = localStorage.getItem('jka-server-status_background-image');
if (backgroundImage === 'disabled') {
    backgroundImageSelect.value = 'disabled';
} else if (backgroundImage === 'always-default') {
    backgroundImageSelect.value = 'always-default';
} else { // if (backgroundImage === 'map-dependent') {
    backgroundImageSelect.value = 'map-dependent';
}
function updateBackgroundImage() {
    if (backgroundImageSelect.value === 'disabled') {
        localStorage.setItem('jka-server-status_background-image', 'disabled');
        backgroundDiv.style.display = 'none';
        disableImageTweaks();
    } else if (backgroundImageSelect.value === 'always-default') {
        localStorage.setItem('jka-server-status_background-image', 'always-default');
        backgroundDiv.style.backgroundImage = 'url(' + defaultBackgroundImage + ')';
        backgroundDiv.style.display = 'block';
        enableImageTweaks();
    } else { //if (backgroundImageSelect.value === 'map-dependent') {
        localStorage.setItem('jka-server-status_background-image', 'map-dependent');
        backgroundDiv.style.backgroundImage = 'url(' + currentBackgroundImage + ')';
        backgroundDiv.style.display = 'block';
        enableImageTweaks();
    }
}
updateBackgroundImage(); // Apply background image settings on load
backgroundImageSelect.addEventListener('change', updateBackgroundImage);

// Background image blur
let backgroundImageBlur = Number.parseInt(localStorage.getItem('jka-server-status_background-image-blur'));
if (isNaN(backgroundImageBlur) || backgroundImageBlur < 0 || backgroundImageBlur > 10) {
    backgroundImageBlur = 5; // Default: 5px
}
backgroundImageBlurSlider.value = backgroundImageBlur;
function updateBlur() {
    if (
        !isNaN(backgroundImageBlurSlider.value)
        && backgroundImageBlurSlider.value >= 0 && backgroundImageBlurSlider.value <= 10
    ) {
        backgroundDiv.style.filter = 'blur(' + backgroundImageBlurSlider.value + 'px)';
        document.getElementById('background-image-blur-radius').textContent = backgroundImageBlurSlider.value + ' px';
        localStorage.setItem('jka-server-status_background-image-blur', backgroundImageBlurSlider.value);
    }
}
updateBlur(); // Apply blur settings on load
backgroundImageBlurSlider.addEventListener('input', updateBlur);

// Background image opacity
let backgroundImageOpacity = Number.parseInt(localStorage.getItem('jka-server-status_background-image-opacity'));
if (isNaN(backgroundImageOpacity) || backgroundImageOpacity < 0 || backgroundImageOpacity > 100) {
    backgroundImageOpacity = 50; // Default: 50%
}
backgroundImageOpacitySlider.value = backgroundImageOpacity;
function updateOpacity() {
    if (
        !isNaN(backgroundImageOpacitySlider.value)
        && backgroundImageOpacitySlider.value >= 0 && backgroundImageOpacitySlider.value <= 100
    ) {
        backgroundDiv.style.opacity = backgroundImageOpacitySlider.value + '%';
        document.getElementById('background-image-opacity-percentage').textContent = backgroundImageOpacitySlider.value + '%';
        localStorage.setItem('jka-server-status_background-image-opacity', backgroundImageOpacitySlider.value);
    }
}
updateOpacity(); // Apply opacity settings on load
backgroundImageOpacitySlider.addEventListener('input', updateOpacity);

function disableImageTweaks() {
    document.querySelectorAll('.background-image-tweak').forEach(function(element) {
        element.classList.add('disabled');
    });
    backgroundImageBlurSlider.setAttribute('disabled', '');
    backgroundImageOpacitySlider.setAttribute('disabled', '');
}

function enableImageTweaks() {
    document.querySelectorAll('.background-image-tweak').forEach(function(element) {
        element.classList.remove('disabled');
    });
    backgroundImageBlurSlider.removeAttribute('disabled');
    backgroundImageOpacitySlider.removeAttribute('disabled');
}

// Background color
let backgroundColor = localStorage.getItem('jka-server-status_background-color');
if (backgroundColor === null) {
    backgroundColor = '#000000';
}
backgroundColorInput.value = backgroundColor;
function updateBackgroundColor() {
    document.body.style.backgroundColor = backgroundColorInput.value;
    localStorage.setItem('jka-server-status_background-color', backgroundColorInput.value);
}
updateBackgroundColor(); // Apply background color on load
backgroundColorInput.addEventListener('change', function() {
    updateBackgroundColor();
});


////////////////////////////////////////////////////////////////////////////////
// Handle "Refreshed X minutes ago" info + auto-refresh

let updateInterval;
const updatedAtElement = document.getElementById('refreshed-footer');
if (updatedAtElement) {
    updatedAtElement.style.display = 'block';
    updateInterval = setInterval(updateDuration, 1000); // Update the duration every second
    updateDuration(); // Update it now
}

function updateDuration() {
    const nbSeconds = Math.round((Date.now() - refreshedAt) / 1000);
    if (nbSeconds < 60) {
        if (nbSeconds == 1) {
            updatedAtElement.textContent = 'Refreshed 1 second ago';
        } else {
            updatedAtElement.textContent = 'Refreshed ' + nbSeconds + ' seconds ago';
        }
    } else if (nbSeconds < 120) {
        updatedAtElement.textContent = 'Refreshed a minute ago'
        if (autoRefresh === 1) {
            autoRefresh = 0; // Don't call location.reload() multiple times, if the response takes more than 1 second.
            location.reload();
        }
    } else if (nbSeconds < 3600) {
        const nbMinutes = Math.floor(nbSeconds / 60);
        updatedAtElement.textContent = 'Refreshed ' + nbMinutes + ' minutes ago';
        if (autoRefresh > 0 && autoRefresh <= nbMinutes) {
            autoRefresh = 0; // Don't call location.reload() multiple times, if the response takes more than 1 second.
            location.reload();
        }
    } else { // More than 1 hour?
        updatedAtElement.textContent = 'Refreshed over an hour ago';
        updatedAtElement.classList.add('red');
        clearInterval(updateInterval);
        if (autoRefresh === 60) {
            autoRefresh = 0; // Don't call location.reload() multiple times, if the response takes more than 1 second.
            location.reload();
        }
    }
}
