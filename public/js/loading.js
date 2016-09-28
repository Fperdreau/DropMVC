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
 * Set up tinyMCE (rich-text textare)
 */
var tinymcesetup = function () {
    tinymce.remove();
    tinymce.init({
        mode: "textareas",
        selector: ".tinymce",
        min_height: 300,
        min_width: 300,
        width: '100%',
        resize: false,
        skin: "lightgray",
        images_upload_url: 'postAcceptor.php',
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools'
        ],
        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        toolbar2: 'print preview media | forecolor backcolor emoticons',
        image_advtab: true,
        templates: [
            { title: 'Test template 1', content: 'Test 1' },
            { title: 'Test template 2', content: 'Test 2' }
        ],
        content_css: [
            '//fast.fonts.net/cssapi/e6dc9b99-64fe-4292-ad98-6974f93cd2a2.css',
            '//www.tinymce.com/css/codepen.min.css'
        ]

    });

};

/**
 * Retrieve and display page content
 * @param url
 */
function loadPageContent(url) {
    var el = $('#pagecontent');
    if (url != window.location) {
        window.history.pushState({path: url}, '', url);
    }
    jQuery.ajax({
        url: url,
        type: 'POST',
        async: true,
        beforeSend: function () {
            loadingDiv(el);
        },
        complete: function() {
            removeLoading(el);
        },
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if (json.status.status === true) {
                changePageMeta(json.info);
                el.html(json.content);
                $('li #' + json.info.name).addClass("activepage");
                $('#navBar').html(json.navBar);
                tinymcesetup();
            } else {
                el
                    .html(json.status.msg)
                    .fadeIn(200);
            }
        }
    });
}

/**
 * Change meta tags of the loaded page
 * @param page: page information
 */
function changePageMeta(page) {
    // Update page title and meta content
    $('meta[name=description]').remove();
    $('meta[name=keywords]').remove();
    var pageTitle = (page.meta_title !== null) ? page.meta_title : page.siteTitle;
    $('title').remove();

    $('head')
        .append('<meta name="description" content="' + page.meta_description + '">')
        .append('<meta name="keywords" content="' + page.meta_keywords + '">')
        .append('<title>' + pageTitle + '</title>');
}

/**
 * Display loading animation during AJAX request
 * @param el: DOM element in which we show the animation
 */
function loadingDiv(el) {
    el
        .css('position','relative')
        .append("<div class='loadingDiv' style='width: 100%; height: 100%;'></div>")
        .fadeIn(100);
}

/**
 * Remove loading animation at the end of an AJAX request
 * @param el: DOM element in which we show the animation
 */
function removeLoading(el) {
    el.find('.loadingDiv')
        .fadeOut(1000)
        .remove();
}

// Responsive design part
function adapt() {
    var sideMenu = $('.sideMenu');
    var topNav = $('.top_nav');
    sideMenu.hide(); // Hide sideMenu

    var headerWidth = $("#sitetitle").outerWidth() + $(".top_nav").outerWidth() + $("#cart_icon").outerWidth();
    var headerHeight = $('header').outerHeight();
    var footerHeight = $('footer').outerHeight();
    var windowsHeight = $(window).height();

    $('main').css({
        'min-height': windowsHeight - headerHeight + 'px',
        left: 0
    });
    $('#pagecontent').css('min-height', windowsHeight - headerHeight + 'px');
    if ($(window).width() <= headerWidth) {
        $(".menu")
            .css('display', 'inline-block')
            .show();
        topNav.hide();
        $("#sub_menu").hide();
    } else {
        $("#sub_menu").show();
        $(".menu").hide();
        topNav
            .css('display', 'inline-block')
            .show();
    }
}

function boot() {
    var url = window.location.href;
    var el = $('body');
    jQuery.ajax({
        url: url,
        type: 'POST',
        async: true,
        beforeSend: function() {
            el
                .css('position','relative')
                .append("<div class='loadingBoot' style='width: 100%; height: 100%;'></div>")
                .fadeIn(100);
        },
        success: function() {
            el.find('.loadingBoot')
                .fadeOut(1000)
                .remove();
        }
    });
}

$( document ).ready(function() {
    // Automatically parse url and load the corresponding page
    adapt();
    tinymcesetup();

    $(window).resize(function () {
        adapt();
    });

});
