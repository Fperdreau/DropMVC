// leanModal v1.1 by Ray Stone - http://finelysliced.com.au
// Dual licensed under the MIT and GPL

/** Main plugin
 *
 */
(function($){
    $.fn.extend({
        leanModal:function(options){
            var defaults={
                top:100,
                overlay:0.5,
                closeButton:null};

            var overlay=$("<div id='lean_overlay'></div>");

            $("body").append(overlay);

            options = $.extend(defaults,options);
            return this.each(function(){
                var o=options;
                $(this).click(function(e){
                    var overlayEL = $('#lean_overlay');

                    var modal_id = ($(this).data("modal") === undefined) ? '#modal':$(this).data("modal"); // Define default DOM target
                    var section = $(this).data('section');

                    overlayEL.click(function(){
                        close_modal(modal_id)
                    });

                    $(o.closeButton).click(function(){
                        close_modal(modal_id)
                    });

                    overlayEL
                        .css({"display":"block",opacity:0})
                        .fadeTo(200,o.overlay);

                    $(modal_id).css({
                        "display":"block",
                        "position":"fixed",
                        "opacity":0,
                        "z-index":11000
                    });

                    $(modal_id).fadeTo(200,1);

                    e.preventDefault()
                })
            });

        }
    })
})(jQuery);

function getModalContent(url, section) {
    jQuery.ajax({
        url: url,
        type: 'POST',
        async: false,
        success: function(data){
            var result = jQuery.parseJSON(data);
            var html = (result.content !== undefined) ? result.content: result;
            section
                .hide()
                .html(html)
                .show();

            adapt_modal();

        }
    });
}

/**
 * Close modal when clicking outsize the window or on the close button
 * @param modal_id
 */
function close_modal(modal_id){
    if (modal_id === undefined) {
        modal_id = ".modalContainer";
    }
    $("#lean_overlay").fadeOut(200);
    $(modal_id).css({"display":"none"})
}

/**
 * Show the targeted modal section and hide the others
 * @param sectionid: section id
 * @param modalid: modal container id
 */
function show_section(sectionid, modalid) {
    modalid = (modalid === undefined) ? '#modal':modalid; // Define default DOM target
    $('.modal_section').hide();
    var title = $(modalid+" .modal_section#"+sectionid).data('title');
    $(".popupHeader").text(title);
    $(modalid + ' .modal_section').each(function() {
        var thisid = $(this).attr('id');
        if (thisid === sectionid) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    adapt_modal();
}

/**
* Adapts modal window's size and position to current window's size
 */
function adapt_modal(modal) {
    var modalContainer = (modal === undefined) ? $('.modalContainer') : modal;

    var windowWidth = $(window).width();
    var windowsHeight = $(window).height();

    $('.modal_section').each(function() {
       if ($(this).is(':visible')) {

           $(this).css({"max-height": 0.9*windowsHeight + 'px'});

           var height = $(this).outerHeight();
           var width = $(this).outerWidth();

           var left = 0.5 * (windowWidth - width);
           var top = 0.5 * (windowsHeight - height);

           modalContainer.css({
               top: top + 'px',
               left: left + 'px'
           })
       }
    });
}

$(document).ready(function() {

    $(window).resize(function () {
        adapt_modal();
    });

    $('body')

        .on('click', '#lean_overlay', function(e) {
            $(this).hide();
        })

        // Bind leanModal to triggers
        .on('mouseover',".leanModal",function(e) {
            e.preventDefault();
            $(this).leanModal({top : 50, overlay : 0.6, closeButton: ".close_modal" });
        })

        .on('click', ".close_modal", function(e) {
            e.preventDefault();
            close_modal();
        })

        .on('click', '.leanModal', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url.length > 0 && url !== undefined) {
                var sectionid = $(this).data('section');
                getModalContent(url, $('.modal_section#'+sectionid));
            }
        });
});
