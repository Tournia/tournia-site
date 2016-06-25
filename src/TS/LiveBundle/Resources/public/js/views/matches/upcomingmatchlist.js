window.UpcomingMatchView = Backbone.View.extend({
    tagName: "tr",
    
    initialize: function () {
        this.model.bind("change", this.render, this);
        this.model.bind("destroy", this.close, this);
    },
    
    render: function () {
        jQuery(this.el).html(this.template(this.model.toJSON()));
        return this;
    }
});

window.UpcomingMatchListView = Backbone.View.extend({
    tagName: 'table',
    id: 'upcoming-matches-table',
    className: 'table team-table',

    initialize: function () {
        this.isLoading = false;
        this.page = 1;
        this.upcomingMatchCollection = new UpcomingMatchCollection();
        
        this.upcomingMatchCollection.bind('add', this.add, this);
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

        this.upcomingMatchCollection.fetch({
                data: {commands: {0: {command: 'Matches.listStatus', status: 'ready', startPos: startPos, limit: limit}}},
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
                    jQuery("#upcoming-matches").append(self.el);

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
        var upcomingMatchView = new UpcomingMatchView({ model: match });

        jQuery(this.el).append(upcomingMatchView.render().el);
    },


    loadmore: function () {
        if( !this.isLoading ) {
            jQuery(".tab-content").find('.loadmore').prop('disabled',true).html('Loading...');

            this.page += 1; // Load next page
            this.loadResults();
        }
    },

});