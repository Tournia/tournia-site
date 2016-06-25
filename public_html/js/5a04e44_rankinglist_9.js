window.RankingView = Backbone.View.extend({
    tagName: "tr",
    
    render: function () {
        jQuery(this.el).html(this.template(this.model.toJSON()));
        return this;
    }
});

window.RankingListView = Backbone.View.extend({
    tagName: 'table',
    id: 'ranking-table',
    className: 'table team-table',

    initialize: function (options) {
        this.isLoading = false;

        if(typeof options.poolId == 'undefined') {
            console.log('Error: No pool given to load rankings from');
        }
        this.poolId = options.poolId;
        this.rankingListCollection = new RankingListCollection();
        
        this.rankingListCollection.bind('add', this.add, this);
    },

    render: function () {
        this.loadResults();
    },
    
    loadResults: function () {
        var self = this;

        this.isLoading = true;

        this.rankingListCollection.fetch({
                data: {commands: {0: {command: 'Rankings.pool', poolId: this.poolId}}},
                type: 'POST',
                success:(function(rankings){
                    _(rankings.models).each(self.add, this);

                    jQuery(".table-wrapper").append(self.el);

                    // Rebind events to bind new DOM elements
                    self.delegateEvents()

                    jQuery(".table-wrapper").find('.loadingpane').remove();

                    self.isLoading = false;
                }),
                error:(function (e) {
                    console.log(' Service request failure: ' + e);
                }),
            }
        );
    },

    add: function(ranking) {
        ranking.set('poolId', this.poolId);
        var rankingView = new RankingView({ model: ranking });

        jQuery(this.el).append(rankingView.render().el);
    },

});