$(function(){
    OAuth2 = new OAuth2Handler("mjs5", "http://musicbrainz.i.bolkhuis.nl/mjs5/");
    OAuth2.login("mp3control", function(){
        window.SongController = new SongController();
        SongController.get("http://musicbrainz.i.bolkhuis.nl//plugin/file/files/browse/Artists/ACDC/Black Ice/0101 - Rock 'n' Roll Train.mp3", function(result) {
            console.log(result);
        });
    }, function(result) {console.log(result); });
});
