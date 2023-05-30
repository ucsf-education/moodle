/**
 * Provides client side update when a different Mediasite site is selected in the course settings.
 *
 * The Moodle plugin precheck incorrectly reports 'SiteChange' is defined but never used.
 * SiteChange is called from /mediasite/site/mod_course_settings_form.php when the course's
 * selected site is changed.
 *
 * @class SiteChange
 * @param {control} siteControl
 * @param {object} data
 */
function SiteChange(siteControl, data) {
    var newSiteId = siteControl.value;
    data.sites.forEach(function(element) {
        if (element.id == newSiteId) {
            updateControl('id_mediasite_courses_enabled', element.showIntegrationCatalog, element.coursesTitle);
            updateControl('id_assignment_submission_enabled', element.showAssignmentSubmission, '');
        }
    });
}

/**
* @class updateControl
* @param {object} controlid
* @param {object} sitesetting
* @param {object} labeltext
*/
function updateControl(controlid, sitesetting, labeltext) {
    var chkbox = document.getElementById(controlid);
    chkbox.checked = (sitesetting > 1);
    chkbox.disabled = (sitesetting == 0 || sitesetting == 3);

    var i = 0;
    if (chkbox.parentNode.tagName.toUpperCase() === 'LABEL') {
        var objectstore = new Array();
        var parent = chkbox.parentNode;
        for (i = 0; i < chkbox.parentNode.children.length; i++) {
            objectstore[i] = chkbox.parentNode.children[i].cloneNode();
        }
        while (parent.firstChild) {
            parent.removeChild(parent.firstChild);
        }
        for (i = 0; i < objectstore.length; i++) {
            parent.appendChild(objectstore[i]);
        }
        var label = document.createElement('span');
        label.innerHTML = (sitesetting > 0) ? '&nbsp;' + labeltext : '';
        parent.appendChild(label);
     } else {
        for (i = 0; i < chkbox.parentNode.children.length; i++) {
            if (chkbox.parentNode.children[i].tagName.toUpperCase() === 'LABEL') {
                chkbox.parentNode.children[i].innerHTML = (sitesetting > 0) ? '&nbsp;' + labeltext : '';
            }
        }
    }

}