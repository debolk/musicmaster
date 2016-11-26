$(function(){
    OAuth2 = new OAuth2Handler("mjs5", "http://musicbrainz.i.bolkhuis.nl/mjs5/");
    OAuth2.login("mp3control", function(){
        window.SongController = new SongController();
        window.mjs = new MjsController("mp3soos", OAuth2);
    }, function(result) { console.log("error"); console.log(result); });
});
