
// This sorting plug-in allows for HTML tags with numeric data. 
// With the 'html' type it will strip the HTML and then sorts by strings, with this type it strips the HTML and then sorts by numbers
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
    "num-html-pre": function ( a ) {
        var x = String(a).replace( /<[\s\S]*?>/g, "" );
        return parseFloat( x );
    },
 
    "num-html-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "num-html-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );


function replaceLinksForNavigation() {
    // replace links to include hash (#) in href for ajax loading
    $("a:not(.notReplaceLink)").each(function() {
        currentLink = $(this).attr("href");
        $(this).attr("href", convertUrlToHash(currentLink));
    });

    // do the same for form action
    $("form:not(.notReplaceLink)").each(function() {
        // prevent submitting it the normal way, but instead use postUrl
        $(this).off("submit");
        $(this).on("submit", function (e) {
            e.preventDefault();
            postURL($(this).attr("action"), $(this));
        });
    });
}

/**
  * Called after loading a page, and also the index page
  * @param boolean isIndex Whether the index page is loaded (true) or a site page (false)
  */
function afterPageLoad(isIndex) {
    if (!isIndex) {
        // set title
        pageTitle = ($("#pageTitle").text());
        title = ($('nav a[href="' + location.hash + '"]').text());
        newTitle = (pageTitle || title || document.title);
        document.title = newTitle;
        $(".page-title").html('<i class="fa fa-bookmark-o fa-fw "></i> '+ newTitle);

        // set datatables
        if (typeof setDatatableReady != "undefined") {
            setDatatableReady();
        }

        showInfoInForm();

        // initialize ckeditor
        $(".ckeditor").each(function() {
            CKEDITOR.replace($(this).attr("id"));
        })

        // prevent rendering of sparksline in heading twice
        $("#sparks .sparkline").each(function() {
            $(this).addClass("sparklineRendered").removeClass("sparkline");
        });
    }

    pageSetUp();

    if (!isIndex) {
        // prevent rendering of sparksline in heading twice, putting class back
        $("#sparks .sparklineRendered").each(function() {
            $(this).removeClass("sparklineRendered").addClass("sparkline");
        });
    }

    replaceLinksForNavigation();

    // clean search field
    $("#navSearch").val("");

    // check for all pages with tabs and errors
    $(".tab-content .form-group.has-error").each(function() {
        // for form elements with errors -> show error in tab
        tabId = $(this).closest(".tab-pane").attr("id");
        $(".nav-tabs [href='#"+ tabId +"']").parent().addClass("formError");
        $(this).closest(".panel").find(".panel-heading").addClass("formError")
    });
    $(".nav-tabs .formError, .tab-content .formError").each(function() {
        $(this).find("a").prepend('<i class="fa fa-warning"></i> ');
    });

    // set link in navigation to active
    $("#left-panel li.active").removeClass("active");
    $('#left-panel a[href="' + location.hash + '"]').closest("li").addClass("active");
    
    setupPopovers();
}

/**
  * Similar to loadURL, but then for POST actions
  * @param url The URL to post, already in hash form (see convertUrlToHash() )
  * @param formReference reference to jQuery object of the form to send
  */
function postURL(url, formReference) {
    onBeforeLeavingPage();

    container = $('#content');

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
            // cog placed
            container.html('<h1><i class="fa fa-cog fa-spin"></i> '+ Translator.trans('page.loading') +'</h1>');

            // Only draw breadcrumb if it is main content material
            // TODO: see the framerate for the animation in touch devices
            if (container[0] == $("#content")[0]) {
                drawBreadCrumb();
                // update title with breadcrumb...
                document.title = $(".breadcrumb li:last-child").text();
                // scroll up
                $("html, body").animate({
                    scrollTop : 0
                }, "fast");
            } else {
                container.animate({
                    scrollTop : 0
                }, "fast");
            }
        },
        success : function(data) {
            container.css({
                opacity : '0.0'
            }).html(data).delay(50).animate({
                opacity : '1.0'
            }, 300);
        },
        error : function(jqXHR, textStatus, errorThrown) {
            container.html('<h4 style="margin-top:10px; display:block; text-align:left"><i class="fa fa-warning txt-color-orangeDark"></i> '+ Translator.trans('page.error') +' '+ textStatus +'! '+ errorThrown +'.</h4>');
        },
        async : false
    });
}


