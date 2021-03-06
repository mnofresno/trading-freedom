// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('trading-freedom', ['ionic', 'trading-freedom.controllers', 'trading-freedom.services', 'trading-freedom.config', 'trading-freedom.interceptors', 'ngLodash'])

.run(function($ionicPlatform, $rootScope, $state, AuthService, $ionicLoading, $locale)
{
    $locale.NUMBER_FORMATS.GROUP_SEP = ".";
    $locale.NUMBER_FORMATS.DECIMAL_SEP = ",";

    $ionicPlatform.ready(function()
    {
        // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
        // for form inputs)
        if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard)
        {
            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
            cordova.plugins.Keyboard.disableScroll(true);
        }
        if (window.StatusBar)
        {
            // org.apache.cordova.statusbar required
            StatusBar.styleDefault();
        }

        $rootScope.$on('unauthorized', function()
        {
            $state.go('login');
        });

        if (AuthService.isAuthed())
        {
            $state.go('tab.balance');
        }
        else
        {
            $state.go('login');
        }
    });

    $rootScope.$on('loading:show', function()
    {
        $ionicLoading.show(
        {
            template: 'Cargando...',
            animation: 'fade-in'
        });
    });

    $rootScope.$on('loading:hide', function()
    {
        $ionicLoading.hide();
    });
})

.config(function($ionicConfigProvider)
{
    $ionicConfigProvider.tabs.position('bottom'); // other values: top
})

.config(function($stateProvider, $urlRouterProvider) {

  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider

  // setup an abstract state for the tabs directive
    .state('tab', {
    url: '/tab',
    abstract: true,
    templateUrl: 'templates/tabs.html'
  })

  // Each tab has its own nav history stack:

  .state('tab.balance', {
    url: '/balance',
    views: {
      'tab-balance': {
        templateUrl: 'templates/tab-balance.html',
        controller: 'BalanceCtrl',
        controllerAs: 'ctrl'
      }
    }
  })

  .state('tab.keys', {
      url: '/keys',
      views: {
        'tab-keys': {
          templateUrl: 'templates/tab-keys.html',
          controller: 'KeysCtrl',
        controllerAs: 'ctrl'
        }
      }
    })
    .state('tab.chat-detail', {
      url: '/chats/:chatId',
      views: {
        'tab-chats': {
          templateUrl: 'templates/chat-detail.html',
          controller: 'ChatDetailCtrl',
            controllerAs: 'ctrl'
        }
      }
    })

  .state('tab.account', {
    url: '/account',
    views: {
      'tab-account': {
        templateUrl: 'templates/tab-account.html',
        controller: 'AccountCtrl',
        controllerAs: 'ctrl'
      }
    }
  })

  .state('login', {
    url: '/login',
    cache: false,
    templateUrl: 'templates/login.html',
    controller: 'LoginCtrl',
    controllerAs: 'ctrl'
  })

  .state('signup', {
    url: '/signup',
    cache: false,
    templateUrl: 'templates/signup.html',
    controller: 'SignUpCtrl',
    controllerAs: 'ctrl'
  });

  // if none of the above states are matched, use this as the fallback
  //$urlRouterProvider.otherwise('/tab/balance');
  //$urlRouterProvider.otherwise('/login');

})

.config(function($httpProvider)
{
	$httpProvider.interceptors.push('LoadingInterceptor');
});
