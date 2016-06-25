window.SearchMatchView = Backbone.View.extend({
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

window.SearchMatchesListView = Backbone.View.extend({
    tagName: 'table',
/*     id: 'current-matches-table', */
    className: 'table team-table',

    initialize: function (options) {
        this.isLoading = false;
        
        if(typeof options.query == 'undefined') {
            console.log('Error: No search query given to load player matches from');
        }
        this.searchQuery = options.query;
        this.searchMatchCollection = new SearchMatchCollection();
        
        this.searchMatchCollection.bind('add', this.add, this);
    },

    render: function () {
        this.loadResults();
    },
    
    loadResults: function () {
        var self = this;

        this.isLoading = true;

        this.searchMatchCollection.fetch({
                data: {commands: {0: {command: 'Matches.listSearch', searchQuery: this.searchQuery}}},
                type: 'POST',
                success:(function(matches){
                    _(matches.models).each(self.add, this);
                    
                    jQuery(".table-wrapper").append(self.el);
                    jQuery(".table-title").text('Search: ' + self.searchQuery);

                    jQuery(".table-wrapper").find('.loadingpane').remove();

                    self.isLoading = false;
                }),
                error:(function (e) {
                    console.log(' Service request failure: ' + e);
                }),
            }
        );
    },

    add: function(match) {
        var searchMatchView = new SearchMatchView({ model: match });

        jQuery(this.el).append(searchMatchView.render().el);
    },
});