// Redefine of function in app.js, because of non-accessibility of method
function notification_check() {
    $this = $('#activity > .badge');

    if (parseInt($this.text()) > 0) {
        $this.addClass("bg-color-red bounceIn animated")
    }
}

// after opening of activity messages -> mark all messages as read
function markAllMessagesAsRead() {
    if ($('#activity').hasClass("active")) {
        // mark all messages as read
        $("#lastMessages li span.unread").each(function() {
            $(this).removeClass("unread");
        });
    }
}

// code to execute before leaving a page
function onBeforeLeavingPage() {
    ajaxDataTableOptions = {};
    roundRefreshAfterUpdate = false;

    getRefreshCommands = function(){
        return new Array();
    }
}

/**
 * Open the edit match modal
 * @param matchId The match that is edited. Info of match will be retrieved from API. When matchId==0 a new match can be created.
 */
function editMatch(matchId) {
    // open modal to edit match
    // by default, empty fields
    $("#editMatchModal :input").val('');
    $("#editMatchPrioritize").prop('checked', false);
    $("#editMatchPool").val($("#poolSelector").val());
    $("#editMatchRound").val($("#roundSelector").val());

    // show/hide non-ready reason
    $("#editMatchNonreadyReasonBox").hide();
    $("#editMatchModal #editMatchStatus").off("change").on('change', function() {
        if ($(this).val() == "postponed") {
            $("#editMatchNonreadyReasonBox").slideDown('slow');
        } else {
            $("#editMatchNonreadyReasonBox").slideUp('slow');
        }
    });

    if ($("#editMatchPool option").size() == 0) {
        // no pools available -> update
        updatePoolSelector(function() {
            $("#editMatchPool").val($("#editMatchPoolSelected").val());
        });
    }

    if (matchId == 0) {
        // create new match
        $("#editMatchLocalId").closest(".form-group").hide();
        $("#editMatchDelete").hide();
        $("#editMatchStatus").closest(".form-group").hide();
        $("#editMatchScore").closest(".form-group").hide();
        $("#editMatchSaveButton").html('<i class="fa fa-plus"></i> '+ Translator.trans('matchModal.create.submit'));
        $("#editMatchModal .modal-title").html(Translator.trans('matchModal.create.title'));

        $("#editMatchTeam1").val('');
        $("#editMatchTeam2").val('');
        $("#editMatchPrioritize").prop('checked', false);

        updateTeamSelector($("#poolSelector").val(), true);
    } else {
        // edit existing match
        $("#editMatchLocalId").closest(".form-group").show();
        $("#editMatchDelete").show();
        $("#editMatchStatus").closest(".form-group").show();
        $("#editMatchScore").closest(".form-group").show();
        $("#editMatchSaveButton").html(Translator.trans('matchModal.edit.submit'));
        $("#editMatchModal .modal-title").html(Translator.trans('matchModal.edit.title'));

        // get match data
        command = {
            command : 'Matches.get',
            matchId: matchId,
            setCommandKey: 'editMatchData',
        }
        addServerCommand(command);
        sendServerCommands(false);
    }
    $("#editMatchId").val(matchId);
    $("#editMatchModal").modal("show");
}

/**
 * Open the edit match score modal
 * @param matchId The match that is edited. Info of match will be retrieved from API.
 */
function openScoreMatch(matchId) {
    command = {
        command : 'Matches.get',
        matchId: matchId,
        setCommandKey: 'scoreMatchData',
    }
    addServerCommand(command);
    sendServerCommands(false);
    $("#scoreMatchModal").modal('show');
}

/**
 * Update the team selectors during editing of a match
 * @param poolId Pool which is used to request team selectors
 * @param boolean sendServer Whether to sendServerCommands immediately, or only put in queue
 */
function updateTeamSelector(poolId, sendServer) {

    command = {
        command : 'Teams.list',
        poolId: poolId,
        setCommandKey: 'editMatchTeamSelector',
    }
    addServerCommand(command);
    if (sendServer) {
        sendServerCommands(false);
    }
}

/**
 * Enable nonreadyReason tooltip
 * @param identifier The identifier in which the .nonreadyReasonTooltip resides
 * @param callbackChangedFunction Function that is called when reason is changed as callbackChangedFunction(objectId, newReason)
 */
