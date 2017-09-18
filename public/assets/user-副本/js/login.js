(function (angular) {
    "use strict";
    /*创建应用导航指令*/
    angular.module('app').directive('login',function () {
        return {
            restrict:'EA',
            templateUrl:web_url+'user/login',

        }
    });
})(angular);