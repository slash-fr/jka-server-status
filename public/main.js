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

let backgroundImage = localStorage.getItem('jka-server-status_background-image');
if (['disabled', 'always-default', 'map-dependent'].includes(backgroundImage) === false) {
    backgroundImage = 'map-dependent';
}

let autoOrCustomBlurAndOpacity = localStorage.getItem('jka-server-status_background-image-blur-and-opacity');
if (autoOrCustomBlurAndOpacity !== 'auto' && autoOrCustomBlurAndOpacity !== 'custom') {
    autoOrCustomBlurAndOpacity = 'auto';
}

let backgroundImageBlur = Number.parseInt(localStorage.getItem('jka-server-status_background-image-blur'));
if (isNaN(backgroundImageBlur) || backgroundImageBlur < 0 || backgroundImageBlur > 10) {
    backgroundImageBlur = 5; // Default: 5px
}

let backgroundImageOpacity = Number.parseInt(localStorage.getItem('jka-server-status_background-image-opacity'));
if (isNaN(backgroundImageOpacity) || backgroundImageOpacity < 0 || backgroundImageOpacity > 100) {
    backgroundImageOpacity = 50; // Default: 50%
}

let backgroundColor = localStorage.getItem('jka-server-status_background-color');
if (backgroundColor === null) {
    backgroundColor = '#000000';
}

// Now, let's take care of the UI

// DOM Elements
const autoRefreshSelect = document.getElementById('auto-refresh-select');
const backgroundDiv = document.createElement('div');
backgroundDiv.id = 'background-image';
document.body.prepend(backgroundDiv);
const mapBackgroundImage = document.getElementById('map-background-image').value;
const defaultBackgroundImage = document.getElementById('default-background-image').value;
const backgroundImageSelect = document.getElementById('background-image-select');
const autoBlurAndOpacityRadioButton = document.getElementById('auto-blur-and-opacity');
const customBlurAndOpacityRadioButton = document.getElementById('custom-blur-and-opacity');
const backgroundImageBlurSlider = document.getElementById('background-image-blur-slider');
const backgroundImageOpacitySlider = document.getElementById('background-image-opacity-slider');
const backgroundColorInput = document.getElementById('background-color-input');

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

/**
 * Requires up to date variables:
 * - autoOrCustomBlurAndOpacity
 * - backgroundImageBlur
 * - backgroundImageOpacity
 * - backgroundImage
 */
function applyBackgroundBlurAndOpacity() {
    if (autoOrCustomBlurAndOpacity === 'custom') {
        backgroundDiv.style.filter = 'blur(' + backgroundImageBlur + 'px)';
        backgroundDiv.style.opacity = backgroundImageOpacity + '%';
        return;
    }

    // "Blur and opacity" is set to "Auto"

    if (backgroundImage === 'always-default') {
        backgroundDiv.style.filter = 'blur('
            + document.getElementById('default-background-image-blur-radius').value
            + 'px)';
        backgroundDiv.style.opacity = document.getElementById('default-background-image-opacity').value + '%';
        return;
    }

    // "Background image" is set to "Map-dependent"
    backgroundDiv.style.filter = 'blur(' + document.getElementById('map-background-image-blur-radius').value + 'px)';
    backgroundDiv.style.opacity = document.getElementById('map-background-image-opacity').value + '%';
}
applyBackgroundBlurAndOpacity(); // Apply blur and opacity settings on load

// Background image
if (backgroundImage === 'disabled') {
    backgroundImageSelect.value = 'disabled';
} else if (backgroundImage === 'always-default') {
    backgroundImageSelect.value = 'always-default';
} else { // 'map-dependent' (or invalid)
    backgroundImageSelect.value = 'map-dependent';
}
function updateBackgroundImage() {
    backgroundImage = backgroundImageSelect.value;
    localStorage.setItem('jka-server-status_background-image', backgroundImage);
    if (backgroundImage === 'disabled') {
        backgroundDiv.style.display = 'none';
        disableImageTweaks();
        return;
    }
    
    if (backgroundImage === 'always-default') {
        backgroundDiv.style.backgroundImage = 'url(' + defaultBackgroundImage + ')';
    } else { // 'map-dependent' (or invalid)
        backgroundDiv.style.backgroundImage = 'url(' + mapBackgroundImage + ')';
    }

    applyBackgroundBlurAndOpacity();
    backgroundDiv.style.display = 'block';
    enableImageTweaks();
}
updateBackgroundImage(); // Apply background image settings on load
backgroundImageSelect.addEventListener('change', updateBackgroundImage);

