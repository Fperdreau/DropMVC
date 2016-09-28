/**
 *
 * File for javascript/jQuery functions
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of JR-Haarsieraad.
 *
 * JR-Haarsieraad is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JR-Haarsieraad is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with JR-Haarsieraad.  If not, see <http://www.gnu.org/licenses/>.
 */

 /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 General functions
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
// Display presentation information in a modal window
var displaypub = function(idpress,formel) {
    jQuery.ajax({
        url: 'php/form.php',
        type: 'POST',
        async: false,
        data: {
            show_pub: idpress
        },
        success: function(data){
            var result = jQuery.parseJSON(data);
            formel
                .hide()
                .html(result)
                .fadeIn(200);
            tinyMCE.remove();
            window.tinymce.dom.Event.domLoaded = true;
            tinymcesetup();
        }
    });
};

// Show publication form
var showpubform = function(url,formel,idpress) {
    if (idpress === undefined) {idpress = false;}
    // First we remove any existing submission form
    $('.submission').remove();
    jQuery.ajax({
        url: url,
        type: 'POST',
        async: false,
        data: {
            getpubform: idpress
        },
        success: function(data){
            var result = jQuery.parseJSON(data);
            formel
                .hide()
                .html(result)
                .show();
            tinyMCE.remove();
            window.tinymce.dom.Event.domLoaded = true;
            tinymcesetup();
        }
    });
};

var displayToolForm = function(formel,toolName,form) {
    if (toolName === undefined) {toolName = false;}
    if (form === undefined) {form = true;}
    var data = {gettoolform: toolName, form: form};
    var callback = function(result) {
        formel
            .hide()
            .html(result)
            .show();
    };
    // First we remove any existing submission form
    $('.submission').remove();
    processAjax(formel,data,callback);
};

var displayCvForm = function(formel,cvid) {
    if (cvid === undefined) {cvid = false;}

    // First we remove any existing submission form
    $('.submission').remove();
    jQuery.ajax({
        url: 'php/form.php',
        type: 'POST',
        async: false,
        data: {getcvform: cvid},
        success: function(data){
            var result = jQuery.parseJSON(data);
            formel
                .hide()
                .html(result)
                .show();
            tinyMCE.remove();
            window.tinymce.dom.Event.domLoaded = true;
            tinymcesetup();
        }
    });
};


var showpostform = function(postid, lang) {
    lang = (lang !== undefined && lang !== '') ? '/'+lang:'';
    var url = (postid === false) ? 'blog/edit':'blog/edit/'+postid+lang;
    jQuery.ajax({
        url: url,
        type: 'POST',
        async: true,
        data: {
            post_show: true,
            postid: postid},
        success: function(data){
            var result = jQuery.parseJSON(data);
            var txtarea = "<textarea name='content' id='post_content' class='tinymce'>"+result.content+"</textarea>";
            setTimeout(function() {
                $('.trad_form_container')
                    .empty()
                    .html(result.form)
                    .fadeIn(200);
                $('.tinymce_container')
                    .closest('.form-group')
                    .html(txtarea)
                    .show();
                tinyMCE.remove();
                window.tinymce.dom.Event.domLoaded = true;
                tinymcesetup();
            }, 1000);
        }
    });
};

/*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 Logout
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
/**
 * Log out the user and trigger a modal window informing the user he/she has been logged out
 */
var logout = function(url) {
    $('.warningmsg').remove();
    jQuery.ajax({
        url: url,
        type: 'POST',
        success: function() {
            $('.mainbody').append("<div class='logoutWarning'>You have been logged out!</div>");
            $('.logoutWarning').fadeIn(200);
            setTimeout(function() {
                $('.logoutWarning')
                    .fadeOut(200)
                    .empty()
                    .hide();
                location.reload();
            },3000);
        }
    });
};


var session_timer = function() {
    jQuery.ajax({
        type: 'post',
        url: '../auth/login_duration',
        success: function(result) {
            var json = jQuery.parseJSON(result);
            console.log(json);
        }
    });
};

