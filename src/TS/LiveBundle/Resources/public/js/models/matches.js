window.Match = Backbone.Model.extend({

    defaults: {
        pool: "",
        deltaStartTime: null,
        localId: null,
        location: "",
        locationId: null,
        locationOnHold: false,
        matchId: null,
        round: "",
        team1Id: null,
        team1Players: null,
        team2Id: null,
        team2Players: null
    }

});

window.CurrentMatchCollection = Backbone.Collection.extend({

    model: Match,

    url: urlApi,

    // Parse de server data to fit models
    parse: function(data, options) {
        return data.data['Matches.listPlaying'];
    }

});

window.UpcomingMatchCollection = Backbone.Collection.extend({

    model: Match,

    url: urlApi,
    
    // Parse de server data to fit models
    parse: function(data, options) {
        return _.map(data.data['Matches.listStatus'], function(match){
            return match
        });
    }

});

window.FinishedMatchCollection = Backbone.Collection.extend({

    model: Match,

    url: urlApi,

    // Parse de server data to fit models
    parse: function(data, options) {
        return _.map(data.data['Matches.listStatus'], function(match){
            return match
        });
    }

});

window.SearchMatchCollection = Backbone.Collection.extend({

    model: Match,

    url: urlApi,

    // Parse de server data to fit models
    parse: function(data, options) {
        return _.map(data.data['Matches.listSearch'], function(match){
            return match
        });
    }

});