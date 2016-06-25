container = $('#settingsContent');

/**
  * Load a page in an ajax way, and put it in the container
  */
function loadPage(url) {
    $.ajax({
        type : "GET",
        url : url,
        dataType : 'html',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend : function() {
            // loading icon placed
            container.html('<h1><i class="fa fa-cog fa-spin"></i> Loading...</h1>');

            container.animate({
                scrollTop : 0
            }, "fast");
        },
        success : function(data) {
            container.css({
                opacity : '0.0'
            }).html(data).delay(50).animate({
                opacity : '1.0'
            }, 300);
        },
        error : function(jqXHR, textStatus, errorThrown) {
            container.html('<h4><i class="fa fa-warning"></i> Error '+ textStatus +'! '+ errorThrown +'.</h4>');
        },
        async : false
    });
}


/**
  * Replace the submit button in the form, so will be executed in an ajax-way
  */
function replaceSubmitAjax() {
    // do the same for form action
    $(container).find("form:not(.notReplaceLink)").each(function() {
        // prevent submitting it the normal way, but instead use postAjax
        $(this).off("submit");
        $(this).on("submit", function (e) {
            e.preventDefault();
            postURL($(this).attr("action"), $(this));
        });
    });
}


/**
  * Posts a form with ajax. Puts result in container.
  * @param url The URL to post
  * @param formReference reference to jQuery object of the form to send
  */
function postAjax(url, formReference) {
    // save value of ckeditor in textarea
    if (typeof CKEDITOR != "undefined") {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }

    var formData = new FormData($(formReference)[0]);
    $.ajax({
        type : "POST",
        url : url,
        dataType : 'html',
        data: formData,

        cache: false,
        contentType: false,
        processData: false,
        beforeSend : function() {
            // loading icon placed
            container.html('<h1><i class="fa fa-cog fa-spin"></i> Loading...</h1>');

            container.animate({
                scrollTop : 0
            }, "fast");
        },
        success : function(data) {
            container.css({
                opacity : '0.0'
            }).html(data).delay(50).animate({
                opacity : '1.0'
            }, 300);
        },
        error : function(jqXHR, textStatus, errorThrown) {
            container.html('<h4><i class="fa fa-warning"></i> Error '+ textStatus +'! '+ errorThrown +'.</h4>');
        },
        async : false
    });
}
