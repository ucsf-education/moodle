<!-- BEGIN mod_mediasite_site_form.js -->

/**
 * Client side iteraction when editing the Mediasite site in the Site Administration.
 *
 * @class MediasiteAddMenuPlacementHint
 */
function MediasiteAddMenuPlacementHint() {
    var placementSelect = document.getElementById('id_my_mediasite_placement');
    var hintText = null;
    try {
        hintText = document.getElementsByName('my_mediasite_placement_hint')[0];
    } catch (e) {
        // Just don't show the hint.
    }
    if (placementSelect !== null && hintText !== null) {
        var parent = placementSelect.parentNode;
        var hint = document.createTextNode(hintText.value);
        var hintNode = document.createElement("div");
        hintNode.className = 'mediasite-add-menu-placement-hint';
        hintNode.appendChild(hint);
        parent.appendChild(hintNode);
    }
}

window.setTimeout(MediasiteAddMenuPlacementHint, 500);

<!-- END mod_mediasite_site_form.js -->