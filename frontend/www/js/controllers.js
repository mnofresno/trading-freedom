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

.controller('AccountCtrl', function($scope) {
  $scope.settings = {
    enableFriends: true
  };
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

.controller('BalanceCtrl', function(CrawlerService, $scope)
{
    var self = this;

    self.Balances = { balances: [] };

    self.exchanges = [];

    CrawlerService.GetExchanges(function(data)
    {
        self.exchanges = data;
    });

    self.GetBalances = function(exchange)
    {
        CrawlerService.GetBalances(exchange, function(result)
        {
            self.Balances = result;
            $scope.$broadcast('scroll.refreshComplete');
        });
    };

    self.GetDefaultBalances = function()
    {
        self.GetBalances({id: 1});
    };

    self.GetDefaultBalances();

    return self;
});
