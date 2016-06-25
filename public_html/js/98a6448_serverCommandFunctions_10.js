////////// Functionality for exchanging data with server //////////////

///// Saving serverCommands ///////
var serverCommands = {
    commands: {},
}; // commands that are send to server
var newCommandNr = 0;
function addServerCommand(command) {
	serverCommands.commands[newCommandNr] = command;
	newCommandNr += 1;
}

var lastUpdateId = 0; // last id of previous update message (so that it won't be displayed again)
var observerUpdateSection = new Array(); // if there is a change in any of these sections, this page must reload its data
//// sending server commands ////
function sendServerCommands(refreshData) {
	showLoadingNotification(true);
	if (lastUpdateId != 0) {
		serverCommands.lastUpdateId = lastUpdateId;
	}
	if (refreshData && (typeof getRefreshCommands == 'function')) {
		refreshCommands = getRefreshCommands();
		for (var i = 0; i < refreshCommands.length; i++) {
			addServerCommand(refreshCommands[i]);
		}
	}
	$.ajax({
		type: 'POST',
		cache: false,
		data: serverCommands,
		url: urlApiCommand,
		dataType: 'json',
		success: function(data) {
			showOfflineMessage(false);
			showLoadingNotification(false);
			preHandleResponse(data);
			if (typeof data['messages'] != "undefined") {
				// show notification messages, one by one
				addMessagesNotifications(data['messages']);	
			}
		},
		statusCode: {
		    403: function() {
		    	alert("You have been signed out, please log in again");
		    	window.location.href = urlFrontLogin;
		    },
            0: function() {
                showOfflineMessage(true);
            },
		},
	});
	serverCommands = {commands: {}}; // reset serverCommands
	newCommandNr = 0;
}

websocketConnection = false;
// requesting update of messages from server
function requestUpdateMessages() {
	if (!websocketConnection) {
		url = urlApiUpdates +'?lastUpdateId='+ lastUpdateId;
		$.ajax({
			type: 'GET',
			cache: false,
			url: url,
			success: function(data) {
				showOfflineMessage(false);
				preHandleResponse(data);
				if (typeof data['messages'] != "undefined") {
					// show notification messages
					addMessagesNotifications(data['messages']);
				}
			},
			statusCode: {
				403: function() {
					alert("You have been signed out, please log in again");
					window.location.href = urlFrontLogin;
				},
				0: function() {
					showOfflineMessage(true);
				},
			},
		});
	}
}

function checkForUpdates(frequencyForPolling) {
	setInterval(requestUpdateMessages, frequencyForPolling);

	var webSocket = WS.connect(webSocketUri);
	webSocket.on("socket/connect", function(session){
		websocketConnection = true;
		showOfflineMessage(false);

		//session is an Autobahn JS WAMP session.
		console.log("Successfully connected to websocket");
		//session.publish("tournament/def", {msg: "This is a message!"});

		//the callback function in "subscribe" is called everytime an event is published in that channel.
		session.subscribe("tournament/"+ tournamentUrl, function(uri, payload){
			//console.log("Received message: ", payload.msg);
			messageJson = $.parseJSON(payload.msg);
			if (typeof messageJson['loginAccountName'] != "undefined") {
				if (messageJson['loginAccountId'].toString() == loginAccountId) {
					messageJson['origin'] = "me";
					messageJson['text'] = "You "+ messageJson['text'];
				} else {
					messageJson['origin'] = "otherPerson";
					messageJson['text'] = messageJson['loginAccountName'] +" "+ messageJson['text'];
				}
			} else {
				messageJson['origin'] = "otherPerson";
				messageJson['text'] = "Anonymous Live "+ messageJson['text'];
			}
			messageArray = [messageJson];

			addMessagesNotifications(messageArray);

			// requesting new data from server
			sendServerCommands(true);
		});
	})

	webSocket.on("socket/disconnect", function(error){
		if (websocketConnection) {
			showOfflineMessage(true);
		}
		websocketConnection = false;

		//error provides us with some insight into the disconnection: error.reason and error.code
		console.log("Disconnected for " + error.reason + " with code " + error.code);
	})
}

