/**
 * MyUpload
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 **
 * MyUpload is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * MyUpload is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MyUpload.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Functions related to uploader
 * @todo: make a plugin
 */

// Get file information on drop
var getdrop = function (e) {
    var dt = e.dataTransfer || (e.originalEvent && e.originalEvent.dataTransfer);

    var files = e.target.files || (dt && dt.files);
    if (files) {
        var nbfiles = files.length;
        for(var i = 0; i < nbfiles; ++i){
            var data = new FormData();
            data.append('file[]',files[i]);
            processUpl(data);
        }
    }
};

/**
 * Process uploads
 * Animate uploader background while files are being uploaded. Send ajax request ($_FILES) and retrieve success or errors.
 * In case of success, show uploaded files in the files list. Otherwise, show error message.
  */
var processUpl = function(data, el) {
    el = (el === undefined) ? $('.upl_container'):el;
    var upl_list = el.find('.upl_filelist');
    var url = (el.find('form').attr('action').length > 0) ? el.find('form').attr('action'):'upload.php' ;
    var animBack = new AnimateBack(el);
    jQuery.ajax({
        type:'POST',
        url: url,
        headers:{'Cache-Control':'no-cache'},
        data:data,
        contentType:false,
        processData:false,
        beforeSend: animBack.start(),
        complete: animBack.stop(),
        success: function(response){
            var result = jQuery.parseJSON(response);
            el.find('.upl_errors').hide();
            if (result.error === true) {
                var form = $('#submit_form');
                var hiddenInput = form.find('input[name="upl_link"]');
                if (hiddenInput.length > 0 && hiddenInput !== undefined) {
                    // Append file id to files list
                    var fileList = hiddenInput.val();
                    hiddenInput.val(fileList+','+result.name);
                } else {
                    form.append('<input type="hidden" name="upl_link" class="upl_link" value="'+result.name+'" />');

                }
                upl_list.append(result.content);
            } else {
                el.find('.upl_errors').html(result.error).show();
            }
        },
        error: function(response){
            el.find('.upl_errors').html(response.statusText).show();
        }
    });
};

/**
 * Process Ajax requests
 * @param formid
 * @param data
 * @param callback: callback function
 * @param url: path to the php file
 */
var sendAjax = function(formid,data,callback,url) {
    url = (url === undefined) ? 'php/form.php':url;
    var loadingBack = new AnimateBack(formid);
    jQuery.ajax({
        url: url,
        type: 'POST',
        async: true,
        data: data,
        beforeSend: loadingBack.start(),
        complete: loadingBack.stop(),
        success: function(data) {
            var result = jQuery.parseJSON(data);
            callback(result);
        }
    });
};

/**
 * Constructor object.
 * Animate background (rightward moving linear-gradient)
 * @param el
 */
function AnimateBack(el) {
    this.el = el;
    this.interval = 0;
    this.gradient_percent = 0;
    this.interval_value = 5;
    this.time_interval = 50;
    this.timer = null;

    /**
     * Animate background
     */
    this.anim = function(){
        if(this.interval == 20) {
            this.interval = 0;
            this.gradient_percent = 0;
        }

        this.gradient_percent += this.interval_value;
        this.el.css('background', 'linear-gradient(to right, rgba(64,64,64,1) '+ this.gradient_percent+'%,rgba(0,0,0,0) 100%)');

        this.interval++;
    };

    /**
     * Start Animation
     */
    this.start = function() {
        if (this.timer == null) {
            this.timer = setInterval(
                (function(self) {
                    return function() {
                        self.anim();
                    }
                })(this),
                this.time_interval
            );
        }
    };

    /**
     * Stop animation
     */
    this.stop = function() {
        var self = this;
        if (self.timer !== null) {
            setTimeout(function () {
                clearInterval(self.timer);
                self.el.css('background-color', 'rgba(68,68,68,1)');
                self.timer = null;
            }, 1000);
        }
    };
}

$(document).ready(function() {

    var dragcounter = 0;
    $('.mainbody')

        .on('dragenter','.upl_container', function(e) {
            e.stopPropagation();
            e.preventDefault();
            dragcounter ++;
            $('.upl_container').addClass('dragging');
        })

        .on('dragleave','.upl_container', function(e) {
            e.stopPropagation();
            e.preventDefault();
            dragcounter --;
            if (dragcounter === 0) {
                $('.upl_container').removeClass('dragging');
            }
        })

        .on('dragover','.upl_container',function(e) {
            e.stopPropagation();
            e.preventDefault();
        })

        .on('drop','.upl_container',function(e) {
            e.stopPropagation();
            e.preventDefault();
            getdrop(e);
            $('.upl_container').removeClass('dragging');
        })

        .on('click','.upl_btn', function(e) {
            var container = $(this).closest('.upl_container');
            container.find('.upl_input').click();
        })

        .on('change','.upl_input',function(e) {
            e.preventDefault();
            var input = $(this);
            var el = input.closest('.upl_container');
            var fileInput = input[0];
            for(var i = 0; i < fileInput.files.length; ++i){
                var data = new FormData();
                data.append('file[]',fileInput.files[i]);
                processUpl(data, el);
            }
        })

        // Delete uploaded file
        .on('click','.del_upl',function(e) {
            e.preventDefault();
            var div = $(this);
            var fileid = div.attr('id');
            var url = (div.parent('a').attr('href') !== undefined && div.parent('a').attr('href').length > 0) ?
                div.parent('a').attr('href'):'media/delete/'+fileid;
            var data = {del_upl: true, uplname: fileid};
            var el = $('.upl_container');
            var callback = function(result) {
                if (result.status === true) {
                    $('.upl_info#upl_'+fileid).remove();
                    $('.upl_link#upl_'+fileid).remove();
                }
            };
            sendAjax(el,data,callback, url);
        });
});