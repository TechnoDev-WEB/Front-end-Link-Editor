
/**
 * Front-end Link Editor
 *
 * Copyright (C) 2023, by TechnoDev
 *
 * Front-end Link Editor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or any later version.
 *
 * Front-end Link Editor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

(function ($) {

    let outerCurrentLink;

    $(document).ready(function () {
        let postID, index, currentLink, linkOnSection, timer, subtmitMessage;
        let urlField = $('#fle-link_field');
        let textField = $('#fle-link_text');
        subtmitMessage = $(".fle-modal .fle-form__show-message");

        if(phpVariables.fleIsSingular && phpVariables.fleIsUserLoggedIn && phpVariables.fleUserCanPost) {
            /**
             * Show edit element above link at hover event
             */
            $("[fle-post-id]").mouseenter(function() {
    
                clearTimeout(timer);
                currentLink = $(this);

                if(!currentLink.closest('[class^="so-widget"]').length) {
            
                    outerCurrentLink = currentLink; 
                    fleMoveElement(currentLink); 

                    let textField =  $(".fle-modal #fle-text");

                    if(currentLink.children().length > 1 || (currentLink.children().is('span') && (currentLink.text().charAt(0) !== currentLink.children().text().charAt(0))) || (currentLink.children().is('img'))) {
                        textField.hide();
                        linkOnSection = true;
                    } else {
                        textField.show();
                        linkOnSection = false;
                    }
                }
            }).mouseleave(function() {
              
                timer = setTimeout(function() {
                    $('.fle-edit-btn__wrapper').removeClass('show');
                  }, 3000);
            });

          /**
           * Actions for closing the form
           */
            $('.fle-form__close').click(function() {
                $('.fle-overlay').hide();
                $('.fle-form__wrapper').hide();
                subtmitMessage.css("display", "none");
            });

            /**
             * Actions at clicking on the edit button (populate the form fields with current link info, show form etc)
             */
            $(".fle-edit-btn__wrapper .fle-edit-btn").click(function() {

                clearTimeout(timer);

                $('.fle-overlay').show();
                $('.fle-modal .fle-form__wrapper').show();
                $('.fle-modal').show();
            
                subtmitMessage.text('');
                subtmitMessage.removeClass( 'fle-success');

                urlField.val(currentLink.attr('href'));
                textField.val(currentLink.text());
                index = currentLink.attr('fle-index');
                postID = currentLink.attr('fle-post-id');
                let checkBox = $('#fle-form #fle-target');

                if (currentLink.attr('target') === '_blank') {
                    checkBox.prop('checked', true);
                } else {
                    checkBox.prop('checked', false);
                }        
            });

            /**
             * Show loader at clicking on "Update link button"
             */
            $("#fle-link_button").click(function() {
                $('.fle-loader').show();
                });

            /**
             * Validate the form fields, handle the errors and the success messages, send data with ajax to php
             */

            $("#fle-form").validate({
                rules: {
                    'fle-link_field': {
                        required: true, 
                        regex: true
                    },
                    'fle-link_text': {
                        required: function(element) {
                            return linkOnSection == false;
                            }
                    }
                },
                messages: {
                    'fle-link_field': {
                        required: "*The field is required!",
                        regex: "*The url address is wrong!"
                    },
                    'fle-link_text': {
                        required: "*The field is required!",
                    } 
                },
                invalidHandler: function() {
                    messagesonSubmit("fle-error", "Fields filled in incorrectly");
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: phpVariables.fleAjaxUrl,
                        type: form.method,
                        data: {
                            'action': 'fle_update_database_link',
                            'nonce': phpVariables.nonce,
                            'fle_form_nonce': $('input[name="fle_form_nonce"]').val(),
                            'fle_link_text': $('#fle-link_text').val(),
                            'fle_link_url': $('#fle-link_field').val(),
                            'target': $('#fle-target').prop('checked'),
                            'index' : index,
                            'post_ID': postID,
                        },
                        success: function(response) {

                            messagesonSubmit("fle-success", response.data);
                    
                            currentLink.attr('href', $('#fle-link_field').val().trim());
        
                            if(!linkOnSection) {
                                currentLink.text($('#fle-link_text').val().trim());
                            }
        
                            $('#fle-target').prop('checked') ?  currentLink.attr('target', '_blank') :  currentLink.removeAttr('target');    
                        },
                        error: function (response) {
                            subtmitMessage.addClass('fle-error');
                            subtmitMessage.text(response.data);
                        },
                        complete: function(){
                            $('.fle-loader').hide();
                        },
                    });
                },
                highlight: function (element) {
                    $(element).parent().addClass('has-error');
                    $('.fle-loader').hide();
                },
                 unhighlight: function (element) {
                     $(element).parent().removeClass('has-error');
                }
            });
            
              /**
             * Add validate rule for validating link
             */
              $.validator.addMethod(
                "regex",
                function (value, element, regexp) {
                    let re = new RegExp(regexp);
                    return this.optional(element) || re.test(value);
                });
                $("#fle-link_field").rules("add", { regex: "^(https?://([a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9].)+[a-zA-Z]{2,}(/.*)*|(www.)+[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9].[a-zA-Z]{2,}(/.*)*)$"});
        }
    });

    /**
     * Function to display messages on the top of the form success/error
     */
    function messagesonSubmit(type, response) {
        let message =  $(".fle-modal .fle-form__show-message");

        if(type === "fle-error") {
            reverseType = "fle-success";
        } else {
            reverseType = "fle-error";
        }
        message.hasClass(reverseType) ? message.removeClass(reverseType) : null;
        message.addClass(type);
        message.text(response);
        message.css("display", "flex");
    }

    /**
     * Move editor button at resizing the window
     */
    $(window).resize(function() {
         fleMoveElement(outerCurrentLink);
    });

      /**
     * Move editor button function
     */
    function fleMoveElement(currentLink) {      
        
        let editButtonPos;
        if(currentLink.children().is('img') ) {
            editButtonPos = currentLink.find("img").offset();
            let oldpositon = editButtonPos.left;
            editButtonPos.left = (currentLink.find("img").width())/2 + oldpositon - 17;
        } else {
            let firstWord = currentLink.text().split(" ")[0];
            let firstLetterPos = currentLink.find("span:contains(" + firstWord[0] + ")");

            if (firstLetterPos.length <= 0) {
                let index = firstWord.indexOf(firstWord[0]); //index will be 0
                firstLetterWrapped = "<span>" + firstWord[index] + "</span>";
                firstLetterPos = currentLink.html(currentLink.text().trim().replace(firstWord[0], firstLetterWrapped)).find("span").offset();
            } else {
                firstLetterPos = currentLink.find("span:contains(" + firstWord[0] + ")").offset();
            }
            editButtonPos = firstLetterPos;
        }

        let $editbutton = $('.fle-modal .fle-edit-btn__wrapper')
        $editbutton.css({
            'top' :  editButtonPos.top-33,
            'left' :  editButtonPos.left
        }); 
        $editbutton.addClass("show");
    }  
})(jQuery);