// Process general information from server json data
function preHandleResponse(data) {
	shouldFetchDataFromServer = false;
	
	// retrieving commands for refreshing of data
	refreshCommandsUpdate = new Array();
	if (typeof getRefreshCommands == 'function') {
		refreshCommands = getRefreshCommands();
		for (var i = 0; i < refreshCommands.length; i++) {
			refreshCommandsUpdate.push(refreshCommands[i].command);
		}
	}

	if ((typeof data['messages'] != "undefined") && (data['messages'].length > 0)) {
		$.each(data['messages'], function(messageId, messageObject) {
			// if the data doesn't include new data and this page listens to the updateSection -> reload data
			//if ((messageObject['type'] == 'success') && (type data['data'] == "undefined") && ((messageObject['updateSection'] == 'all') || (observerUpdateSection.length == 0) || ($.inArray(messageObject['updateSection'], observerUpdateSection) >= 0))) {
			if (messageObject['type'] == 'success') {
				// reload data
				shouldFetchDataFromServer = true; // saving in other variable because the request should be after everything else (i.e. saving the lastUpdateId)
			}	
		});

		addMessagesHeader(data['messages']);
	}
	
	if (typeof data['lastUpdateId'] != "undefined") {
		lastUpdateId = data['lastUpdateId'];
	}
	
	if (typeof data['data'] != "undefined") {
		$.each(data['data'], function(commandKey, commandResponse) {
			if (refreshCommandsUpdate.indexOf(commandKey) >= 0) {
				// found that data of a command is received, and therefore shouldn't be asked from the server again when refreshing
				refreshCommandsUpdate.splice(refreshCommandsUpdate.indexOf(commandKey), 1);
			}
			if (commandKey == "Rounds.list") {
				// update of round options
				currentRoundSelected = $("#roundSelector").val();
				$(".roundSelector").html('');
				$.each(commandResponse, function(key, round) {
					optionHtml = '<option value="'+ round +'"';
					if (currentRoundSelected == round) {
						optionHtml += ' selected="selected"';
					}
					optionHtml += '>'+ round +'</option>';
					$(".roundSelector").append(optionHtml);
				});
				if (commandResponse.length == 0) {
					// no rounds available
					optionHtml = '<option value="-" selected="selected">No rounds available</option>';
					$(".roundSelector").append(optionHtml);
				} else if (currentRoundSelected == null) {
					// select last round by default
					$(".roundSelector option:last").prop('selected', true);
				}
				
				if (typeof callbackUpdateRoundSelector == 'function') {
					// call function after updating round selectors
                    callbackUpdateRoundSelector();
				}
				
				// fetch new data from server
				if (roundRefreshAfterUpdate) {
					sendServerCommands(true);
				}
			}

            if (typeof controlHandleResponse == "function") {
                controlHandleResponse(commandKey, commandResponse);
            }
            if (typeof handleResponse == "function") {
			    handleResponse(commandKey, commandResponse);
            }
		});
	}
	
	if (shouldFetchDataFromServer && (refreshCommandsUpdate.length > 0)) {
		// requesting new data from server
		sendServerCommands(true);
	}
}

/* Handle pNotify notifications */

// show loading notification (show == true) or hide it (show == false)
function showLoadingNotification(show) {
	if (show) {
		$("#loadingIndicator").show();
	} else {
		$("#loadingIndicator").hide();
	}
}

