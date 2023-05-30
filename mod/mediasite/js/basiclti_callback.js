/**
 * Provides support to map the data returned from Mediasite to the Moodle form for persistence to the database.
 *
 * @class MediasiteLtiCallBack
 * @param {object} message
 */
function MediasiteLtiCallBack(message) {
    // We should use '==' instead of '===' here, so that we can find both 'null' and 'undefined'.
    if (message.data == null || message.data.Mode == null || message.data.EntityType == null
        || message.data.LaunchUrl == null || message.data.Title == null) {
        return;
    }

    setFormValue('id_siteid', message.data.ext_content_return_url);
    setFormValue('id_name', message.data.Title);
    setFormValue('id_description', message.data.Description);
    setFormValue('id_resourcetype', message.data.EntityType);
    setFormValue('id_resourceid', message.data.ResourceId);
    setFormValue('id_launchurl', message.data.LaunchURL);
    setFormValue('id_displaymode', message.data.Mode);
    if (message.data.EntityType === 'Presentation') {
        setFormValue('id_recorddateutc', (new Date(message.data.RecordDateTimeUTC).getTime() / 1000).toFixed(0));
        if (message.data.Presenters != undefined && message.data.Presenters !== null && message.data.Presenters.length > 0) {
            setFormValue('id_presenters', message.data.Presenters.join('~!~'));
            setFormValue('id_presenters_display', message.data.Presenters.join('\n\n'));
        }
        if (message.data.Tags != undefined && message.data.Tags !== null && message.data.Tags.length > 0) {
            setFormValue('id_sofotags', message.data.Tags.join('~!~'));
            setFormValue('id_sofotags_display', message.data.Tags.join(', '));
        }
    } else {
        setFormValue('id_recorddateutc', (new Date().getTime() / 1000).toFixed(0));
        setFormValue('id_presenters', '');
        setFormValue('id_sofotags', '');
    }

    setFormValue('id_showdescription', (message.data.Mode == 'PresentationLink' || message.data.Mode == 'BasicLTI') ? '0' : '1');

    var iframeDiv = document.getElementById('mediasite_lti_content');
    if (iframeDiv != undefined && iframeDiv !== null) {
        iframeDiv.style.display = 'none';
    }

    toggleEmbedModeChange(message.data.Mode);
}

/**
 * Find the element in the form and set the value
 *
 * @param {string} id
 * @param {string} value
 */
function setFormValue(id, value) {
    var el = document.getElementById(id);
    if (el !== null && el != undefined) {
        el.value = value;
    }
}

/**
 * Toggle embed controls based on the passed in value
 *
 * @param {enum} mode
 */
function toggleEmbedModeChange(mode) {
    var allEmbedControls = document.getElementsByClassName("sofo-embed");
    Array.prototype.forEach.call(allEmbedControls, function(currentControl) {
        toggleDisplayOfParentDiv(currentControl, false);
    });

    var myEmbedControls = document.getElementsByClassName("sofo-embed-type-" + mode);
    Array.prototype.forEach.call(myEmbedControls, function(currentControl) {
        toggleDisplayOfParentDiv(currentControl, true);
    });

    setFormValue('id_showdescription', (mode === 'BasicLTI') ? '0' : '1');
}

/**
 * Show or hide the element (label and control) in the form
 *
 * @param {control} currentControl
 * @param {bool} visible
 */
function toggleDisplayOfParentDiv(currentControl, visible) {
    var parentDiv = document.getElementById('fitem_' + currentControl.id);
    if (parentDiv === null) {
        parentDiv = document.getElementById(currentControl.id);
    }
    if (parentDiv === null) {
        currentControl.style.display = visible ? 'block' : 'none';
    } else {
        parentDiv.style.display = visible ? 'block' : 'none';
    }
}

window.addEventListener("message", MediasiteLtiCallBack, false);