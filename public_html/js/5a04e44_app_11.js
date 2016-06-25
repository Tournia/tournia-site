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