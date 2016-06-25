window.CurrentMatchView = Backbone.View.extend({
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

window.CurrentMatchListView = Backbone.View.extend({
    tagName: 'table',
    id: 'current-matches-table',
    className: 'table team-table',

    initialize: function () {
        this.isLoading = false;
        this.currentMatchCollection = new CurrentMatchCollection();
        
        this.currentMatchCollection.bind('add', this.add, this);
    },

    render: function () {
        this.loadResults();
    },
    
    loadResults: function () {
        var self = this;

        this.isLoading = true;

        this.currentMatchCollection.fetch({
                data: {commands: {0: {command: 'Matches.listPlaying'}}},
                type: 'POST',
                success:(function(matches){
                    _(matches.models).each(self.add, this);
                    
                    jQuery("#current-matches").append(self.el);

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
        var currentMatchView = new CurrentMatchView({ model: match });

        jQuery(this.el).append(currentMatchView.render().el);
    },
/*
    initialize: function () {
        this.render();
    },

    render: function () {
        var matches = this.collection.models;

        _.each(matches, function (match) {
            jQuery(this.el).append(new CurrentMatchView({model: match}).render().el);
        }, this);

        return this;
    }
*/
});
