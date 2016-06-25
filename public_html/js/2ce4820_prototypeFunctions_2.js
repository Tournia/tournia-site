
function addFormRow(collectionHolder, addBefore, mainId, beforeString, afterString, deleteParentLocation) {
    // set default values
    if(typeof(beforeString)==='undefined') beforeString = '';
    if(typeof(afterString)==='undefined') afterString = '';
    if(typeof(deleteParentLocation)==='undefined') deleteParentLocation = '';

    // Get the data-prototype
    var prototype = collectionHolder.data('prototype');

    // get the new index
    var index = collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);

    // Display the form in the page in a div
    var newFormLine = $('#'+ mainId).append('<div id="'+ mainId + index +'" class="newFormLine">'+ beforeString + newForm + afterString +'</div>');
    addBefore.before(newFormLine);

    // add a delete link to the new form
    addFormDeleteLink($('#'+ mainId + index), deleteParentLocation);

    // focus on first input element
    $('#'+ mainId + index +' :input').first().focus();
}

function addFormDeleteLink(formLine, deleteParentLocation) {
    if(typeof(deleteParentLocation)==='undefined') deleteParentLocation = '';

    var removeFormA = $('<a href="#"><i class="fa fa-times"></i> Remove</a>');
    formLine.find(deleteParentLocation +" .form-values").append(removeFormA);

    removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the form
        formLine.remove();
    });
}

function setTitleAccordionRow(rowId) {
    // setting title of accordion groups based on name of field
    $('#'+ rowId +' a[data-parent=#'+ rowId +']').each(function() {
        var nameInput = $(this).parent().parent().find(':input').first();
        var titleObject = $(this).find(".accordion-title");
        nameInput.off('change');
        nameInput.on('change', function (e) {
            if ($(this).val() == '') {
                titleObject.text('New');
            } else {
                titleObject.text($(this).val());
            }
        });
        nameInput.triggerHandler('change');
    });
}

function addRemoveFormChoiceOptions(formFieldId) {
    // add and remove options for form choice options
    var formChoiceCollectionHolder = $('#'+ formFieldId +'_choiceOptions');
    var addFormChoiceLink = $('<a href="#"><i class="fa fa-plus"></i> Add a choice option</a>');
    var newFormChoice = $('#'+ formFieldId +'_choiceOptions').after(addFormChoiceLink);
    formChoiceCollectionHolder.append(newFormChoice);
    formChoiceCollectionHolder.data('index', formChoiceCollectionHolder.find(':input').length);
    formChoiceCollectionHolder.find('.form-group').each(function() {
        addFormDeleteLink($(this), '');
    });
    addFormChoiceLink.on('click', function(e) {
        e.preventDefault();
        addFormRow(formChoiceCollectionHolder, newFormChoice, formFieldId +'_choiceOptions');
    });
    fixPrototypeColsize();
}

function showHideFormChoiceOptions(formFieldId) {
    // show or hide options depending if form type is choice or not
    $('#'+ formFieldId +'_type').on('change', function(e) {
        if ($(this).val() == 'choice') {
            $('#'+ formFieldId +'_choiceOptions').parent().parent().show("slow");
            $('#'+ formFieldId +'_choiceExpanded').parent().parent().show("slow");
        } else {
            $('#'+ formFieldId +'_choiceOptions').parent().parent().hide("slow");
            $('#'+ formFieldId +'_choiceExpanded').parent().parent().hide("slow");
        }
    });
    $('#'+ formFieldId +'_type').triggerHandler('change');
}

function setupAccordion(accordionId, addText, isSortable) {
    // make accordion sortable
    if (isSortable) {
        $("#"+ accordionId).sortable({
            items: ':not(.notSortable)',
        }).bind('sortupdate', function() {
            // sort order has changed
            var i = 0;
            $(this).find(".panel-body").each(function() {
                // change value of e.g. tournament[products][x][position] to new position
                // first find the correct position form element
                $(this).find(":input[type=hidden]").each(function() {
                    subName = $(this).attr('name').split("[");
                    if (subName[subName.length-1] == "position]") {
                        // found correct form element to set new position
                        $(this).val(i);
                    }
                })
                i++;
            })
        });
        $("#"+ accordionId +" [data-toggle='collapse']").append('<i class="fa fa-arrows pull-right"></i>');
    }

    // add and remove options for accordion
    var collectionHolder = $('#'+ accordionId);
    var addRowLink = $('<a href="#"><i class="fa fa-plus"></i> '+ addText +'</a>');
    var newRow = collectionHolder.after(addRowLink);
    collectionHolder.append(newRow);
    collectionHolder.data('index', collectionHolder.find('.panel-heading').length);
    collectionHolder.find('.panel-default').each(function() {
        addFormDeleteLink($(this), '.removeRow');
        var rowIndex = $(this).find(':input').first().prop('id');
        var formElementId = rowIndex.substr(0, rowIndex.lastIndexOf('_'));
        addRemoveFormChoiceOptions(formElementId);
        showHideFormChoiceOptions(formElementId);
    });
    accordionIndex = 1;
    addRowLink.on('click', function(e) {
        e.preventDefault();
        addFormRow(collectionHolder, newRow, accordionId, '<div class="panel panel-default"><div class="panel-heading"><a data-toggle="collapse" data-parent="#'+ accordionId +'" href="#new'+ accordionId +'-'+ accordionIndex +'"><span class="accordion-title"></span></a></div><div id="new'+ accordionId +'-'+ accordionIndex +'" class="panel-collapse collapse"><div class="panel-body">', '<div class="form-group removeRow"><label class="col-sm-2 control-label"></label><div class="col-sm-10 form-values"></div></div></div></div></div>', '.removeRow');
        var rowIndex = $('#new'+ accordionId +'-'+ accordionIndex +' :input').first().prop('id');
        var formElementId = rowIndex.substr(0, rowIndex.lastIndexOf('_'));
        addRemoveFormChoiceOptions(formElementId);
        $('#'+ formElementId +'_choiceOptions').append('<p class="form-control-static">Please save the form first before adding choice options.</p>');
        $('#'+ formElementId +'_choiceOptions').parent().find("a").remove();
        showHideFormChoiceOptions(formElementId);

        if (typeof newPrototypeHook == "function") {
            newPrototypeHook($('#new'+ accordionId +'-'+ accordionIndex));
        }

        $('#new'+ accordionId +'-'+ accordionIndex).collapse("show");
        accordionIndex++;
        setTitleAccordionRow(accordionId);
    });
    setTitleAccordionRow(accordionId);
}

// fix prototype col size
function fixPrototypeColsize() {
    $(".form-values div[data-prototype]").each(function() {
        // change prototype to new col-sm-8
        newPrototype = $(this).attr("data-prototype").replace("col-sm-10", "col-sm-8");
        $(this).attr("data-prototype", newPrototype);
        $(this).removeClass("form-control");

        // change existing form-values
        $(this).find(".form-values").each(function() {
            $(this).removeClass("col-sm-10").addClass("col-sm-8");
        });
    });
}
