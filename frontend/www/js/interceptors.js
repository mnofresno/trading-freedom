angular.module('trading-freedom.interceptors', [])

.factory('LoadingInterceptor', function($rootScope, $q) {
	return {
        request: function(config) {
			$rootScope.$broadcast('loading:show');
			return config;
        },
		response: function(response) {
			$rootScope.$broadcast('loading:hide');
			return response;
		},
		responseError: function(rejection) {
			$rootScope.$broadcast('loading:hide');
			
			var deferred = $q.defer();
			deferred.reject(rejection);
			return deferred.promise;
		}
    };
});