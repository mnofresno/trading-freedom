angular.module('trading-freedom.services', [])

.service('$localStorage', function($window)
{
    var self = this;

    self.set = function(key, value)
    {
        if (typeof(value) === 'string')
        {
            $window.localStorage[key] = value;
        }
        else
        {
            $window.localStorage[key] = JSON.stringify(value);
        }
    };

    self.get = function(key)
    {
        var value = $window.localStorage[key];

        if (value === undefined || value === 'undefined') return undefined;

        try
        {
            return JSON.parse(value);
        }
        catch (e)
        {
            return value;
        }
    };

    self.has = function(key)
    {
        return $window.localStorage[key] !== undefined;
    };

    self.delete = function(key)
    {
        $window.localStorage.removeItem(key);
    };

    return self;
})

.factory('Chats', function() {
  // Might use a resource here that returns a JSON array

  // Some fake testing data
  var chats = [{
    id: 0,
    name: 'Ben Sparrow',
    lastText: 'You on your way?',
    face: 'img/ben.png'
  }, {
    id: 1,
    name: 'Max Lynx',
    lastText: 'Hey, it\'s me',
    face: 'img/max.png'
  }, {
    id: 2,
    name: 'Adam Bradleyson',
    lastText: 'I should buy a boat',
    face: 'img/adam.jpg'
  }, {
    id: 3,
    name: 'Perry Governor',
    lastText: 'Look at my mukluks!',
    face: 'img/perry.png'
  }, {
    id: 4,
    name: 'Mike Harrington',
    lastText: 'This is wicked good ice cream.',
    face: 'img/mike.png'
  }];

  return {
    all: function() {
      return chats;
    },
    remove: function(chat) {
      chats.splice(chats.indexOf(chat), 1);
    },
    get: function(chatId) {
      for (var i = 0; i < chats.length; i++) {
        if (chats[i].id === parseInt(chatId)) {
          return chats[i];
        }
      }
      return null;
    }
  };
})

.service('http', function($http, AuthService)
{
    var self = function(config)
    {
        config.headers = { 'X-Requested-With' : 'XMLHttpRequest' };

        if(AuthService.isAuthed())
        {
            var token = AuthService.getToken();
            config.headers['Authorization'] = 'bearer ' + token;
        }

        return $http(config).then(function(response)
        {
            if (config.success) {
              config.success(response.data);
            }
        }, function(response)
        {
            if(400 < response.status < 420)
            {
                //AuthService.clear();
            }
            if(config.error) config.error(response.data ? response.data : ["No pudo conectarse con el servidor"]);
        });
    };

    return self;
})

.service('AuthService', function($localStorage, $rootScope)
{
    var self = this;

    self.isAuthed = function()
    {
        return $localStorage.has('user_token');
    };

    self.getToken = function()
    {
        if(self.isAuthed()) return $localStorage.get('user_token');
    };

    self.setToken = function(loginData)
    {
        $localStorage.set('user_token', loginData.token);
        $localStorage.set('user_id', loginData.user_id)
    };

    self.clear = function()
    {
        $rootScope.$broadcast('unauthorized');
        $localStorage.delete('user_token');
    };

    self.getCurrentUserId = function () {
      return $localStorage.get('user_id');
    }

    return self;
})

.service('UserService', function(http, ENV, AuthService){
  var self = this;

  self.get = function (callback) {
    http({
      url: ENV.endpoint + 'users/own',
      success: callback,
    });
  };

  self.update = function (user, callback, error) {
    http({
      url: ENV.endpoint + 'users/own',
      success: callback,
      error: error,
      method: 'POST',
      data: user,
    });
  };

  return self;
})

.service('LoginService', function(http, AuthService, ENV)
{
    var self = this;

    var loggedInCallback = null;

    self.setLoggedInCallback = function(callback)
    {
        loggedInCallback = callback;
    };

    self.Login = function(credentials, successCallback, errorCallback)
    {
        var successLogin = function(data)
        {
            loggedInCallback();
            AuthService.setToken(data);
            successCallback();
        };

        http({ data: credentials, url: ENV.endpoint + 'auth/login', method: 'POST', success: successLogin, error: errorCallback })
    };

    self.Register = function(newUser, successCallback, errorCalback)
    {
      var successLogin = function (data) {
        self.Login(newUser, successCallback, errorCalback);
      };

      http({
        data: newUser,
        url: ENV.endpoint + 'auth/register',
        method: 'POST',
        success: successLogin,
        error: errorCalback
      })
    };

    self.updateFCM = function(fcmToken, callback)
    {
        var usuario_actual_id = AuthService.getCurrentUserId();
        if(!usuario_actual_id) return;
        http({ method: 'PUT', url: ENV.endpoint + 'users/' + usuario_actual_id + '/fcm', data: { fcm_token: fcmToken }}).success(callback);
    };

    return self;
})

.service('CrawlerService', function(http, ENV)
{
    var self = this;

    self.GetExchanges = function (callback) {
      http({
        url: ENV.endpoint + 'exchanges',
        success: callback
      });
    };

    self.GetOwnExchanges = function (callback) {
      http({
        url: ENV.endpoint + 'exchanges/own',
        success: callback
      });
    };

    self.GetBalances = function(exchange, callback)
    {
      var exchangeId = exchange ? exchange.id : 1;
      http({ url: ENV.endpoint + 'balances/' +  exchangeId, success: callback });
    };

    return self;
})

.service('KeysService', function(http, ENV)
{
  var self = this;

  self.Save = function (newKey, successCallback, errorCalback)
  {
    http({
      data: newKey,
      url: ENV.endpoint + 'apikeys',
      method: 'POST',
      success: successCallback,
      error: errorCalback
    })
  };

  self.Delete = function (exchange) {
    var exchangeId = exchange.id;
    return http({
      url: ENV.endpoint + 'apikeys/' + exchangeId,
      method: 'DELETE',
    });
  };

  return self;
})

.service('NotificationsService' , function($localStorage, LoginService)
{
    var self = this;

    var notificationCallback = null;

    self.setNotificationCallback = function(callback)
    {
        notificationCallback = callback;
    };

    self.saveToken = function(token)
    {
        LoginService.updateFCM(token, function(response)
        {
            $localStorage.set('fcm_token_stored', true);
        });
    };

    self.getToken = function()
    {
        if(!$localStorage.has('fcm_token_stored'))
        {
            window.FirebasePlugin.getToken(self.saveToken);
        }
    };

    self.installTokenRefresher = function()
    {
        window.FirebasePlugin.onTokenRefresh(function(token)
        {
            $localStorage.delete('fcm_token_stored');
            self.saveToken(token);
        });
    };

    self.installNotificationHandler = function()
    {
        window.FirebasePlugin.onNotificationOpen(self.notificationHandler);
    };

    self.notificationHandler = function(data)
    {
        console.debug(data);
        if(notificationCallback)
        {
            notificationCallback(data.mensaje);
        }

        if(data.isCommand) return;

        if(data.wasTapped)
        {
            //Notification was received on device tray and tapped by the user.
            alert(data.mensaje);
        }
        else
        {
            //Notification was received in foreground. Maybe the user needs to be notified.
            alert(data.mensaje);
        }

        MensajesService.MarcarComoLeidos();
    };

    self.registerCallbacks = function()
    {
        if(!window.FirebasePlugin) return;

        self.getToken();
        self.installTokenRefresher();
        self.installNotificationHandler();
        LoginService.setLoggedInCallback(self.getToken);
    };

    return self;
});
