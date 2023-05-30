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
    var DIALOGHEIGHTOFFSET = 140;
    var DIALOGWIDTHOFFSET = 60;
    var MODE = '';
    var TOOLCONSUMERKEY = '';
    var NEWPAGE = '';
    var EXTCONTENTRETURNURL = '';
    var ASSIGNMENTLAUNCHURL = '';
    var DIVID = 'atto_mediasitebutton';
    var IFRAME_TEMPLATE = '<button id="{{element_id}}_button" class="mediasitebutton_submit btn btn-warning" style="margin: 0 0 12px 20px">{{insert}}</button>' +
                          '<div id="{{element_id}}_div" class="mdl-align">' +
                          '<iframe id="{{element_id}}_iframe" src="{{launch_url}}" height="{{height}}" width="{{width}}" scrolling="auto"></iframe>' +
                          '</div>';
    var SUBMISSION_TEMPLATE = '<div style="padding: 8px; background-color: white;">' + 
        '<div style="width: 100%; margin-bottom: 8px;"><a href="~~LAUNCHURL~~" target="_blank" />' +
        '<div class="btn btn-secondary">{{grade_presentation}}</div></a></div>' +
        '<h3><a href="~~LAUNCHURL~~">{{title}}</a></h3>' +
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
            this._log('Initializing release');
            this._log('launch_url: ' + config.launch_url);
            this._log('site_id: ' + config.site_id);
    
            IFRAMESRC = this._decodeUrl(config.launch_url);
            SITEID = config.site_id;
            MODE = 'CustomIntegration';
            TOOLCONSUMERKEY = config.toolconsumerkey;
            NEWPAGE = config.newpage;
            EXTCONTENTRETURNURL = config.extcontentreturnurl;
            ASSIGNMENTLAUNCHURL = this._decodeUrl(config.assignmentlaunchurl);
    
            this.addButton({
                title: 'mediasitebutton',
                buttonName: 'mediasitebutton',
                icon: 'mediasite_logo',
                iconComponent: 'atto_mediasitebutton',
                callback: this._displayMediasiteUploadDialog
            });
        },
    
        _decodeUrl: function (url) {
            return decodeURIComponent(atob(url)).replace('&amp;', '&')
        },
    
        _displayMediasiteUploadDialog: function (e, clickedicon) {
            this._log('_displayMediasiteUploadDialog');
            
            var dialog = this.getDialogue({
                headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
                width: (WIDTH + DIALOGWIDTHOFFSET) + 'px',
                height: (HEIGHT + DIALOGHEIGHTOFFSET) + 'px',
                focusAfterHide: clickedicon
            });
    
            dialog.after('visibleChange', function() {
                var attributes = dialog.getAttrs();
    
                if (attributes.visible === false) {
                    setTimeout(function() {
                        dialog.reset();
                    }, 5);
                }
            });
    
            dialog.set('bodyContent', this._buildMediasiteDialogContent()).show();
    
            this.markUpdated();
        },
    
        _buildMediasiteDialogContent: function() {
            var template = Y.Handlebars.compile(IFRAME_TEMPLATE)
            var content = Y.Node.create(template({
                launch_url: IFRAMESRC,
                height: HEIGHT,
                width: WIDTH,
                element_id: DIVID,
                insert: M.util.get_string('insert', COMPONENTNAME)
            }));
            this._form = content;
            this._form.one('.mediasitebutton_submit').on('click', this._processMediasiteInsert, this);
            return content;
        },
    
        _processMediasiteInsert: function (e) {
            var self = this;
            self._log('you clicked insert!');
            e.preventDefault();
    
            var iframe = document.getElementById(DIVID + '_iframe').contentWindow;
            debugger;
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
                self._log('postmessage has been received');
                self._log('SITEID: ' + SITEID);
                var preso = JSON.parse(e.data);
    
                var embed = self._layoutAbstractWithTemplate(preso);
    
                self.getDialogue({ focusAfterHide: null }).hide();
    
                self.editor.focus();
                self.get('host').insertContentAtFocusPoint(embed);
                self.markUpdated();            
            });
        },
    
        _layoutAbstractWithTemplate: function(preso) {
            this._log('_layoutAbstractWithTemplate called.');
            var template = Y.Handlebars.compile(SUBMISSION_TEMPLATE);
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
            // html = html.replace('~~PRESENTERS~~', presenters);
            html = html.replace('~~TAGS~~', tags);
            this._log(html);
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