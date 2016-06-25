window.PoolSelectorView = Backbone.View.extend({
    render: function () {
        jQuery(this.el).html(this.template(this.model.toJSON()));
        return this;
    }
});

window.PoolSelectorListView = Backbone.View.extend({
    className: 'list',

    initialize: function (options) {
        this.poolId = options.poolId;

        this.isLoading = false;
        this.poolListCollection = new PoolListCollection();
        
        this.bind( "change", this.loadResults() );
        this.poolListCollection.bind('add', this.add, this);
    },

    render: function () {
        this.loadResults();
    },
    
    loadResults: function () {
        var self = this;

        this.isLoading = true;

        this.poolListCollection.fetch({
                data: {commands: {0: {command: 'Pools.list'}}},
                type: 'POST',
                success:(function(pools){
                    jQuery("#pool-select .list").remove();

                    _(pools.models).each(self.add, this);

                    jQuery("#pool-select").append(self.el);

                    // Rebind events to bind new DOM elements
                    self.delegateEvents()

                    self.isLoading = false;
                }),
                error:(function (e) {
                    console.log(' Service request failure: ' + e);
                }),
            }
        );
    },

    add: function(pool) {
        if(this.poolId == pool.id) {
            jQuery('#pool-select .current .pool').html('<small class="muted">Pool</small>' + pool.get("name"));
        }

        var poolSelectorView = new PoolSelectorView({ model: pool });

        jQuery(this.el).append(poolSelectorView.render().el);
    },

});