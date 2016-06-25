window.Ranking = Backbone.Model.extend({

    defaults: {
        givenUp: false,
        matchesDraw: null,
        matchesLost: null,
        matchesPlayed: null,
        matchesRelative: "",
        matchesWon: null,
        players: null,
        pointsLost: null,
        pointsRelative: "",
        pointsWon: null,
        rank: null,
        setsLost: null,
        setsRelative: "",
        setsWon: null,
        teamId: null,
    }

});

window.RankingListCollection = Backbone.Collection.extend({

    model: Ranking,

    url: urlApi,

    // Parse de server data to fit models
    parse: function(data, options) {
        return data.data['Rankings.pool'];
    }

});