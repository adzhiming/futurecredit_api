

(function (angular) {
    "use strict";
    /*创建应用导航指令*/
    angular.module('app').directive('login',function () {
        return {
            restrict:'EA',
            templateUrl:'tel/login.html',
            link:function(){
                $(".cancel").on("click",function () {
                    $('.all').css({
                        "display":"none"
                    })
                });
            }
        }
    });
})(angular);