// adding message to header bar
function addMessagesHeader(messages) {
	if ($("#lastMessages .notification-body").length == 0) {
		// remove default message 
		$("#lastMessages").html('<ul class="notification-body"></ul>');
	}


	$.each(messages, function(messageId, messageObject) {
		if ($("#messageId-"+ messageObject['messageId']).length > 0) {
			// message already placed -> do nothing
			return true;
		}
		dateTime = (messageObject['datetime'] != null) ? messageObject['datetime']['date'] : "";
		htmlMessage = '\
            <li id="messageId-'+ messageObject['messageId'] +'">\
				<span class="padding-10 unread">\
					<em class="badge padding-5 no-border-radius bg-color-blueLight pull-left margin-right-5">\
						<i class="fa fa-check fa-fw fa-2x"></i>\
					</em>\
					<span>\
						<strong>'+ messageObject['title'] +'</strong><br />\
						'+ messageObject['text'] +'<br />\
						<span class="pull-right font-xs text-muted"><i class="fa fa-clock-o"></i><i> '+ dateTime +'</i></span>\
					</span>\
				</span>\
			</li>\
            ';
        $("#lastMessages ul").prepend(htmlMessage);
    });
    newCount = parseInt($("#lastMessagesCount").text()) + messages.length;
    $(".lastMessagesCount").text(newCount);
    if (typeof notification_check == 'function') {
        notification_check();
    }
}

// show only one message as a notification, and display the next notification after the previous is closed.
var messagesNotifications = new Array();
function addMessagesNotifications(messages) {
	$.each(messages, function(messageId, messageObject) {
		// Prevent double message processing
		if (messagesNotifications.indexOf(messageObject['messageId']) != -1) {
			// message already processed
			return true;
		}
		messagesNotifications.push(messageObject['messageId']);

		if ((messageObject['origin'] == 'me') && (localStorage.getItem("notificationSettingsMe") != 'true')) {
            // Notification settings state that my notifications must not be displayed
            return;
        }
        if ((messageObject['origin'] == 'otherPerson') && (localStorage.getItem("notificationSettingsOtherPerson") != 'true')) {
            // Notification settings state that other person's notifications must not be displayed
            return;
        }

        colorHex = "#739E73";
		iconShown = "fa fa-check bounce animated";
		if (messageObject['type'] == "error") {
			colorHex = "#D9534F";
			iconShown = "fa fa-times bounce animated";
		}
        if (typeof $.smallBox != "undefined") {
            $.smallBox({
                title : messageObject['title'],
                content : messageObject['text'],
                color : colorHex,
                iconSmall : iconShown,
                timeout : 4000,
                sound : false
            });
        }
	});
}

var currentlyOffline = false;
/**
 * Function to show/hide offline message
 * @param boolean showOffline Whether to show offline message or not
 */
function showOfflineMessage(showOffline) {
    if (typeof isControl != "undefined" && isControl) {
        if (showOffline) {
            // show offline message
            $(".offline-ui.offline-ui-down").show();
            currentlyOffline = true;
        } else {
            if (currentlyOffline) {
                // remove offline message (but only do this once for performance reasons)
                $(".offline-ui.offline-ui-down").hide();
            }
            currentlyOffline = false;
        }
    }
}


/* General functions  */

// retrieves id from string text by removing everything before "-"
function getId(str) {
	if (str == undefined) {
		return undefined;
	} else {
		return str.substr(str.indexOf("-")+1);
	}
}

// set player names correct for teams with multiple players
function stylePlayerNames(context) {
	if (context == null) {
		context = $("body");
	}
	$(".playerNames:not(.styled)", context).each(function() {
		$(this).find("span:not(:last-child), a:not(:last-child)").each(function() {
			if ($(this).next().html() != '') {
				// place an enter (since it's not already there)
				$(this).after('<br />');
			}
		});
		$(this).addClass('styled');
	});
}

// inputting data of selectable pools in selection boxes
function updatePoolSelector(callbackAfterUpdate) {
	$.ajax({
		type: 'GET',
		cache: false,
		url: urlApiPoolsList,
		dataType: 'json',
		success: function(data) {
			$(".poolSelector").html('');
			$.each(data, function(poolIndex, poolObject) {
				optionHtml = '<option value="'+ poolObject['poolId'] +'">'+ poolObject['name'] +' ('+ poolObject['totTeams'] +')</option>';
				$(".poolSelector").append(optionHtml);
			});

            if (typeof callbackAfterUpdate == 'function') {
                callbackAfterUpdate();
            }
		},
		statusCode: {
		    403: function() {
		    	alert("You have been signed out, please log in again");
		    	window.location.href = urlFrontLogin;
		    },
		},
	});
}