function enableNonreadyReasonTooltip(identifier, callbackChangedFunction) {
    $(identifier + ' .nonreadyReasonTooltip').popover({
        html : true,
        trigger: "click",
        title: Translator.trans('nonready.reason') +":",
        content: function() {
            tooltipHtml = '<span class="nonreadyReasonOriginal">'+ $(this).attr('data-reason') +'</span><textarea class="nonreadyReasonTextarea form-control">'+ $(this).attr('data-reason') +'</textarea>';
            return tooltipHtml;
        }
    }).on("hide.bs.popover", function(e) {
        newReason = $(this).next().find(".nonreadyReasonTextarea").val();
        if (newReason != $(this).next().find(".nonreadyReasonOriginal").text()) {
            // reason changed
            objectId = $(this).attr("data-objectId");
            callbackChangedFunction(objectId, newReason);
        }
    });
}

/**
 * Process general information from server API data
 * @param commandKey
 * @param commandResponse
 */
function controlHandleResponse(commandKey, commandResponse) {
    if (commandKey == "editMatchData") {
        // update team selector
        updateTeamSelector(commandResponse['poolId'], true);

        // update rounds
        updateRoundSelector(commandResponse['poolId'], false, function() {
            $("#editMatchRound").val(commandResponse['round']);
        });

        // fill in match data for editing
        $("#editMatchLocalId").val(commandResponse['localId']);
        $("#editMatchPool").val(commandResponse['poolId']);
        $("#editMatchPoolSelected").val(commandResponse['poolId']);
        $("#editMatchRound").val(commandResponse['round']);
        $("#editMatchTeam1").val(commandResponse['team1Id']);
        $("#editMatchTeam1Selected").val(commandResponse['team1Id']);
        $("#editMatchTeam2").val(commandResponse['team2Id']);
        $("#editMatchTeam2Selected").val(commandResponse['team2Id']);
        $("#editMatchPrioritize").prop('checked', commandResponse['priority']);

        $("#editMatchStatus option[value='playing']").remove();
        if (commandResponse['status'] == "playing") {
            // add option for playing
            $("#editMatchStatus option[value='ready']").after('<option value="playing">Playing</option>');
        }
        $("#editMatchStatus, #editMatchStatusOriginal").val(commandResponse['status']);
        $("#editMatchScore").text(commandResponse['score']);
        if (commandResponse['score'] == '') {
            score = Translator.trans('matchModal.noScore');
        } else {
            score = commandResponse['scoreText'];
        }
        $("#editMatchScore").html('<button type="button" class="btn btn-link btnNoShadow" onClick="$(\'#editMatchModal\').modal(\'hide\');openScoreMatch(\''+ commandResponse['id'] +'\');">'+ score +'</button>');
        if ((commandResponse['status'] == "ready") || (commandResponse['status'] == "postponed")) {
            // only show prioritize option when match hasn't been played yet
            $("#editMatchPrioritize").parent().parent().show();
        } else {
            $("#editMatchPrioritize").parent().parent().hide();
        }

        if (commandResponse['status'] == "postponed") {
            // only show non-ready reason when match is postponed
            $("#editMatchNonreadyReasonBox").show();
            $("#editMatchNonreadyReason").val(commandResponse['nonreadyReason']);
            $("#editMatchNonreadyReasonOriginal").val(commandResponse['nonreadyReason']);
        } else {
            $("#editMatchNonreadyReasonBox").hide();
        }

    } else if (commandKey == "editMatchTeamSelector") {
        $("#editMatchTeam1, #editMatchTeam2").html('');
        $("#editMatchTeam1, #editMatchTeam2").append('<option value=""></option>');
        $.each(commandResponse, function(teamId, teamObject) {
            optionHtml = '<option value="'+ teamId +'">'+ teamObject['name'] +'</option>';
            $("#editMatchTeam1, #editMatchTeam2").append(optionHtml);
        });

        // team1 and team2 already requested via API when commandKey == "editMatchData"
        if ($("#editMatchTeam1Selected").val() != "") {
            $("#editMatchTeam1").val($("#editMatchTeam1Selected").val());
        }
        if ($("#editMatchTeam2Selected").val() != "") {
            $("#editMatchTeam2").val($("#editMatchTeam2Selected").val());
        }
    } else if (commandKey == "scoreMatchData") {
        // for editing score screen, set data
        scoreCommandResponse(commandResponse);
    }
}