function animate_submenu(submenu_container, submenu) {
    if (submenu_container.is(':visible')) {
        submenu_container.animate({
                height: 'toggle'
            },
            {
                duration: 500,
                start: function() {
                    $('.submenu').hide();
                },
                complete: function() {
                    if (submenu !== undefined && submenu.length > 0) {
                        submenu.show();
                        submenu_container.animate({'height':'toggle'}, 500);
                    }

                }
            });
    }

}

$( document ).ready(function() {

    $('.mainbody')

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Cookie policy bar
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click', '#cookie_consent_btn', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            jQuery.ajax({
                url: url,
                type: 'post',
                success: function() {
                    $("#cookie_consent_container").slideToggle();
                }
            });
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Header menu/Sub-menu
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Dropdown menu
        .on('click','.menu',function() {
            var sideMenu = $('.sideMenu');
            if (!sideMenu.is(':visible')) {
                var overlay=$("<div id='lean_overlay'></div>");
                $("body").append(overlay);
                $('#lean_overlay')
                    .css({"display":"block",opacity:0})
                    .fadeTo(200,0.5);
                sideMenu.animate({width:'toggle'});
                $('main').animate({left:'200px'});
            } else {
                $('#lean_overlay').hide();
                sideMenu.animate({width:'toggle'});
                $('main').animate({left:'0px'});
            }
        })

        // Display sub menu
        .on('click','.submenu_trigger',function(e) {
            e.preventDefault();
            var menuEl = $(this).parent('li');
            var id = menuEl.attr('id');
            var submenu = $('.submenu#'+id);
            var submenu_container = $('header #sub_menu');
            var sideMenu = $('.sideMenu .submenu#'+id);

            if ($(this).closest('div').hasClass('sideMenu')) {
                if (sideMenu.length === 0)  {
                    menuEl.after(submenu);
                    $('.sideMenu .submenu#'+id).hide();
                }
                submenu.slideToggle();
            } else {
                if (!submenu.is(':visible')) {
                    animate_submenu(submenu_container, submenu);
                }
            }
            return false;
        })

        // Menu section
        .on('click',".menu_section",function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            var id = $(this).parent('li').attr('id');
            var split = id.split('/');
            var subMenu = $('.submenu#'+split[split.length-1]);
            var sideMenu = $('.sideMenu');
            $("li").removeClass("activepage");
            $("a").removeClass("activepage");

            if (!subMenu.is(':visible') && subMenu.length == 0 && $(this).closest('#sub_menu').length==0) {
                $('.submenu').hide();
            }

            // Mark current page as active in main menu
            $('li#'+split[0]).addClass("activepage");

            // Mark submenu section as active
            if (split.length>1) {
                $('li #'+split[1]).addClass("activepage");
            }

            loadPageContent(url, split[0]);

            if (sideMenu.is(':visible') && !$(this).hasClass('submenu_trigger')) {
                $('#lean_overlay').hide();
                sideMenu.animate({width:"toggle"});
                $('main').animate({left:0});
            }
            return false;
        })

        // Hide side menu when not clicked
        .on('click', function(e) {
            var menuBtn = $('.menu');
            var nav = $("nav");
            var sideMenu = $('.sideMenu');

            if (!menuBtn.is(e.target)&& menuBtn.has(e.target).length === 0) {
                if (sideMenu.is(':visible') && !sideMenu.is(e.target) && sideMenu.has(e.target).length === 0) {
                    sideMenu.animate({width:"toggle"});
                    $('main').animate({left:0});
                }
            }
        })

        .on('change', '.ajax_select', function(e) {
            e.preventDefault();
            var form = $(this).length > 0 ? $($(this)[0].form) : $();
            var url = form.attr('action');
            var data = form.serialize();
            jQuery.ajax({
                type: 'post',
                url: url,
                data: data,
                success: function() {
                    location.reload();
                }
            });
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Admin section
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // logout
        .on('click','.logout',function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            logout(url);
        })

        .on('click', '.show_customer_details', function() {
            tinymcesetup();
        })

        .on('click', '.modify_login', function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var callback = function(result) {
                console.log(result);
                if (result.status === true) {
                    logout('../auth/logout');
                }
            };
            processForm(form, callback);
        })

        .on('click', '.gen_sitemap', function(e) {
            e.preventDefault();
            var form = $(this).length > 0 ? $($(this)[0].form) : $();
            $('#sitemap_content').children().fadeOut();
            var callback = function(data) {
                $('#sitemap_content').html(data.content);
            };
            processForm(form, callback);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Rating widget
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click', '.input_rating', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var div = form.closest('.item_desc_ratings');
            var url = form.attr('action');
            var data = form.serializeArray();
            jQuery.ajax({
                type: 'post',
                url: url,
                data: data,
                success: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.status == true) {
                        div.html(json.content);
                    }
                }
            });
            return false;
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Collections
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('mouseover', '.item_desc_photo', function() {
            $(this).leanModal();
        })

        .on('click', '.item_desc_photo', function(e) {
            var url = $(this).find('img').attr('src');
            $('.modal_section#photo').html("<img src='"+url+"' class='picture_modal'>");
            show_section('photo');
        })

        .on('click', '.input_rating', function(e) {
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            processForm(form);
        })

        .on('click','.item_modify_icon', function() {
            $(this).closest('.leanModal').click();
        })

        .on('mouseover', '.delete', function() {
            $(this).leanModal();
        })

        .on('click','.delete', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var el = $('.modal_section#delete_confirmation');
            var id = $(this).data('id');
            var url = ($(this).attr('href') === undefined) ? $(this).data('url'):$(this).attr('href');
            var el_id = '.el_to_del#'+id;
            el.find('input').remove();
            el.append("<input type='hidden' name='url' value='"+url+"'/>");
            el.append("<input type='hidden' name='el_id' value='"+el_id+"'/>");
            show_section('delete_confirmation');
        })

        .on('click', '.confirm_delete', function() {
            var formid = $(".modal_section#delete_confirmation");
            var el_id = $('input[name="el_id"]').val();
            var url = $('input[name="url"]').val();
            var callback = function(result) {
                if (result.status === true) {
                    close_modal();
                    $(el_id).remove();
                }
                return false;
            };
            processAjax(formid, {}, callback, url);
        })

        .on('click', '.item_thumb', function() {
            $('.item_thumb').removeClass('item_thumb_selected');
            $(this).addClass('item_thumb_selected');
            var input = $('select[name="photo"]');
            if (input.length > 0 && input !== undefined) {
                input.val($(this).data('id'));
            }
        })

        .on('mouseover', '.item_thumb', function() {
            var url = $(this).find('img').attr('src');
            var dest = $('.item_desc_photo');
            dest.html('<img class="item_main_photo" src="'+url+'">');
        })

        // Add a category
        .on('click','.type_add',function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var name = form.find('input[name="name"]').val();
            var target_id = input.data('div');
            var target_div = $('.type_list#'+target_id);
            var callback = function(result) {
                if (result.status === true) {
                    if (target_div.find('.icon_container#'+name).length == 0) {
                        var html = result.content;
                        target_div.append(html);
                        close_modal();
                    }
                    form.find('input[name="name"]').empty();
                }
            };
            processForm(form, callback);
        })

        .on('change', '.filter_category', function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var url = form.attr('action');
            var dest_div = $('.type_list#items');
            var data = form.serializeArray();
            jQuery.ajax({
                type: 'post',
                url: url,
                data: data,
                success: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.status) {
                        dest_div
                            .hide()
                            .html(json.content)
                            .fadeIn(200);
                    }
                }
            })
        })

        .on('click', '.orders_headers', function(e) {
            e.preventDefault();
            var url = $(this).find('a').attr('href');
            var dest = $(this).closest('.filter_table_container');
            if (dest === undefined) {
                dest = $("#pagecontent");
            }

            jQuery.ajax({
                url: url,
                type: 'post',
                success: function(result) {
                    var json = jQuery.parseJSON(result);
                    dest.html(json.content);
                }
            });
        })

        .on('click', "#paging_menu a", function() {
            var url = $(this).attr('href');
            loadPageContent(url);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         POST submission
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click', '.load_edit', function(e) {
            e.preventDefault();
            var form = $(this).length > 0 ? $($(this)[0].form) : $();
            var data = form.serialize();
            var url = form.attr('action');
            jQuery.ajax({
                type: 'post',
                url: url,
                data: data,
                success: function(result) {
                    var json = jQuery.parseJSON(result);
                    $('.editcontent').html(json);
                    tinymcesetup();
                }
            });
        })

        .on('change', '.select_lang', function() {
            var lang = $(this).val();
            var url = $(this).data('url');
            var form = $(this).closest('.trad_form_container');
            jQuery.ajax({
                url: url+'/'+lang,
                type: 'post',
                success: function(result) {
                    var json = jQuery.parseJSON(result);
                    form.html(json);
                    tinymcesetup();
                }
            });
        })

        // Add a new post
        .on('click','.post_new',function(e) {
            e.preventDefault();
            showpostform(false);
        })

        .on('click', '.mail_preview', function() {
            var form = $(this).length > 0 ? $($(this)[0].form) : $();
            var data = form.serializeArray();
            var content = tinyMCE.get('message').getContent();
            data = modArray(data, 'message', content);
            jQuery.ajax({
                type: 'post',
                url: '../mail/preview',
                data: data,
                success: function(result) {
                    var json = jQuery.parseJSON(result);
                    $('.mail_preview_container')
                        .html(json)
                        .fadeIn();
                }
            });
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Language Menu
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Change language and refresh the current page
        .on('click', '.lang_trigger', function() {
            var url = $(this).attr('href');
            jQuery.ajax({
                url: url,
                type: 'post',
                success: function(result) {
                    window.location.href = jQuery.parseJSON(result);
                }
            });
        })

        .on('click', '.language_container #selected', function(e) {
            e.preventDefault();
            var position = $(this).position();
            var el = $('.language_container #option_list');
            var height = $(this).outerHeight();
            $(this).css({border:'1px solid rgba(50, 50, 50, 1)'});
            el.css({
                'position':'absolute',
                top: position.top+height+'px',
                'z-index': 999
            }).toggle();
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Tools
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // show tool description(homepage)
        .on('mouseenter','.tool_container',function(){
            $(this).find('.tool_desc').slideToggle(300);
        })

        .on('mouseleave','.tool_container',function(){
            $(this).find('.tool_desc').fadeOut(200);
        })
            
        // Add a tool
        .on('click','#modal_trigger_tool',function(e){
            e.preventDefault();
            displayToolForm(modalpubform);
        })

        // Modify tool
        .on('click','#modal_trigger_Modtool',function(e){
            e.preventDefault();
            e.stopPropagation();
            var toolName = $(this).attr('data-tool');
            displayToolForm(modalpubform,toolName);
        })

        // Add a tool
        .on('click','.toolformBtn',function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var callback = function(result) {
                if (result.status == true) {
                    setTimeout(function() {
                        displayToolForm(form,false);
                    },2000);
                } else {
                    showfeedback(result.msg);
                }
            };
            var data = form.serialize();
            processAjax(form,data,callback);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         CV
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Add/modify an element
        .on('click','#modal_trigger_cv',function(e){
            e.preventDefault();
            var cvid = $(this).attr('data-id');
            displayCvForm(modalpubform, cvid);
        })

        // Add a CV element
        .on('click','.cvformBtn',function(e) {
            e.preventDefault();
            var form = $(this).closest('#cvform');
            if (!checkform(form)) {return false;}
            var data = form.serialize();
            var callback = function(result) {
                if (result.status == true) {
                    setTimeout(function() {
                        var form = $('.modal_section#cv_form');
                        displayToolForm(form,false);
                    },2000);
                }
            };
            processAjax(form,data,callback);
        })

        // Remove CV element
        .on('click','.delcvEl',function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            var el = $(this).parent('.cvsubsection');
            var cvid = el.data('id');
            var data = {delcvEl:cvid};
            var callback = function(result) {
                if (result.status == true) {
                    $('#cvEl_'+cvid).remove();
                }
            };
            processAjax(el,data,callback,url);
        })

        // Remove CV element
        .on('click','.modcvEl',function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            console.log(url);
            var el = $(this).parent('.cvsubsection');
            var cvid = el.data('id');
            var data = {delcvEl:cvid};
            jQuery.ajax({
                url: url,
                type: 'post',
                success: function(data) {
                    var result = jquery.parseJSON(data);
                    if (result.status == true) {
                        $('#cvEl_'+cvid).remove();
                    }
                }
            });

        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         TinyMCE
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('mouseover', '.tinymce', function() {
            tinymcesetup();
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Click tracker
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.addClick',function(e) {
            var type = $(this).data('click');
            var name = $(this).data('id');
            var url = $(this).data('url');
            jQuery.ajax({
                url: url,
                type: 'POST',
                async: true,
                data: {
                    type:type,
                    name:name}
            });
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         PDF downloader
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click', '.downloadpdf', function(e) {
            e.preventDefault();
            var el = $(this);
            var btn = el.find('.Btn_cv_icon');
            var div = el.closest('.cv_generate');
            var pdfsrc = el.data('src');
            var controller = el.attr('href');
            var icon_content = btn.html();
            jQuery.ajax({
                url: pdfsrc,
                type: 'get',
                beforeSend: function() {
                    btn.html("<div class='dl_btn pub_btn icon_btn'><img src='../public/images/small_loader.gif'></div>");
                },

                success: function(data){
                    var json = jQuery.parseJSON(data);
                    jQuery.ajax({
                        url: controller,
                        type: 'POST',
                        data: {make_pdf: json.content},
                        complete: function() {
                            btn.html(icon_content);
                        },
                        success: function(data){
                            var result = jQuery.parseJSON(data);
                            div.after("<div class='cv_url' style='display: none;'>" +
                                "<a href='"+result+"' target='_blank'>PDF</a></div>");
                            div.next('.cv_url').animate({width:'toggle'});
                        }
                    });
                }
            });
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Login/Logout
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Login form
        .on('click',".login",function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var callback = function(result) {
                if (result.status === true) {
                    location.reload();
                }
            };
            processForm(form,callback);
        })

        .on('click', '.policies_toggle_content', function(e) {
            e.preventDefault();
            var id = $(this).closest('.policies_section').attr('id');
            var state = $(this).attr('id');
            if (state === 'on') {
                $(this).removeClass('toggle_on').addClass('toggle_off');
                $(this).attr('id','off');
            } else {
                $(this).removeClass('toggle_off').addClass('toggle_on');
                $(this).attr('id','on');

            }
            $('.policies_content#'+id).animate({height:'toggle'}, 500);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Modal dialog Events
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Show publication deletion confirmation
        .on('click',".delete_ref",function(e){
            e.preventDefault();
            var id_pres = $(this).attr("data-id");
            $(".modal_section#pub_delete").append('<input type=hidden id="del_pub" value="' + id_pres + '"/>');
            show_section('pub_delete');
        })

        // Confirm delete publication
        .on('click',"#confirm_pubdel",function(e) {
            e.preventDefault();
            var id_pres = $("input#del_pub").val();
            var data = {del_pub:id_pres};
            var el = $('.modal_section#pub_delete');
            var callback = function(result) {
                if (result.status == true) {
                    close_modal('.modalContainer');
                    $('#' + id_pres).remove();
                }
            };
            processAjax(el,data,callback);
        })

        // Going back to publication
        .on('click',".pub_back_btn",function(){
            show_section('submission_form');
        })

        .on('click','.back_btn',function(e) {
            e.preventDefault();
            show_section('user_login');
        })

        // Show download list
        .on('click','.dl_btn',function() {
            var menuEl = $(this);
            var height = menuEl.outerHeight();
            var absPos = menuEl.position();
            $(".dlmenu")
                .css({
                    'position': "absolute",
                    'left':absPos.left + "px",
                    'top':absPos.top + height + "px"
                })
                .toggle(200);
        });
});