// update the round selector, with updateData a boolean to whether or not refresh entire page after rounds have been loaded
var roundRefreshAfterUpdate = false;
var callbackUpdateRoundSelector = null;
function updateRoundSelector(poolId, refreshAfterUpdate, callbackAfterUpdate) {
	// update the round selector
	roundRefreshAfterUpdate = refreshAfterUpdate;
	selectedPoolId = poolId;
	if ((typeof poolId == "undefined") || (poolId == null)) {
		selectedPoolId = "all";
	}
    if (typeof callbackAfterUpdate == 'function') {
        callbackUpdateRoundSelector = callbackAfterUpdate;
    }
	command = {
		command : 'Rounds.list',
		poolId: selectedPoolId,
	}
	addServerCommand(command);
	sendServerCommands(false);
}

// ajax datatable settings
var ajaxDataTableOptions = {}; // options for the table index, formatted as ajaxTableOptions.tableId.option = value
function setDatatableReady() {
	initObjectLiteral = {
		//"sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
		//"sPaginationType": "bootstrap_full",
		//"oLanguage": {
		//	"sLengthMenu": "_MENU_ records per page"
		//},
		"iDisplayLength": 25,
	    "bDeferRender": true,
	    "bDestroy": true,
	    "bFilter": true,
	    "bInfo": true,
	    "bPaginate": true,
        "autoWidth": false,
        //"scrollX": true,
	    "bSort": true,
	    "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
			if ($(this).parent().find(".dataTableLoadingIndicator").length > 0) {
				// remove loading indicator
				$(this).parent().find(".dataTableLoadingIndicator").remove();
			}

			// create formSwitch
			$(".formSwitch", nRow).each(function() {
				if ($(this).siblings(".bootstrap-switch-handle-on").length == 0) {
					$(this).bootstrapSwitch();
				}
			});

			if (typeof datatableProcessRow === "function") {
				if (!$(nRow).hasClass("dataTableRowProcessed")) {
					datatableProcessRow(nRow, $(this));
					$(nRow).addClass("dataTableRowProcessed");
				}
			}
		}
	};

	$.each(ajaxDataTableOptions, function(tableId, options) {
		$.each(options, function(index, value) {
			initObjectLiteral[index] = value;
		});
		$(".ajaxDataTable#"+ tableId).addClass("ajaxDataTableRendered").dataTable(initObjectLiteral);
	})
	// render normal datatables for those without ajaxDataTableOptions
	$('.ajaxDataTable:not(.ajaxDataTableRendered)').addClass("ajaxDataTableRendered").dataTable(initObjectLiteral);

	$(".ajaxDataTable").parent().parent().find(".dt-top-row, .dt-bottom-row").each(function() {
		// when header or footer is empty => hide
		if ($(this).text() == "") {
			$(this).css("display", "none");
		}
	})
	
	// show loading indicator
	$(".ajaxDataTable").after('<div class="dataTableLoadingIndicator alert alert-info"><b>Loading...</b></div>');
}

function dataTableFinishedLoading(objectId) {
	$("#"+ objectId).parent().find(".dataTableLoadingIndicator").remove();
	$("#"+ objectId).dataTable().fnAdjustColumnSizing();
}

