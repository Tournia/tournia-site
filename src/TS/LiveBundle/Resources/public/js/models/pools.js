window.Pool = Backbone.Model.extend({

    defaults: {
        id: null,
        name: "",
    }

});

window.PoolListCollection = Backbone.Collection.extend({

    model: Pool,

    url: urlApi,

    // Parse de server data to fit models
    parse: function(data, options) {
        return _.map(data.data['Pools.list'], function(pool){
            return pool
        });
    }

});