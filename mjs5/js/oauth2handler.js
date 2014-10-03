// Standard OAuth2Helper
// Depends on jQuery 2
function OAuth2Handler(client_id, redirect_uri)
{
    // Check if the current user is authorized to use resource
    // Redirects to login page if the user is not logged in
	this.login = function(resource, succes, failure)
	{
		var urlToken = this._getUrlParam("code");
		if(!this.accessToken && !urlToken)
		{
			this.state = Math.random();
			this._setState(this.state);
			
			window.location = this.endpoint + "authorize?response_type=code&client_id=" + encodeURIComponent(this.client_id) + "&client_pass=" + encodeURIComponent(this.client_pass) + "&redirect_uri=" + encodeURIComponent(this.redirect_uri) + "&state=" + this.state;
			return;
		}
		
		if(urlToken)
		{
			if(this.state != this._getUrlParam("state"))
			{
				failure("Invalid state (" + this.state + "!=" + this._getUrlParam("state"));
				return;
			}

            var me = this;

			// Convert urlToken to accessToken
			$.ajax({
				method: 'POST',
				url: this.endpoint + 'token',
				dataType: 'JSON',
				data: {
					grant_type: 'authorization_code',
					code: urlToken,
					redirect_uri: this.redirect_uri,
					client_id: this.client_id,
					client_secret: this.client_pass,
				},
				success: function(result){
					me.access_token = result.access_token;
					me._setStoredToken(result.access_token);
                    console.log(me);
                    window.location = me.redirect_uri;
				},
				error: function(result){
					failure(result);
				},
			});
			return;
		}
		
		//Check for resource
		$.ajax({
			method: 'GET',
			url: this.endpoint + "/" + resource + "?access_token=" + this.accessToken,
			success: function(result){
				succes();
			},
			error: function(result){
				failure(result);
			},
		});
		return;
	}
	
	this._getStoredToken = function()
	{
		return sessionStorage.getItem("OAuth2AccessToken");
	}
	
	this._setStoredToken = function(token)
	{
		sessionStorage.setItem("OAuth2AccessToken", token);
	}
	
	this._getState = function()
	{
		return sessionStorage.getItem("OAuth2State");
	}
	
	this._setState = function(state)
	{
		return sessionStorage.setItem("OAuth2State", state);
	}
	
	this._getUrlParam = function(name) {
		var res = decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);
		if(res == 'null')
			return false;
		return res;
    }
	
	this.accessToken = this._getStoredToken();
	this.endpoint = 'https://login.i.bolkhuis.nl/';
	
	this.redirect_uri = redirect_uri;
	this.client_id = client_id;
	this.client_pass = '';
	
	this.state = this._getState();
    
}
