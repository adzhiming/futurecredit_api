(function(angular){

//    路由配置

    angular.module('app').config(["$stateProvider","$urlRouterProvider",function($stateProvider,$urlRouterProvider) {
        'use strict';
        $urlRouterProvider.when("", "/card");

        $stateProvider
            .state('card', {
            url: '/card',
            templateUrl: web_url+'user/card',
            controller: function($state){
                    $state.go('card.free');//默认显示第一个tab
                }

            })
            //免年费信用卡
            .state('card.free', {
                url: '/free',
                templateUrl: web_url+'user/free',
                controller:'freeController'
            })
            //银行中心
            .state('bankCenter', {
                url: '/bankCenter?bankid',
                templateUrl: web_url+'user/bankCenter',
                controller:'bankCenterController'
            })

            //白金信用卡
            .state('card.platinum', {
                url: '/platinum',
                templateUrl: web_url+'user/platinum',
                controller:"platinumController"
            })
            //贷款
            .state('loan', {
            url: '/loan',
            templateUrl: web_url+'user/loan',
                controller: function($state){
                    $state.go('loan.speed');//默认显示第一个tab
                }
        })
            //极速到账
            .state('loan.speed', {
                url: '/speed',
                templateUrl: web_url+'user/speed',
                controller:'loanController'
            })
            //低息贷款
            .state('loan.lowMoney', {
                url: '/lowMoney',
                templateUrl: web_url+'user/lowMoney',
                controller:'loanController'
            })
            //我的个人中心
            .state('my', {
                url: '/my',
                templateUrl: web_url+'user/my',
                controller:"myController"
            })
            //我的贷款
            .state('myLoan', {
            url: '/myLoan',
            templateUrl: web_url+'user/myLoan',
                controller:"myLoanController"

            })
            //我的贷款信用卡
            .state('myLoan.creditCard', {
                url: '/creditCard',
                templateUrl: web_url+'user/creditCard',
                controller:"myLoanController"
            })
            // 我的贷款-贷款
            .state('myLoan.money', {
                url: '/money',
                templateUrl: web_url+'user/money',
                controller:"myLoanController"
            })
            // 我的贷款-申请中
            .state('myLoan.apply', {
                url: '/apply',
                templateUrl: web_url+'user/apply',
                controller:"myLoanController"
            })
            //我的贷款-否决
            .state('myLoan.veto', {
                url: '/veto',
                templateUrl: web_url+'user/veto',
                controller:"myLoanController"
            })
            // 贷款登录
            .state('loanLogin', {
                url: '/loanLogin?loanid',
                templateUrl: web_url+'user/loanLogin',
                controller:"loanLoginController"
            })


    }]);

})(angular)