// Blur and opacity: Auto / Custom
if (autoOrCustomBlurAndOpacity === 'custom') {
    customBlurAndOpacityRadioButton.setAttribute('checked', '');
} else { // 'auto' (or invalid)
    autoBlurAndOpacityRadioButton.setAttribute('checked', '');
}
function updateAutoOrCustomBlurAndOpacity() {
    if (customBlurAndOpacityRadioButton.checked) { // Custom
        autoOrCustomBlurAndOpacity = 'custom';
        enableCustomImageTweaks();
    } else { // Auto
        autoOrCustomBlurAndOpacity = 'auto';
        disableCustomImageTweaks();
    }
    localStorage.setItem('jka-server-status_background-image-blur-and-opacity', autoOrCustomBlurAndOpacity);
    applyBackgroundBlurAndOpacity();
}
updateAutoOrCustomBlurAndOpacity(); // Apply the Auto / Custom choice on load
autoBlurAndOpacityRadioButton.addEventListener('change', updateAutoOrCustomBlurAndOpacity);
customBlurAndOpacityRadioButton.addEventListener('change', updateAutoOrCustomBlurAndOpacity);

// Background image blur
backgroundImageBlurSlider.value = backgroundImageBlur;
function updateBlur() {
    if (
        !isNaN(backgroundImageBlurSlider.value)
        && backgroundImageBlurSlider.value >= 0 && backgroundImageBlurSlider.value <= 10
    ) {
        backgroundImageBlur = backgroundImageBlurSlider.value;
        document.getElementById('background-image-blur-radius').textContent = backgroundImageBlur + ' px';
        localStorage.setItem('jka-server-status_background-image-blur', backgroundImageBlur);
        applyBackgroundBlurAndOpacity();
    }
}
updateBlur(); // Apply blur settings on load
backgroundImageBlurSlider.addEventListener('input', updateBlur);

// Background image opacity
backgroundImageOpacitySlider.value = backgroundImageOpacity;
function updateOpacity() {
    if (
        !isNaN(backgroundImageOpacitySlider.value)
        && backgroundImageOpacitySlider.value >= 0 && backgroundImageOpacitySlider.value <= 100
    ) {
        backgroundImageOpacity = backgroundImageOpacitySlider.value;
        document.getElementById('background-image-opacity-percentage').textContent = backgroundImageOpacity + '%';
        localStorage.setItem('jka-server-status_background-image-opacity', backgroundImageOpacity);
        applyBackgroundBlurAndOpacity();
    }
}
updateOpacity(); // Apply opacity settings on load
backgroundImageOpacitySlider.addEventListener('input', updateOpacity);

function disableImageTweaks() {
    document.querySelectorAll('.background-image-tweak').forEach(function(element) {
        element.classList.add('disabled');
    });
    autoBlurAndOpacityRadioButton.setAttribute('disabled', '');
    customBlurAndOpacityRadioButton.setAttribute('disabled', '');
    disableCustomImageTweaks();
}

function disableCustomImageTweaks() {
    document.querySelectorAll('.background-image-custom-tweak').forEach(function(element) {
        element.classList.add('disabled');
    });
    backgroundImageBlurSlider.setAttribute('disabled', '');
    backgroundImageOpacitySlider.setAttribute('disabled', '');
}

function enableImageTweaks() {
    document.querySelectorAll('.background-image-tweak').forEach(function(element) {
        element.classList.remove('disabled');
    });
    autoBlurAndOpacityRadioButton.removeAttribute('disabled');
    customBlurAndOpacityRadioButton.removeAttribute('disabled');
    if (autoOrCustomBlurAndOpacity === 'custom') {
        enableCustomImageTweaks();
    }
}

function enableCustomImageTweaks() {
    document.querySelectorAll('.background-image-custom-tweak').forEach(function(element) {
        element.classList.remove('disabled');
    });
    backgroundImageBlurSlider.removeAttribute('disabled');
    backgroundImageOpacitySlider.removeAttribute('disabled');
}

// Background color
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