// set typeahead for looking up player(Id's)
var typeaheadIndex = 0;
function setPlayersTypeahead() {
	playersTypeaheadObject = $(".playersTypeahead");

	// add loading indicator
	loadingObject = $(playersTypeaheadObject).parent().parent().find(".fa-search");
	$(loadingObject).removeClass("fa-search").addClass("fa-spinner fa-spin");

	$.ajax({
		type: 'GET',
		cache: false,
		url: urlApiPlayerList,
		dataType: 'json',
		success: function(data) {
			localSource = [];
			$.each(data['players'], function(playerId, playerObject) {
				datum = {
					value: playerObject['name'],
					id: playerObject['playerId']
				}
			    localSource.push(datum);
			});

			// instantiate the bloodhound suggestion engine
		    var sourceOptions = new Bloodhound({
		    	datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.value); },
		    	queryTokenizer: Bloodhound.tokenizers.whitespace,
		    	local: localSource
		    });
		    // initialize the bloodhound suggestion engine
		    sourceOptions.initialize();

		    // (re)creeate typeahead object
			typeaheadIndex++;
			$(playersTypeaheadObject).typeahead("destroy");
		    $(playersTypeaheadObject).typeahead(null, {
		    	name: 'players'+ typeaheadIndex,
		    	source: sourceOptions.ttAdapter(),
		    	hint: false
		    }).on('typeahead:selected typeahead:autocompleted', function (obj, datum) {
			    $('.selectedPlayerIdTypeahead').val(datum.id);
		    	$('.selectedPlayerIdTypeahead').triggerHandler("change");
			});
			$(loadingObject).removeClass("fa-spinner fa-spin").addClass("fa-search");

			// css fixes
			$(playersTypeaheadObject).css("background-color", "");

			$("#searchPlayerId").focus();
            //$('#site-head .navbar-search').show();
            $('#site-head .navbar-search #navSearch').focus();

			// set event handler of navigation search
			$("#navSearch").off('typeahead:selected typeahead:autocompleted');
			$("#navSearch").on('typeahead:selected typeahead:autocompleted', function (obj, datum) {
			    url = Routing.generate('control_player_info', {'tournamentUrl': tournamentUrl, 'playerId': datum.id, '_locale': locale });
			    url = convertUrlToHash(url);
			    window.location.href = url;
			});
		},
		statusCode: {
		    403: function() {
		    	alert("You have been signed out, please log in again");
		    	window.location.href = urlFrontLogin;
		    },
		},
	});

	
}

// set typeahead for search
$("#navSearch").on("focus", function() {
	$(this).tooltip('hide');
	if ($(this).hasClass("tt-input")) {
		return;
	}

	// update search typeahead
	setPlayersTypeahead();
});

// global variables set in renderPlayerNames, and used after the function is called (kind of second result besides the res)
var playersNotReady = false;
var playersPlaying = false;
// return the formatted names of players, including information about whether the player is ready and link
function renderPlayerNames(playersArray, isControl) {
	res = '<span class="playerNames">';
	$.each(playersArray, function(key, playerObject) {
		// playerObject from API can be a string with the name, or an object with more information
		if (typeof playerObject == "object") {
			playerId = playerObject['playerId'];
			playerName = playerObject['name'];
			playerReady = playerObject['ready'];
			playerCurrentlyPlaying = playerObject['currentlyPlaying'];
		} else {
			// playerObject is a string
			playerId = key;
			playerName = playerObject;
			playerReady = true;
			playerCurrentlyPlaying = false;
		}

		// creating url
		if (isControl) {
			url = Routing.generate('control_player_info', {'tournamentUrl': tournamentUrl, 'playerId': playerId, '_locale': locale });
			url = convertUrlToHash(url);
		} else {
			url = Routing.generate('live_player', {'tournamentUrl': tournamentUrl, '_locale': locale }) +"#"+ playerId;
		}
		res += '<a href="'+ url +'" data-playerId="'+ playerId +'">';

		// creating result string
		if (!playerReady) {
			res += "<span class=\"label label-danger\">"+ playerName +"</span>";
		} else if (playerCurrentlyPlaying) {
			res += "<span class=\"label label-warning\">"+ playerName +"</span>";
		} else {
			res += "<span class=\"label label-primary \">"+ playerName +"</span>";
		}
		res += "</a>";

		// checking if players are not ready or currently playing
		if (!playersNotReady && !playerReady) {
			playersNotReady = true;
		}
		if (!playersPlaying && playerCurrentlyPlaying) {
			playersPlaying = true;
		}

	});
	res += "</span>";
	return res;
}

