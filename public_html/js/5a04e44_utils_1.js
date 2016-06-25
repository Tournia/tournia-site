window.utils = {

    // Asynchronously load templates located in separate .html files
    loadTemplate: function(views, callback) {

        var deferreds = [];

        jQuery.each(views, function(index, view) {
            if (window[view]) {
                deferreds.push(
                    window[view].prototype.template = _.template( jQuery("#" + view ).html() )
                );
            } else {
                alert(view + " not found");
            }
        });

        jQuery.when.apply(null, deferreds).done(callback);
    },

    changePage: function() {
        if (location.hash !== '')
        {
            // Deactivate active page
            jQuery('#main-navbar ul.nav').find('li.active').each(function () {
                jQuery(this).removeClass("active");
            });
            jQuery('.content-tabs').find('.content-tab.active').each(function () {
                jQuery(this).removeClass("active");
            });
    
            // Activate active page
            var routeArray = location.hash.split("/");
            // The selected tab has to be the first arary element
            var selectedPage = routeArray[0];
            jQuery('.content-tabs').find('.content-tab' + selectedPage).addClass('active');
            jQuery('#main-navbar ul.nav').find('li a[href="' + selectedPage + '"]').parent('li').addClass('active')
        }
    },

    uploadFile: function (file, callbackSuccess) {
        var self = this;
        var data = new FormData();
        data.append('file', file);
        jQuery.ajax({
            url: 'api/upload.php',
            type: 'POST',
            data: data,
            processData: false,
            cache: false,
            contentType: false
        })
        .done(function () {
            console.log(file.name + " uploaded successfully");
            callbackSuccess();
        })
        .fail(function () {
            self.showAlert('Error!', 'An error occurred while uploading ' + file.name, 'alert-error');
        });
    },

    displayValidationErrors: function (messages) {
        for (var key in messages) {
            if (messages.hasOwnProperty(key)) {
                this.addValidationError(key, messages[key]);
            }
        }
        this.showAlert('Warning!', 'Fix validation errors and try again', 'alert-warning');
    },

    addValidationError: function (field, message) {
        var controlGroup = jQuery('#' + field).parent().parent();
        controlGroup.addClass('error');
        jQuery('.help-inline', controlGroup).html(message);
    },

    removeValidationError: function (field) {
        var controlGroup = jQuery('#' + field).parent().parent();
        controlGroup.removeClass('error');
        jQuery('.help-inline', controlGroup).html('');
    },

    showAlert: function(title, text, klass) {
        jQuery('.alert').removeClass("alert-error alert-warning alert-success alert-info");
        jQuery('.alert').addClass(klass);
        jQuery('.alert').html('<strong>' + title + '</strong> ' + text);
        jQuery('.alert').show();
    },

    hideAlert: function() {
        jQuery('.alert').hide();
    }

};