var ENCODED_ASSIGNMENT_LAUNCH_URL;
var RealSavedThumbnailClassName = "real-saved-thumbnail";
var DisplaySubmissionContentId = "fitem_id_display_submission_content";
var ValueSubmissionContentId = "id_submission_content";


function launchMyMediasite(encodedMyMediasiteLaunchUrl, encodedAssignmentLaunchUrl) {
    var reg = new RegExp('&amp;', 'g');
    var myMediasiteLaunchUrl = decodeURIComponent(atob(encodedMyMediasiteLaunchUrl)).replace(reg, '&');
    console.log("assignment submission workflow, mymediasite launch url: " + myMediasiteLaunchUrl);

    ENCODED_ASSIGNMENT_LAUNCH_URL = encodedAssignmentLaunchUrl;

    // _blank/_self
    // window.open(launchUrl, "_blank", "width=800, heigth=600");
    window.open(myMediasiteLaunchUrl);
}


try {
    window.removeEventListener("message", MediasiteCallBack, false);
} catch (e) {
}
window.addEventListener("message", MediasiteCallBack, false);


function MediasiteCallBack(event) {

    if (event == null || event.data == null) return;

    var presoData = JSON.parse(event.data);
    
    // If not from assign_submission postMessage, jump out
    if(!presoData.assignSubmissionEmbed) return;

    var reg = new RegExp('&amp;', 'g');
    var assignmentLaunchUrl = decodeURIComponent(atob(ENCODED_ASSIGNMENT_LAUNCH_URL)).replace(reg, '&');
    assignmentLaunchUrl = assignmentLaunchUrl.replace("REPLACE_THIS_RESOURCEID", presoData.resourceId);
    console.log("assignment submission workflow, assignment launch url: " + assignmentLaunchUrl);

    var presoThumbnail =
        '<a target="_blank" href="' + assignmentLaunchUrl + '" class="' + RealSavedThumbnailClassName + '">' +
        '<img src="' + presoData.thumbnailUrl + '" alt="presentation_thumbnail_url" />' +
        '</a>';

    AddThumbnailToDisplayContent(presoThumbnail);

    //set thumbnail to value content
    document.getElementById(ValueSubmissionContentId).value = presoThumbnail;
}

function AddThumbnailToDisplayContent(displayThumbnail) {
    var formField = document.getElementById(DisplaySubmissionContentId);
    var contentDiv = formField.getElementsByClassName("form-control-static")[0];
    contentDiv.innerHTML = displayThumbnail;
}