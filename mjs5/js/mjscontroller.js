function MjsController(name, oauth)
{
    var me = this;
    this.name = name;
    this.oauth = oauth;
    this.url = 'http://musicbrainz.i.bolkhuis.nl/player/mjs/' + name + '/';

    this.playlist = {};
    this.status = 'stopped';
    this.current = {};

    var updateCount = 0;

    this._getStatus = function(succes, failure = function(){})
    {
        requestJson("status", function(data){
                succes(data['status']);
            }, failure);
    }

    var requestJson = function(url, succes, failure){
        $.ajax({
            url: me.url + url,
            dataType: "json",
            succes: succes,
            failure: failure,
        });
    }

    this._getCurrent = function(succes, failure = function(){})
    {
        requestJson("current", succes, failure);
    }

    this._getPlaylist = function(succes, failure = function(){})
    {
        var f = console.log("hi");
        f = function(){f()};
        requestJson("playlist", f, failure);
    }

    this.update = function(){
        /*me._getStatus(function(status){
            me.status = status;
        });
        me._getCurrent(function(current){
            me.current = current;
        });

        if(updateCount++ % 30 == 0)
        {
            updateCount = 1;*/
            me._getPlaylist(function(playlist){
                me.playlist = playlist;
                console.log(playlist);
                console.log(me.playlist);
            }, function(error){ console.log(error); });
        //}
        setTimeout(me.update, 1000);

    }

    this.fullUpdate = function(){
        updateCount = 0;
    }

    setTimeout(this.update, 1000);
    
}