function convertUrlToHash(link) {
    if ((typeof link != "undefined") && (link.indexOf(controlBaseUrl) != -1)) {
        link = link.replace(controlBaseUrl, "#");
    }
    return link;
}

function openUrlAsHash(url) {
	hashUrl = convertUrlToHash(url);
	window.location.href = controlBaseUrl + hashUrl;
}

// Enter data in modal popup, based on match(Id) data
function fillModalData(modalId, matchId) {
    if ($("#matchId-"+ matchId).closest("table").prop('id') == 'playingMatchesTable') {
        // match in currently playing table
        i = 1;
    } else {
        // match in upcoming matches table
        i = 0;
    }

    $("#"+ modalId +" .modalTeam1").html($("#matchId-"+ matchId +" td:nth-child("+ (2+i) +")").html());
    $("#"+ modalId +" .modalTeam2").html($("#matchId-"+ matchId +" td:nth-child("+ (3+i) +")").html());

    $("#"+ modalId +" .modalLocalId").html($("#matchId-"+ matchId +" td:nth-child("+ (1+i) +")").html());
    $("#"+ modalId +" .modalPool").html($("#matchId-"+ matchId +" td:nth-child("+ (4+i) +")").html());
    $("#"+ modalId +" .modalRound").html($("#matchId-"+ matchId +" td:nth-child("+ (5+i) +")").html());
    $("#"+ modalId +" .modalMatchId").val(matchId);
}

function sendSecondCall() {
    command = {
        command : 'Matches.secondCall',
        matchId: $("#secondCallModal .modalMatchId").val(),
        playerIds: {},
    }
    i = 0;
    $('#secondCallModal .secondCallPlayerCheck:checked').each(function() {
        command.playerIds[i] = getId($(this).attr('id'));
        i++;
    });
    addServerCommand(command);
    sendServerCommands(false);
    $("#secondCallModal").modal('hide');
}

function scoreCommandResponse(commandResponse) {
    $("#scoreMatchModal .modalTeam1").html(commandResponse['team1Name']);
    $("#scoreMatchModal .modalTeam2").html(commandResponse['team2Name']);
    $("#scoreMatchModal .modalLocalId").html(commandResponse['localId']);
    $("#scoreMatchModal .modalPool").html(commandResponse['poolName']);
    $("#scoreMatchModal .modalRound").html(commandResponse['round']);
    $("#scoreMatchModal .modalMatchId").val(commandResponse['id']);
    // set scores of sets
    $.each(commandResponse['score'], function(setNr, scoreArray) {
        $("#set"+ (setNr+1) +"-1").val(scoreArray[1]);
        $("#set"+ (setNr+1) +"-2").val(scoreArray[2]);
    });
}

$(document).ready(function() {
    $("#scoreMatchModal").on("show.bs.modal", function() {
        $("#scoreMatchModal .input-prepend :input").val('');
    });
    $("#scoreMatchModal").on("shown.bs.modal", function() {
        $("#scoreMatchModal .input-prepend :input").first().focus();
    });
    $('#scoreMatchModal .input-prepend :input').keyup(function(e) {
        if(e.keyCode == 13) {
            // pressed enter -> send form
            sendScoreMatch();
        }
    });

    $("#secondCallModal").on("show.bs.modal", function() {
        // add checkboxes for second call

        $("#secondCallModal .playerNames a").each(function() {
            playerId = $(this).attr('data-playerId');
            $(this).before('<input type="checkbox" class="secondCallPlayerCheck" id="secondCallPlayerId-'+ playerId +'" /> ');
            $(this).replaceWith('<label for="secondCallPlayerId-'+ playerId +'">'+ $(this).html() +'</label>');
        });
    });
});

