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

.controller('AccountCtrl', function ($localStorage, CrawlerService, lodash, $scope) {
  var self = this;
  self.exchanges = [];
  self.selectedExchange = null;

  $scope.$on('$ionicView.beforeEnter', function () {
    CrawlerService.GetOwnExchanges(function (data) {
      self.exchanges = data;
      self.selectedExchange = $localStorage.has('defaultExchangeId')
        ? lodash.find(self.exchanges, {id:$localStorage.get('defaultExchangeId')})
        : self.exchanges[0];
    });
  });

  self.selectionChanged = function() {
    $localStorage.set('defaultExchangeId', self.selectedExchange.id);
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

    return self;
})

.controller('BalanceCtrl', function(CrawlerService, $scope, $localStorage)
{
    var self = this;

    self.Balances = { balances: [] };

    self.exchanges = [];

    self.selectedExchange = null;

    self.GetBalances = function(exchange)
    {
        self.selectedExchange = exchange;
        CrawlerService.GetBalances(exchange, function(result)
        {
            self.Balances = result;
            $scope.$broadcast('scroll.refreshComplete');
        });
    };

    self.GetDefaultBalances = function()
    {
      self.selectedExchange = {
        id: $localStorage.has('defaultExchangeId')
          ? $localStorage.get('defaultExchangeId')
          : self.exchanges[0].id
      };
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
