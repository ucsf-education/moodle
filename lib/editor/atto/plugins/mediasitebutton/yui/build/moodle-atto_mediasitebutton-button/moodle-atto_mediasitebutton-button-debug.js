/**
 * @module moodle-atto_atto_mediasitebutton-button
 */

/**
 * Atto text editor mediasitebutton plugin.
 *
 * @namespace M.atto_mediasitebutton
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

YUI.add('moodle-atto_mediasitebutton-button', function (Y, NAME) {

    var COMPONENTNAME = 'atto_mediasitebutton';
    var IFRAMESRC = '';
    var SITEID = '';
    var WIDTH = 1024;
    var HEIGHT = 800;
    var DIALOGHEIGHTOFFSET = 180;
    var DIALOGWIDTHOFFSET = 60;
    var MODE = '';
    var TOOLCONSUMERKEY = '';
    var NEWPAGE = '';
    var EXTCONTENTRETURNURL = '';
    var ASSIGNMENTLAUNCHURL = '';
    var EMBEDBUTTONCLICKED = false;
    var DIVID = 'atto_mediasitebutton';
    var SELF_PAGE;
    var IFRAME_TEMPLATE = '<div id="{{element_id}}_div" class="mdl-align">' +
                        '<iframe id="{{element_id}}_iframe" src="{{launch_url}}" height="{{height}}" width="{{width}}" scrolling="auto"></iframe>' +
                        '</div>';

    Y.namespace('M.atto_mediasitebutton').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
        /**
         * The current language en by default.
         * **/
        _lang: 'en',

        /**
         * Initialization function.
         *
         * @param {object} config
         *   The information needed to use Mediasite
         **/
        initializer: function(config) {
            this._log('Initializing atto_mediasitebutton in debug');
            this._log('launch_url: ' + config.launch_url);
            this._log('site_id: ' + config.site_id);
            this._log('enabled: ' + config.enabled);
            this._log('assignment launch url: ' + ASSIGNMENTLAUNCHURL);
            if (!config.enabled) {
                this._log('The atto_mediasitebutton is disabled, exiting.');
                return;
            }

            IFRAMESRC = this._decodeUrl(config.launch_url);
            SITEID = config.site_id;
            MODE = 'CustomIntegration';
            TOOLCONSUMERKEY = config.toolconsumerkey;
            NEWPAGE = config.newpage;
            EXTCONTENTRETURNURL = config.extcontentreturnurl;
            ASSIGNMENTLAUNCHURL = this._decodeUrl(config.assignmentlaunchurl);

            var viewPortWidth = Y.DOM.winWidth();
            if (viewPortWidth > 0) {
                if (viewPortWidth > 900) {
                    WIDTH = viewPortWidth - DIALOGWIDTHOFFSET * 2;
                } else {
                    WIDTH = viewPortWidth - 40;
                }
            }
            this._log('viewPortWidth: ' + viewPortWidth + ', WIDTH: ' + WIDTH);

            this.addButton({
                title: 'mediasitebutton',
                buttonName: 'mediasitebutton',
                icon: 'icon',
                iconComponent: 'atto_mediasitebutton',
                callback: this._displayMediasiteUploadDialog
            });
        },

        _decodeUrl: function (url) {
            return decodeURIComponent(atob(url)).replace(/&amp;/g, '&')
        },

        _displayMediasiteUploadDialog: function (e, clickedicon) {
            this._log('_displayMediasiteUploadDialog');
            SELF_PAGE = this;
            
            var dialog = this.getDialogue({
                headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
                width: (WIDTH + DIALOGWIDTHOFFSET) + 'px',
                height: (HEIGHT + DIALOGHEIGHTOFFSET) + 'px',
                focusAfterHide: clickedicon
            });

            EMBEDBUTTONCLICKED = false;

            dialog.after('visibleChange', function() {
                var attributes = dialog.getAttrs();

                if (attributes.visible === false) {
                    setTimeout(function() {
                        dialog.reset();
                    }, 5);
                }
            });

            dialog.set('bodyContent', this._buildMediasiteDialogContent()).show();
            this._resizeDialog();
            this.markUpdated();
        },

        _buildMediasiteDialogContent: function(e) {
            var inserttext = M.util.get_string('editor_insert', COMPONENTNAME);
            var template = Y.Handlebars.compile(IFRAME_TEMPLATE);
            var content = Y.Node.create(template({
                launch_url: IFRAMESRC,
                height: HEIGHT,
                width: WIDTH,
                element_id: DIVID,
                insert: inserttext
            }));
            this._form = content;

            return content;
        },

        _resizeDialog: function(e) {
            this._log('_resizeDialog called');
            var iframeid = DIVID + '_iframe';
            this._log('iframdeid: ' + iframeid);
        },

        _log: function(msg) {
            console.log(COMPONENTNAME + ' : ' + msg);
        }
    });

    // Add event listener here, so that callback function is able to receive postMessage data from My Mediasite presentation details page
    try {
        window.removeEventListener("message", MediasiteAttoButtonCallBack, false);
    } catch (e) {
    }
    window.addEventListener("message", MediasiteAttoButtonCallBack, false);
    
    function MediasiteAttoButtonCallBack(event){
        console.log(COMPONENTNAME + ' : ' + "Atto button callback received a message!");

        if(event == null || event.data == null) return;

        try {
            var embedData = JSON.parse(event.data);
            // If not from atto_button postMessage, jump out
            if(!embedData.attoButtonEmbed) return;

            SELF_PAGE.getDialogue({ focusAfterHide: null }).hide();
            SELF_PAGE.editor.focus();

            if (!EMBEDBUTTONCLICKED) {
                var resourceId = embedData.resourceId;
                var embedLaunchUrl = ASSIGNMENTLAUNCHURL.replace('##ID##', resourceId);
                var embedContentHtml = decodeURIComponent(embedData.contentHtml);
                embedContentHtml = embedContentHtml.replace("REPLACE_THIS_LAUNCH_URL", embedLaunchUrl);
                embedContentHtml = embedContentHtml.replace("REPLACE_THIS_LAUNCH_URL", embedLaunchUrl);
                embedContentHtml = embedContentHtml.replace("REPLACE_THIS_LAUNCH_URL", embedLaunchUrl);

                console.log(COMPONENTNAME + ' : ' + "replaced embed launch url: " + embedLaunchUrl);
                console.log(COMPONENTNAME + ' : ' + "replaced embed content html: " + embedContentHtml);

                SELF_PAGE.get('host').insertContentAtFocusPoint(embedContentHtml);
                EMBEDBUTTONCLICKED = true;
            } else {
                console.log(COMPONENTNAME + ' : ' + 'The content has already been embedded once. Skip this event.');
            }

            SELF_PAGE.markUpdated();
        }
        catch (err) {
            console.log(COMPONENTNAME + ' : ' + 'There was an error parsing the message');
            return;
        }
        console.log(COMPONENTNAME + ' : ' + 'Finished processing postmessage');
    }

}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin", "get"]});