YUI.add('moodle-mod_mediasite-custmenu', function(Y) {

    M.local_mediasite = M.local_mediasite || {};

    M.local_mediasite.custmenu = {
        module: 'local_mediasite',

        init: function(config) {
            if (M.cfg.developerdebug) {
                Y.log("Entered local_mediasite.custmenu!", "info", this.module);
            }

            var location = 0;
            // Header data.
            if (config.header && config.hdrsearch) {
                if (config.hdrappend && Boolean(config.hdrappend)) {
                    location = null;
                }
                this.embed(config.hdrsearch, config.header, location);
            }

            // Menu.
            if (config.items && config.menusearch) {
                location = 0;
                if (config.menuappend && Boolean(config.menuappend)) {
                    location = null;
                }
                this.embed(config.menusearch, config.items, location);
            }
        }, // End init.

        embed: function(search, content, location) {
            var node = Y.one(search);
            if (node) {
                node.insert(content, location);
            } else {
                if (M.cfg.developerdebug) {
                    Y.log("Unable to locate element " + search, "error", this.module);
                }
            }
        } // End embed.

    }; // End custmenu.

}, '@VERSION@', {requires: ['node', 'console']});