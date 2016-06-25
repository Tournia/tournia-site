window.utils = {

    // Asynchronously load templates located in separate .html files
    loadTemplate: function(views, callback) {

        var deferreds = [];

        jQuery.each(views, function(index, view) {
            if (window[view]) {
                deferreds.push(
                    window[view].prototype.template = _.template( jQuery("#" + view ).html() )
                );
            } else {
                alert(view + " not found");
            }
        });

        jQuery.when.apply(null, deferreds).done(callback);
    },

    changePage: function() {
        if (location.hash !== '')
        {
            // Deactivate active page
            jQuery('#main-navbar ul.nav').find('li.active').each(function () {
                jQuery(this).removeClass("active");
            });
            jQuery('.content-tabs').find('.content-tab.active').each(function () {
                jQuery(this).removeClass("active");
            });
    
            // Activate active page
            var routeArray = location.hash.split("/");
            // The selected tab has to be the first arary element
            var selectedPage = routeArray[0];
            jQuery('.content-tabs').find('.content-tab' + selectedPage).addClass('active');
            jQuery('#main-navbar ul.nav').find('li a[href="' + selectedPage + '"]').parent('li').addClass('active')
        }
    },

    uploadFile: function (file, callbackSuccess) {
        var self = this;
        var data = new FormData();
        data.append('file', file);
        jQuery.ajax({
            url: 'api/upload.php',
            type: 'POST',
            data: data,
            processData: false,
            cache: false,
            contentType: false
        })
        .done(function () {
            console.log(file.name + " uploaded successfully");
            callbackSuccess();
        })
        .fail(function () {
            self.showAlert('Error!', 'An error occurred while uploading ' + file.name, 'alert-error');
        });
    },

    displayValidationErrors: function (messages) {
        for (var key in messages) {
            if (messages.hasOwnProperty(key)) {
                this.addValidationError(key, messages[key]);
            }
        }
        this.showAlert('Warning!', 'Fix validation errors and try again', 'alert-warning');
    },

    addValidationError: function (field, message) {
        var controlGroup = jQuery('#' + field).parent().parent();
        controlGroup.addClass('error');
        jQuery('.help-inline', controlGroup).html(message);
    },

    removeValidationError: function (field) {
        var controlGroup = jQuery('#' + field).parent().parent();
        controlGroup.removeClass('error');
        jQuery('.help-inline', controlGroup).html('');
    },

    showAlert: function(title, text, klass) {
        jQuery('.alert').removeClass("alert-error alert-warning alert-success alert-info");
        jQuery('.alert').addClass(klass);
        jQuery('.alert').html('<strong>' + title + '</strong> ' + text);
        jQuery('.alert').show();
    },

    hideAlert: function() {
        jQuery('.alert').hide();
    }

};
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
var AppRouter = Backbone.Router.extend({

    routes: {
        ""                                      : "currentMatchList",
        "matches"                               : "currentMatchList",
        "matches/current-matches"               : "currentMatchList",
        "matches/upcoming-matches"              : "upcomingMatchList",
        "matches/finished-matches"              : "finishedMatchList",
        "rankings"                              : "rankingList",
        "rankings/:poolId"                  : "rankingList",
        "player/:playerId"                      : "playerOverview",
        "player/:playerId/overview"             : "playerOverview",
        "player/:playerId/ranking/:poolId"  : "playerRanking",
        "player/:playerId/matches/"             : "playerMatches",
        "search/:query"                         : "searchPlayer"
    },

    initialize: function () {
        this.bind( "all", this.change );
    },
    
    change: function() {
        utils.changePage();

        // Remove old tables
        jQuery('.tab-content').find('table').each(function () {
            this.remove();
        });

        jQuery('.content-tabs').find('table').each(function () {
            this.remove();
        });

        // Initiate loading indicator
        if(jQuery(".tab-content").find(".loadingpane").length == 0) {
            jQuery(".tab-content").append("<div class=\"loadingpane\"><div class=\"logo spinner\"><i class=\"glyphicon glyphicon-bookmark\"></i> Tournia.net</div></div></div>");
        }

        if(jQuery(".table-wrapper").find(".loadingpane").length == 0) {
            jQuery(".table-wrapper").append("<div class=\"loadingpane\"><div class=\"logo spinner\"><i class=\"glyphicon glyphicon-bookmark\"></i> Tournia.net</div></div></div>");
        }
    },

	currentMatchList: function()
	{
        var currentMatchListView = new CurrentMatchListView();
        currentMatchListView.render();
    },
	upcomingMatchList: function(page)
	{
        var upcomingMatchListView = new UpcomingMatchListView();
        upcomingMatchListView.render();
    },
	finishedMatchList: function(page)
	{
        var finishedMatchListView = new FinishedMatchListView();
        finishedMatchListView.render();
    },
	rankingList: function(poolId)
	{
        jQuery('.loadingpane').find('.logo').removeClass('spinner');

        var poolSelectorListView = new PoolSelectorListView({poolId: poolId});
        poolSelectorListView.render();

        if (typeof poolId != 'undefined') {
            jQuery('.loadingpane').find('.logo').addClass('spinner');
            jQuery('#pool-select .current .pool').html('<small class="muted">Pool</small> Loading pool...');

            //Only call rankinglist if a pool is given
            var rankingListView = new RankingListView({poolId: poolId});
            rankingListView.render();
        }
        else
        {
            jQuery('#pool-select .current .pool').html('<small class="muted">Pool</small> Select pool');
        }

    },
	playerOverview: function(playerId)
	{
	    console.log('playerOverview (playerId: ' + playerId + ')');
    },
	playerRanking: function(playerId, poolId)
	{
	    console.log('playerRanking (playerId: ' + playerId + ', poolId: ' + poolId + ')');
    },
	playerMatches: function(playerId)
	{
	    console.log('playerMatches (playerId: ' + playerId + ')');
    },
	searchPlayer: function(query)
	{
	    var query = query.split('+').join(' ');

        var searchMatchesListView = new SearchMatchesListView({query: query});
        searchMatchesListView.render();
    }

});

utils.loadTemplate(['CurrentMatchView', 'UpcomingMatchView', 'FinishedMatchView', 'SearchMatchView', 'RankingView', 'PoolSelectorView'], function() {
    app = new AppRouter();
    Backbone.history.start();
});