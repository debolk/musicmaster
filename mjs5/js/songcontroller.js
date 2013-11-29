function SongController()
{
    this.cache = {};

    this.get = function(uri, succes, failure = function(){})
    {
        if(this.cache[uri])
        {
            succes(this.cache[uri]);
            return;
        }

        $.ajax({
            method: 'GET',
            url: uri,
            dataType: 'JSON',
            success: function(result){
                result['uri'] = uri;
                succes(result);
            },
            failure: failure,
        });
    }
}
