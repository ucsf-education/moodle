YUI.add('moodle-atto_mediasitebutton-button', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for atto_mediasitebutton
 *
 * @package atto
 * @subpackage   atto_mediasitebutton
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


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
var ISASSIGNMENT = true;
var EMBEDBUTTONCLICKED = false;
var DIVID = 'atto_mediasitebutton';
var IFRAME_TEMPLATE = '<div id="{{element_id}}_instruction_div" class="mediasite-instruction alert alert-info alert-block fade in" style="color: #0149bc;"><div><h2>{{instruction_title}}</h2><p>~~INSTRUCTIONAL_TEXT~~</p></div><button id="{{element_id}}_button_continue" class="mediasitebutton_continue btn btn-primary">{{continue}}</button></div>' +
                      '<div id="{{element_id}}_iframe_div">' + 
                      '<button id="{{element_id}}_button_top" class="mediasitebutton_submit btn btn-success" style="margin: 0 0 12px 20px; background-color: green;">{{insert}}</button>' +
                      '<div id="{{element_id}}_div" class="mdl-align">' +
                      '<iframe id="{{element_id}}_iframe" src="{{launch_url}}" height="{{height}}" width="{{width}}" scrolling="auto"></iframe>' +
                      '</div>' +
                      '<button id="{{element_id}}_button_bottom" class="mediasitebutton_submit btn btn-success" style="margin: 0 0 12px 20px; background-color: green;">{{insert}}</button>' +
                      '</div>';
var SUBMISSION_TEMPLATE = '<div style="padding: 8px; background-color: white;">' + 
    '<div style="width: 100%; margin-bottom: 8px;"><a href="~~LAUNCHURL~~" target="_blank" />' +
    '<div class="btn btn-secondary">{{grade_presentation}}</div></a></div>' +
    '<h3><a href="~~LAUNCHURL~~" target="_blank">{{title}}</a></h3>' +
    '<a href="~~LAUNCHURL~~" target="_blank">' +
    '<img style="margin-left: 20px" align="right" src="{{thumbnail}}" alt="{{title}}"></a>' +
    '<p><span style="font-weight: bold;">{{record_date_label}}</span>: {{record_date}}</p>' +
    '<p><span style="font-weight: bold;">{{upload_date_label}}</span>: {{upload_date}}</p>' +
    '<p>{{description}}</p>' +
    '<p><span style="font-weight: bold;">{{tags_label}}</span><br />' +
    '<ul style="list-style-type: none; margin: 0; padding: 0;">~~TAGS~~</ul></p>' +
    '<br clear="both" />' +
    '<div style="text-align: center; width: 100%; margin-top: 8px;"><a href="~~LAUNCHURL~~" target="_blank" />' +
    '<div class="btn btn-secondary">{{grade_presentation}}</div></a></div>' +
    '</div>';

var EDITOR_TEMPLATE = '<div style="padding: 8px; background-color: white;">' + 
    '<div style="width: 100%; margin-bottom: 8px;"><a href="~~LAUNCHURL~~" target="_blank" />' +
    '<h3><a href="~~LAUNCHURL~~" target="_blank">{{title}}</a></h3>' +
    '<a href="~~LAUNCHURL~~" target="_blank">' +
    '<img style="margin-left: 20px" align="right" src="{{thumbnail}}" alt="{{title}}"></a>' +
    '<p><span style="font-weight: bold;">{{record_date_label}}</span>: {{record_date}}</p>' +
    '<p><span style="font-weight: bold;">{{upload_date_label}}</span>: {{upload_date}}</p>' +
    '<p>{{description}}</p>' +
    '<p><span style="font-weight: bold;">{{tags_label}}</span><br />' +
    '<ul style="list-style-type: none; margin: 0; padding: 0;">~~TAGS~~</ul></p>' +
    '<br clear="both" />' +
    '<div style="text-align: center; width: 100%; margin-top: 8px;"><a href="~~LAUNCHURL~~" target="_blank" />' +
    '</div>';

    var HELP_TEMPLATE = '';


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
        ISASSIGNMENT = (config.isassignment == true);
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
        Y.one('#{{element_id}}_iframe_div'.replace('{{element_id}}', DIVID)).hide();

        this._resizeDialog();

        this.markUpdated();
    },

    _buildMediasiteDialogContent: function(e) {
        var inserttext = ISASSIGNMENT ? M.util.get_string('insert', COMPONENTNAME) : M.util.get_string('editor_insert', COMPONENTNAME);
        var instructionaltext = ISASSIGNMENT ? M.util.get_string('submission_instructions', COMPONENTNAME) : M.util.get_string('editor_instructions', COMPONENTNAME);
        var template = Y.Handlebars.compile(IFRAME_TEMPLATE.replace('~~INSTRUCTIONAL_TEXT~~', instructionaltext));
        var content = Y.Node.create(template({
            launch_url: IFRAMESRC,
            height: HEIGHT,
            width: WIDTH,
            element_id: DIVID,
            insert: inserttext,
            continue: M.util.get_string('continue', COMPONENTNAME),
            instruction_title: M.util.get_string('dialogtitle', COMPONENTNAME)
        }));
        this._form = content;
        this._form.one('#{{element_id}}_button_top'.replace('{{element_id}}', DIVID)).on('click', this._processMediasiteInsert, this);
        this._form.one('#{{element_id}}_button_bottom'.replace('{{element_id}}', DIVID)).on('click', this._processMediasiteInsert, this);
        this._form.one('#{{element_id}}_button_continue'.replace('{{element_id}}', DIVID)).on('click', this._showMediasiteIframe, this);
        return content;
    },

    _resizeDialog: function(e) {
        this._log('_resizeDialog called');
        var iframeid = DIVID + '_iframe';
        this._log('iframdeid: ' + iframeid);
    },

    _showMediasiteIframe: function (e) {
        // debugger;
        this._log('Hide the instructions and show the iframe.');
        e.preventDefault();
        // hide the instructions
        Y.one('#{{element_id}}_instruction_div'.replace('{{element_id}}', DIVID)).hide();
        // show the iframe
        Y.one('#{{element_id}}_iframe_div'.replace('{{element_id}}', DIVID)).show();
    },

    _processMediasiteInsert: function (e) {
        var self = this;
        self._log('Starting to insert Mediasite content.');
        e.preventDefault();

        // debugger;

        var iframe = document.getElementById(DIVID + '_iframe').contentWindow;
        var data = JSON.stringify({ 
            type: 'embedrequest', 
            Mode: MODE, 
            NewPage: NEWPAGE, 
            ExtContentReturnUrl: EXTCONTENTRETURNURL, 
            ToolConsumerKey: TOOLCONSUMERKEY 
        });
        iframe.postMessage(data, '*');

        var eventSupportMode;
        var messageEventName;
        // browser detection
        if (window.addEventListener) {
            eventSupportMode = 'addEventListener';
            messageEventName = 'message';
        } else {
            eventSupportMode = 'attachEvent';
            messageEventName = 'onmessage';
        }
        // bind the event
        window[eventSupportMode](messageEventName, function (e) {
            self._log('Received a postmessage');
            self._log('SITEID: ' + SITEID);
            try {
                var preso = JSON.parse(e.data);
                // make sure the message is 
                if (preso.type === 'embedresponse') {
                    self._log('The message is type embedresponse');
                    var embed = self._layoutAbstractWithTemplate(preso);

                    self.getDialogue({ focusAfterHide: null }).hide();
        
                    self.editor.focus();
                    if (!EMBEDBUTTONCLICKED) {
                        self.get('host').insertContentAtFocusPoint(embed);
                        EMBEDBUTTONCLICKED = true;
                    } else {
                        self._log('The content has already been embedded once. Skip this event.')
                    }
                    self.markUpdated();            
                } else {
                    self._log('The message is of an unknown type');
                    return;
                }
            }
            catch (err) {
                self._log('There was an error parsing the message');
                return;
            }
            self._log('Finished processing postmessage');
        });
    },

    _layoutAbstractWithTemplate: function(preso) {
        this._log('_layoutAbstractWithTemplate called.');
        var template = ISASSIGNMENT ? Y.Handlebars.compile(SUBMISSION_TEMPLATE) : Y.Handlebars.compile(EDITOR_TEMPLATE);
        // var presenters = this._buildListItemsFromArray(preso.Presenters);
        var tags = this._buildListItemsFromArray(preso.Tags);
        var tagsLabel = '';
        if (preso.Tags.length === 1) {
            tagsLabel = M.util.get_string('tag', COMPONENTNAME);
        } else if (preso.Tags.length > 1) {
            tagsLabel = M.util.get_string('tags', COMPONENTNAME);
        }
        var content = Y.Node.create(template({
            title: preso.Name,
            record_date: this._formatDate(preso.Date, preso.Culture),
            upload_date: this._formatDate(preso.UploadDate, preso.Culture),
            thumbnail: preso.ThumbnailUrl,
            description: preso.Description,
            grade_presentation: M.util.get_string('grade_presentation', COMPONENTNAME),
            record_date_label: M.util.get_string('record_date', COMPONENTNAME),
            upload_date_label: M.util.get_string('upload_date', COMPONENTNAME),
            tags_label: tagsLabel
        }));
        var html = content._node.outerHTML.replace(new RegExp('~~LAUNCHURL~~', 'g'), 
            ASSIGNMENTLAUNCHURL.replace('##ID##', preso.ResourceId));
        html = html.replace('~~TAGS~~', tags);

        return html;
    },

    _buildListItemsFromArray: function(array) {
        var list = '';
        for (var i = 0; i < array.length; i++) {
            list += '<li>' + array[i] + '</li>';
        }
        return list;
    },

    _formatDate: function(datestring, culture) {
        if (datestring === undefined) {
            return;
        }
        this._log('_formatDate: datestring = ' + datestring + ', culture = ' + culture);
        if (window.Intl && typeof window.Intl === "object") {
            var date = new Date(datestring);
            var options = { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric', timeZoneName: 'short' };
            options.timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            return new Intl.DateTimeFormat(culture, options).format(date);
        } else {
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
    },

    _log: function(msg) {
        console.log(COMPONENTNAME + ' : ' + msg);
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin", "get"]});