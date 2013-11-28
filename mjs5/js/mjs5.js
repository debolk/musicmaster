$(function(){
    OAuth2 = new OAuth2Handler("mjs5", "http://musicbrainz.i.bolkhuis.nl/mjs5/");
    OAuth2.login("mp3control", function(){ document.write('hi'); }, function(result) {console.log(result); });
});