$(document).ready(function() {
    replaceLinksForNavigation();

    // Specific code for edit match modal
    $("#editMatchPool").on("change", function() {
        // on change of pool selection -> change selection of teams
        updateTeamSelector($(this).val(), true);
    });
    $("#editMatchSaveButton").on("click", function() {
        // Save edited or new match
        matchId = $("#editMatchId").val();
        poolId = $("#editMatchPool").val();
        round = $("#editMatchRound").val();
        team1 = $("#editMatchTeam1").val();
        team2 = $("#editMatchTeam2").val();
        if (matchId == 0) {
            // create new match
            command = {
                command : 'Matches.create',
                poolId: poolId,
                round: round,
                team1: team1,
                team2: team2,
                priority: $("#editMatchPrioritize").prop('checked'),
            }
        } else {
            // edit existing match
            command = {
                command : 'Matches.edit',
                matchId: matchId,
                localId: $("#editMatchLocalId").val(),
                poolId: poolId,
                round: round,
                team1: team1,
                team2: team2,
                priority: $("#editMatchPrioritize").prop('checked'),
            }
        }
        addServerCommand(command);
        if ((matchId != 0) && (($("#editMatchStatusOriginal").val() != $("#editMatchStatus").val()) || ($("#editMatchNonreadyReasonOriginal").val() != $("#editMatchNonreadyReason").val()))  ) {
            // changed status of match or non-ready reason (for existing match)
            command = {
                command : 'Matches.setStatus',
                matchId: matchId,
                status: $("#editMatchStatus").val(),
            }
            if ($("#editMatchStatus").val() == "postponed") {
                command.nonreadyReason = $("#editMatchNonreadyReason").val();
            }
            addServerCommand(command);
        }
        sendServerCommands(true);
        $("#editMatchModal").modal("hide");
    });
    $("#editMatchDelete").on("click", function() {
        // delete a match
        command = {
            command : 'Matches.remove',
            matchId: $("#editMatchId").val(),
        }
        addServerCommand(command);
        sendServerCommands(true);
        $("#editMatchModal").modal("hide");
    });

    // stuff for search in navigation
    $(document).keypress(function(e) {
        if (e.altKey && (e.which === 402 || e.which == 223)) {
            // alt-s or alt-f is pressed -> focus search
            // todo: make it work for windows as well
            $("#navSearch").focus();
        }
    });

    // show notification messages / settings
    $('.ajax-dropdown input[name="showNotification"]').change(function() {
        if ($(this).attr('id') == 'showNotificationMessages') {
            // show notification messages
            $(".ajax-dropdown #lastMessages").show();
            $(".ajax-dropdown #notificationSettings").hide();
        } else if ($(this).attr('id') == 'showNotificationSettings') {
            // show notification settings
            $(".ajax-dropdown #lastMessages").hide();
            $(".ajax-dropdown #notificationSettings").show();

            // set checkboxes
            $('.ajax-dropdown #notificationSettingsMe').prop('checked', (localStorage.getItem("notificationSettingsMe") == 'true'));
            $('.ajax-dropdown #notificationSettingsOtherPerson').prop('checked', (localStorage.getItem("notificationSettingsOtherPerson") == 'true'));
            $('.ajax-dropdown #notificationSettingsRound').prop('checked', (localStorage.getItem("notificationSettingsRound") == 'true'));
            console.log("1");
            console.log(localStorage.getItem("notificationSettingsMe"));
        }
    });
    if (localStorage.getItem("notificationSettingsMe") == null) {
        localStorage.setItem("notificationSettingsMe", true);
    }
    if (localStorage.getItem("notificationSettingsOtherPerson") == null) {
        localStorage.setItem("notificationSettingsOtherPerson", false);
    }
    if (localStorage.getItem("notificationSettingsRound") == null) {
        localStorage.setItem("notificationSettingsRound", true);
    }
    $('.ajax-dropdown #showNotificationMessages').trigger('click');


    $('.ajax-dropdown #notificationSettingsMe').change(function() {
        localStorage.setItem("notificationSettingsMe", $(this).prop('checked'));
    });
    $('.ajax-dropdown #notificationSettingsOtherPerson').change(function() {
        localStorage.setItem("notificationSettingsOtherPerson", $(this).prop('checked'));
    });
    $('.ajax-dropdown #notificationSettingsRound').change(function() {
        localStorage.setItem("notificationSettingsRound", $(this).prop('checked'));
    });

    // check frequently for updates messages
    checkForUpdates(10000);

    // add active class to header
    $("header .controlActive").addClass("active");
});
