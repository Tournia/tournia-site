window.FinishedMatchView = Backbone.View.extend({
    tagName: "tr",
    
    render: function () {
        jQuery(this.el).html(this.template(this.model.toJSON()));
        return this;
    }
});

window.FinishedMatchListView = Backbone.View.extend({
    tagName: 'table',
    id: 'finished-matches-table',
    className: 'table team-table',

    initialize: function () {
        this.isLoading = false;
        this.page = 1;
        this.finishedMatchCollection = new FinishedMatchCollection();
        
        this.finishedMatchCollection.bind('add', this.add, this);
    },

    events: {
      'click .loadmore': 'loadmore'
    },

    render: function () {
        this.loadResults();
    },
    
    loadResults: function () {
        var self = this;

        this.isLoading = true;

        var startPos = (this.page - 1) * 10;
        var limit = 10;

        this.finishedMatchCollection.fetch({
                data: {commands: {0: {command: 'Matches.listStatus', status: 'played', startPos: startPos, limit: limit, sortOrder: 'DESC'}}},
                type: 'POST',
                success:(function(matches){
                    // Remove current loadmore button to prevent it beeing in the middle of the table
                    jQuery(".tab-content").find('.loadmore').parent().parent().remove();

                    _(matches.models).each(self.add, this);

                    if(matches.models.length == limit)
                    {
                        // Limit of matches is loaded so aparently there are still matches left that can be loaded
                        jQuery(self.el).append("<tr><td colspan=\"4\" class=\"loadmore-row\"><button class=\"loadmore btn btn-default btn-block\">Load more matches<\/button><\/td><\/tr>"); 
                    }
                    jQuery("#finished-matches").append(self.el);

                    // Rebind events to bind new DOM elements
                    self.delegateEvents()

                    jQuery(".tab-content").find('.loadingpane').remove();

                    self.isLoading = false;
                }),
                error:(function (e) {
                    console.log(' Service request failure: ' + e);
                }),
            }
        );
    },

    add: function(match) {
        var finishedMatchView = new FinishedMatchView({ model: match });

        jQuery(this.el).append(finishedMatchView.render().el);
    },


    loadmore: function () {
        if( !this.isLoading ) {
            jQuery(".tab-content").find('.loadmore').prop('disabled',true).html('Loading...');

            this.page += 1; // Load next page
            this.loadResults();
        }
    },

});