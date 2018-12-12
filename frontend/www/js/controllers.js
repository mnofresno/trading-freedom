angular.module('trading-freedom.controllers', [])

.controller('DashCtrl', function($scope) {})

.controller('ChatsCtrl', function($scope, Chats) {
  // With the new view caching in Ionic, Controllers are only called
  // when they are recreated or on app start, instead of every page change.
  // To listen for when this page is active (for example, to refresh data),
  // listen for the $ionicView.enter event:
  //
  //$scope.$on('$ionicView.enter', function(e) {
  //});

  $scope.chats = Chats.all();
  $scope.remove = function(chat) {
    Chats.remove(chat);
  };
})

.controller('ChatDetailCtrl', function($scope, $stateParams, Chats) {
  $scope.chat = Chats.get($stateParams.chatId);
})

.controller('AccountCtrl', function ($localStorage, CrawlerService, lodash, $scope, AuthService, KeysService) {
  var self = this;
  self.ownExchanges = [];
  self.exchanges = [];
  self.selectedExchange = null;
  self.keyToAdd = null;

  self.addKeyButtonTitle = function() {
    return self.keyToAdd ? 'Cancel' : 'Add new key';
  };

  self.onload = function() {
    self.keyToAdd = null;
    CrawlerService.GetOwnExchanges(function (data) {
      self.ownExchanges = data.length ? data : [{
        id: null,
        description: '-no keys configured-'
      }];
      self.selectedExchange = $localStorage.has('defaultExchangeId') ?
        lodash.find(self.ownExchanges, {
          id: $localStorage.get('defaultExchangeId')
        }) :
        self.ownExchanges[0];
    });
  };

  $scope.$on('$ionicView.beforeEnter', self.onload);

  self.selectionChanged = function()
  {
    $localStorage.set('defaultExchangeId', self.selectedExchange.id);
  };

  self.Logout = function()
  {
    AuthService.clear();
  };

  self.AddKey = function ()
  {
    if(self.exchanges.length === 0) {
      CrawlerService.GetExchanges(function (data) {
        self.exchanges = data;
      });
    }

    if (self.keyToAdd) {
      self.keyToAdd = null;
    } else {
      self.keyToAdd = {
        api_key: '',
        api_secret: '',
        exchange_provider_id: null
      };
    }
  };

  self.isValidKey = function()
  {
    return self.keyToAdd && self.keyToAdd.api_key && self.keyToAdd.api_secret && self.keyToAdd.exchange_provider_id;
  };

  self.SaveKey = function()
  {
    KeysService.Save(self.keyToAdd, self.onload);
  };

  return self;
})

.controller('SignUpCtrl', function (LoginService, $state, http) {
  var self = this;

  self.newUser = {
    name: '',
    email: '',
    password: '',
    repeatPassword: ''
  };

  self.isValid = function()
  {
    return self.newUser.name &&
      self.newUser.email &&
      self.newUser.password;
  };

  self.Register = function()
  {
    if(self.newUser.password !== self.newUser.repeatPassword)
    {
      return;
    }
    LoginService.Register(self.newUser, () => $state.go('tab.balance'));
  };

  return self;
})

.controller('LoginCtrl', function(LoginService, $state)
{
    var self = this;

    function Credentials()
    {
        var c = this;
        c.email    = '';
        c.password = '';
        c.isValid  = function()
        {
            return c.email !== '' && c.password !== '';
        };

        return c;
    }

    self.credentials = new Credentials();

    self.loginWasCalled = false;
    self.loginErrors = [];
    self.Login = function()
    {
        self.loginErrors = [];
        self.loginWasCalled = true;
        LoginService.Login(self.credentials, function()
        {
            $state.go('tab.balance');
        },function(error)
        {
            self.credentials = new Credentials();
            self.loginErrors = error;
            setTimeout(function()
            {
                self.loginErrors = [];
            }, 1000);
            self.loginWasCalled = false;
        });
    };

    self.Register = function()
    {
      $state.go('signup');
    };

    return self;
})

.controller('BalanceCtrl', function(CrawlerService, $scope, $localStorage)
{
    var self = this;

    self.loadingMessage = "Getting information...";

    self.Balances = { balances: [] };

    self.exchanges = [];

    self.selectedExchange = null;

    self.GetBalances = function(exchange)
    {
        self.selectedExchange = exchange;

        if(!exchange) {
          self.loadingMessage = "No keys configured";
          return;
        }

        CrawlerService.GetBalances(exchange, function(result)
        {
            self.Balances = result;
            $scope.$broadcast('scroll.refreshComplete');
        });
    };

    self.GetDefaultBalances = function()
    {
      self.selectedExchange = self.exchanges.length > 0
        ? {
          id: $localStorage.has('defaultExchangeId')
            ? $localStorage.get('defaultExchangeId')
            : self.exchanges[0].id
        }
        : null;
      self.GetBalances(self.selectedExchange);
    };

    $scope.$on('$ionicView.beforeEnter', function(){
      CrawlerService.GetOwnExchanges(function (data) {
        self.exchanges = data;
        self.GetDefaultBalances();
      });
    });

    return self;
});
