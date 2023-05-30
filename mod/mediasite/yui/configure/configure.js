YUI.add('moodle-mod_mediasite-configure', function(Y) {

    M.mod_mediasite = M.mod_mediasite || {};
    M.mod_mediasite.configure = {
        init: function() {
            var self = this;
            Y.on("click", function() {
                var addSiteUrl = 'add.php';
                document.location = addSiteUrl;
            }, "#id_siteaddbutton", self);
        }
    };
}, '@VERSION@', {
    "requires": [
        "base",
        "event",
        "event-delegate",
        "node",
        "node-event-delegate",
        "node-base"
    ]
});
