/**
 *
 * File for javascript/jQuery functions
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of DropMVC.
 *
 * DropMVC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropMVC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DropMVC.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Functions required to manage plugins and scheduled tasks
 * @todo: create a plugin instead of series of independent functions
 */

/**
 * Send ajax request to a provided url, show loading icon during the ajax call and runs some callback function if the
 * request succeeded.
 * @param el: DOM element
 * @param callback
 */
function ajaxableLink(el, callback) {
    var iconDiv = el.find('.optBtn_icon');
    var current_class = iconDiv.attr('class').split(' ');
    current_class.splice( current_class.indexOf('optBtn_icon'), 1 );
    jQuery.ajax({
        type: 'post',
        url: el.attr('href'),
        beforeSend: function () {
            iconDiv
                .removeClass(current_class)
                .addClass('loadBtn');
        },
        complete: function () {
            iconDiv
                .removeClass('loadBtn')
                .addClass(current_class);
        },
        success: function (result) {
            var json = jQuery.parseJSON(result);
            if (callback !== undefined) {
                callback(json);
            }
        }
    });
}

$(document).ready(function() {
    $("<style>")
        .prop("type", "text/css")
        .html("\
            .valid_input {\
                background: rgba(0, 200, 0, .5);\
            }\
            .wrong_input {\
                background: rgba(200, 0, 0, .5);\
            }")
        .appendTo("head");

    $('.mainbody')

    /**
     * Modify plugin/scheduled task settings
     */
        .on('click','.modSettings', function(e){
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();

            var name = $(this).data('name');
            var op = $(this).data('op');
            var postUrl = input.closest('form').attr('action');
            var data = form.serializeArray();

            jQuery.ajax({
                url: postUrl,
                type: 'POST',
                data: data,
                success: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json === true || json !== false) {
                        if (op == 'cron') {
                            $('#cron_time_'+name).html(json.msg);
                        }
                        input.addClass('valid_input');
                        setTimeout(function(){
                            input.removeClass('valid_input');
                        }, 500)
                    } else {
                        input.addClass('wrong_input');
                        setTimeout(function(){
                            input.removeClass('wrong_input');
                        }, 500);
                    }
                }
            });
        })

        .on('click', '.modCron', function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var name = form.find('input[name="modCron"]').val();
            var callback = function(json) {
                $('.plugTime#' + name).html(json.msg);
            };
            processForm(form, callback);
        })

        /**
         * Launch installation of plugin/scheduled task
         */
        .on('click','.installDep',function(e) {
            e.preventDefault();
            var el = $(this);
            var op = $(this).data('op');
            var div = $(this).closest('.plugDiv');
            var callback = function(result) {
                if (result.status) {
                    var prevClass = (op === 'install') ? 'installBtn' : 'uninstallBtn';
                    var newClass = (op === 'install') ? 'uninstallBtn' : 'installBtn';
                    var newAttr = (op === 'install') ? 'uninstall' : 'install';
                    el.attr('data-op', newAttr);
                    el.find('.optBtn_icon')
                        .removeClass(prevClass)
                        .addClass(newClass);
                }
                validsubmitform(div, result);
            };
            ajaxableLink(el, callback);
        })

        /**
         * Activate/Deactivate plugin/scheduled task
         */
        .on('click','.activateDep',function(e) {
            e.preventDefault();
            var el = $(this);
            var op = $(this).attr('data-op');
            var div = $(this).closest('.plugDiv');
            var callback = function(result) {
                if (result.status) {
                    var prevClass = (op === 'On') ? 'activateBtn' : 'deactivateBtn';
                    var newClass = (op === 'On') ? 'deactivateBtn':'activateBtn';
                    var newAttr = (op === 'On') ? 'Off':'On';
                    el.attr('data-op', newAttr);
                    el.find('.optBtn_icon')
                        .removeClass(prevClass)
                        .addClass(newClass);
                }
                validsubmitform(div, result);
            };
            ajaxableLink(el, callback);
        })

        /**
         * Display plugin/scheduled task options
         */
        .on('click','.optShow',function(e) {
            e.preventDefault();
            var name = $(this).closest('.plugDiv').attr('id');
            var op = $(this).attr('data-op');
            var callback = function(json) {
                //var json = jQuery.parseJSON(data);
                $(".plugOpt#"+name)
                    .html(json)
                    .toggle();
            };
            ajaxableLink($(this), callback); return true;
        })

        /**
         * Modify plugin/scheduled task options
         */
        .on('click','.modOpt',function(e) {
            e.preventDefault();
            var name = $(this).closest('.plugOpt').attr('id');
            var op = $(this).attr('data-op');
            var div = $(this).closest('.plugDiv');
            var form = $(this).length > 0 ? $($(this)[0].form) : $();
            var option = form.serializeArray();
            var data = {modOpt: name, op: op, data:option};
            var url = form.attr('action');
            processAjax(div, data, null, url);
        })

        /**
         * Show task's logs
         */
        .on('click', '.showLog', function(e) {
            e.preventDefault();
            var name = $(this).attr('id');
            var div = $('.plugLog#' + name);
            if (!div.is(':visible')) {
                jQuery.ajax({
                    type: 'post',
                    url: $(this).attr('href'),
                    data: {showLog: name},
                    success: function(data) {
                        var json = jQuery.parseJSON(data);
                        $('.plugLog#' + name).html(json).toggle();
                    }
                });
            } else {
                div.toggle();
            }
        })

        .on('click', '.deleteLog', function(e) {
            e.preventDefault();
            var div = $(this).closest('.plugDiv');
            jQuery.ajax({
                type: 'post',
                url: $(this).attr('href'),
                success: function(data) {
                    validsubmitform(div, data);
                }
            });
        })

        /**
         * Run a scheduled task manually
         */
        .on('click','.run_cron',function(e) {
            e.preventDefault();
            var el = $(this);
            var cron = $(this).attr('data-cron');
            var div = $(this).closest('.plugDiv');
            var callback = function (data) {
                validsubmitform(div, data);
            };
            ajaxableLink(el, callback);
        });
});
