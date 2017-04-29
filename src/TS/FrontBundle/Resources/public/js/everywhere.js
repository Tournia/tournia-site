// Google analytics
if (typeof hideGoogleAnalytics == "undefined") {
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-39349997-1', 'tournia.net');
  ga('send', 'pageview');
}

// placeholder support in internet explorer
// Source https://github.com/parndt/jquery-html5-placeholder-shim/blob/master/jquery.html5-placeholder-shim.js
(function($) {
  $.extend($,{ placeholder: {
      browser_supported: function() {
        return this._supported != "undefined" ?
          this._supported :
          ( this._supported = !!('placeholder' in $('<input type="text">')[0]) );
      },
      shim: function(opts) {
        var config = {
          color: '#888',
          cls: 'placeholder',
          selector: 'input[placeholder], textarea[placeholder]'
        };
        $.extend(config,opts);
        return !this.browser_supported() && $(config.selector)._placeholder_shim(config);
      }
  }});

  $.extend($.fn,{
    _placeholder_shim: function(config) {
      function calcPositionCss(target)
      {
        var op = $(target).offsetParent().offset();
        var ot = $(target).offset();

        return {
          top: ot.top - op.top,
          left: ot.left - op.left,
          width: $(target).width()
        };
      }
      function adjustToResizing(label) {
      	var $target = label.data('target');
      	if(typeof $target != "undefined") {
          label.css(calcPositionCss($target));
          $(window).one("resize", function () { adjustToResizing(label); });
        }
      }
      return this.each(function() {
        var thisVar = $(this);

        if( thisVar.is(':visible') ) {

          if( thisVar.data('placeholder') ) {
            var olVar = thisVar.data('placeholder');
            olVar.css(calcPositionCss(thisVar));
            return true;
          }

          var possible_line_height = {};
          if( !thisVar.is('textarea') && thisVar.css('height') != 'auto') {
            possible_line_height = { lineHeight: thisVar.css('height'), whiteSpace: 'nowrap' };
          }

          var ol = $('<label />')
            .text(thisVar.attr('placeholder'))
            .addClass(config.cls)
            .css($.extend({
              position:'absolute',
              display: 'inline',
              cssFloat: 'none',
              styleFloat: 'none',
              overflow:'hidden',
              textAlign: 'left',
              color: config.color,
              cursor: 'text',
              paddingTop: thisVar.css('padding-top'),
              paddingRight: thisVar.css('padding-right'),
              paddingBottom: thisVar.css('padding-bottom'),
              paddingLeft: thisVar.css('padding-left'),
              fontSize: thisVar.css('font-size'),
              fontFamily: thisVar.css('font-family'),
              fontStyle: thisVar.css('font-style'),
              fontWeight: thisVar.css('font-weight'),
              textTransform: thisVar.css('text-transform'),
              backgroundColor: 'transparent',
              zIndex: 99
            }, possible_line_height))
            .css(calcPositionCss(this))
            .attr('for', this.id)
            .data('target',thisVar)
            .click(function(){
              $(this).data('target').focus();
            })
            .insertBefore(this);
          thisVar
            .data('placeholder',ol)
            .focus(function(){
              ol.hide();
            }).blur(function() {
              ol[thisVar.val().length ? 'hide' : 'show']();
            }).triggerHandler('blur');
          $(window).one("resize", function () { adjustToResizing(ol); });
        }
      });
    }
  });
})(jQuery);

jQuery.support.placeholder = (function(){
    var i = document.createElement('input');
    return 'placeholder' in i;
})();

$(document).ready(function() {
    if (jQuery.placeholder && !$.support.placeholder) {
        jQuery.placeholder.shim();
    }

    //Nav search
    $('[data-toggle="nav-search"]').on('click', function(e)
    {
        e.preventDefault();

        $('#site-head .navbar-search').show();
        $('#site-head .navbar-search input').focus();
    });
    // Hide search on focus out
    $('#site-head .navbar-search input').on('focusout', function(e)
    {
        $('#site-head .navbar-search').hide();
    });
});

function showInfoInForm() {
    // show info after form element
    $('.infoIcon').popover({
        html: true,
        trigger: 'hover',
        placement: 'left',
    });
}
showInfoInForm();

function setupPopovers() {
    $("[data-toggle='popover']").popover({
        trigger: 'hover',
        html: true
    });
}
setupPopovers();

// remove form-control class from checkbox
$(":input[type='checkbox']").removeClass('form-control');

// Datatables addition to be able to switch loading indicator on/off
// Example use: $('#tableName').dataTable().fnProcessingIndicator() or $('#tableName').dataTable().fnProcessingIndicator(false)
if (typeof jQuery.fn.dataTableExt != "undefined") {
    jQuery.fn.dataTableExt.oApi.fnProcessingIndicator = function ( oSettings, onoff ) {
        if(typeof(onoff)=='undefined') {
            onoff = true;
        }
        this.oApi._fnProcessingDisplay( oSettings, onoff );
    }
}

/**
 * Get parameter in url
 * @returns null when $key is not in url, otherwise the value of $key in url
 */
function getUrlParam(key) {
    var sURL = window.document.URL.toString();
    if (sURL.indexOf("?") > 0) {
        var arrParams = sURL.split("?");
        var arrURLParams = arrParams[1].split("&");
        var arrParamNames = new Array(arrURLParams.length);
        var arrParamValues = new Array(arrURLParams.length);
        var i = 0;
        for (i=0;i<arrURLParams.length;i++) {
            var sParam =  arrURLParams[i].split("=");
            arrParamNames[i] = sParam[0];
            if (sParam[1] != "") {
                arrParamValues[i] = unescape(sParam[1]);
            } else {
                arrParamValues[i] = ""; // no value
            }
        }

        for (i=0;i<arrURLParams.length;i++) {
            if (arrParamNames[i] == key){
                return arrParamValues[i];
            }
        }
        return null;
    }
}

/**
 * Set parameter in url, without reloading page
 */
function setUrlParam(key, value) {
    // turn hashchange detection off
    $(window).off('hashchange');

    key = encodeURI(key);
    value = encodeURI(value);

    questionmarkIndex = document.location.hash.indexOf("?");
    var kvp;
    var hashWithoutParams;
    if (questionmarkIndex == -1) {
        // no questionmark
        kvp = new Array();
        hashWithoutParams = document.location.hash +"?";
    } else {
        var kvp = document.location.hash.substr(questionmarkIndex+1).split('&');
        hashWithoutParams = document.location.hash.substr(0, questionmarkIndex+1);
    }

    var i = kvp.length;
    var x;
    while (i--) {
        x = kvp[i].split('=');

        if (x[0]==key) {
            x[1] = value;
            kvp[i] = x.join('=');
            break;
        }
    }

    if (i<0) {
        kvp[kvp.length] = [key,value].join('=');
    }

    // change hash location
    window.location.hash = hashWithoutParams + kvp.join('&');

    // turn hashchange detection back on
    setTimeout(function() {
        $(window).on('hashchange', function() {
            checkURL();
        })
    }, 300);
}

