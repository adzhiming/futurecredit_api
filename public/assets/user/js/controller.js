(function(angular) {
    "use strict";
    /*创建模块 注入路由*/
    var app = angular.module('app', ['ui.router', 'ngTouch','angular-loading-bar']);
    var rootTiao = '';
    var isxindai = '';
    app.controller('appController', ['$scope', '$window', '$http', function($scope, $window, $http) {

        /*记录当前点击的类型*/
        $scope.type = "card";
        $scope.loginCen = false;
        $scope.loginBox = false;
        $scope.lodinCen = false;
        $scope.spinner = false;
        $scope.tabbarClick = function(type) {
            $scope.type = type;
            /*发通知，改标题*/
            switch ($scope.type) {
                case 'card':
                    $scope.type = "card";
                    break;
                case 'loan':
                    $scope.type = "loan";
                    break;
                case 'my':
                    $scope.type = "my";
                    break;
            }
        };
        var href = window.location.href;
        var index = href.indexOf("#/");
        var str = href.substring(index, href.length);
        if (str.indexOf('my') != -1) {
            $scope.type = "my";
        } else if (str.indexOf('bankCenter') != -1 || str.indexOf('card') != -1) {
            $scope.type = "card";
        } else if (str.indexOf('loanLogin') != -1 || str.indexOf('loan') != -1) {
            $scope.type = "loan";
        }
        // 全局的点击弹出框
        $scope.cardUrl = function(id) {
            $scope.id = id;
            sessionStorage.setItem("id", id);
            $http({
                method: 'GET',
                url: api_url + 'user/check_all_login'
            }).then(function(data) {
                console.log(data);
                //没在登录状态
                if (data.data.code == -5) {
                    $scope.loginCen = true;
                    $scope.loginBox = true;
                } else if (data.data.code == 1) {
                    $scope.lodinCen = true;
                    $scope.spinner = true;
                    $scope.id = sessionStorage.getItem("id");
                    $http({
                        method: 'post',
                        url: api_url + 'user/bank_url',
                        data: { id: $scope.id }
                    }).then(function(data) {
                        $scope.bankurl = data.data.data.url;
                        window.location.href = data.data.data.url;
                        $scope.lodinCen = false;
                        $scope.spinner = false;
                    })
                }
            });
        }
        $scope.cancel = function() {
            $scope.loginCen = false;
            $scope.loginBox = false;
            $('.name').val('');
            $('#telphone').val('');
            $('#yzm').val('');
            rootTiao = '';
            isxindai = '';
        }
        var timer;
        var nums = 60;
        var isbtn = true;
        $scope.sendCodeNew = function() {
            var username = $('.name').val();
            var iphoneVal = $('#telphone').val();
            var code = $('.gecode');
            if (username == "") {
                alert("请输入真实姓名");
                return
            } else if (!(/^1[34578]\d{9}$/.test(iphoneVal))) {
                alert("请正确填写手机号码")
                return;
            } else if (username == "" && iphoneVal == "") {
                alert("请输入手机号码和姓名")
                return
            }
            if (isbtn) {
                isbtn = false;
                $http.post(api_url + "user/crode",{phone: iphoneVal}).then(function(data){
                    data = data.data;
                    if (data.code == 2001 || iphoneVal == "") {
                        $('#telphone').val("")
                        clearInterval(timer); //清除js定时器
                        isbtn = true;
                        $scope.tips = '获取验证码';
                        nums = 60; //重置时间
                        $('#code').html('发送失败')
                        return
                    } else if (data.code == 2002) {
                        $('#telphone').val("")
                        alert("手机号码不能为空");
                        clearInterval(timer); //清除js定时器
                        isbtn = true
                        code.html = '获取验证码';
                        nums = 60; //重置时间
                        $('#code').html('发送失败')
                        return
                    } else if (data.code == 2003) {
                        $('#telphone').val("")
                        clearInterval(timer); //清除js定时器
                        code.html('获取验证码');
                        isbtn = true
                        nums = 60; //重置时间
                        $('#code').html('发送失败')
                        return
                    } else if (data.code == 1) {
                        timer = setInterval(doLoop, 1000);
                        code.disabled = "disabled";
                        $("#code").html("验证码已经发送到手机" + $('#telphone').val().substring(0, 3) + "****" + $('#telphone').val().substring(7, 11));
                    }
                })
            }
            function doLoop() {
                nums--;
                if (nums > 0) {
                    $('.gecode').html(nums + ' s后重发');
                } else {
                    clearInterval(timer); //清除js定时器
                    isbtn = true;
                    $('.gecode').html('获取验证码');
                    nums = 60; //重置时间myController
                }
            }
        }
        $scope.login = function() {
            var username = $('.name').val();
            var iphoneVal = $('#telphone').val();
            var code = $('.gecode');
            var yzm = $('#yzm').val();
            if (username == "") {
                alert('请输入真实姓名');
                return;
            } else if (iphoneVal == "") {
                alert('请输入手机号码');
                return;
            } else if (!(/^1[34578]\d{9}$/.test(iphoneVal))) {
                alert("请正确填写手机号码");
                return;
            } else if (yzm == "") {
                alert('请输入正确验证码');
                return;
            }
            var getUser = '';
            if (isxindai == 'yes') {
                getUser = 'user/loan_url';
            } else {
                getUser = 'user/bank_url';
            }
            $http.post( api_url + 'user/check_login' ,{phone: iphoneVal,code: yzm,username: username}).then(function(data){
                data = data.data;
                    $scope.lodinCen = true;
                    $scope.spinner = true;
                    if (data.code == 2005) {
                        $scope.id = sessionStorage.getItem("id");
                        $http({
                            method: 'post',
                            url: api_url + getUser,
                            data: { id: $scope.id }
                        }).then(function(data) {
                            if (rootTiao == 'speed') {
                                rootTiao = '';
                                // $('.loding-cen').show();
                                window.location.href = "index#!/myLoan/creditCard";
                                return
                            }
                            rootTiao = '';
                            getUser = '';
                            $scope.bankurl = data.data.data.url;
                            window.location.href = data.data.data.url;
                            $scope.lodinCen = false;
                            $scope.spinner = false;
                            $scope.loginCen = false;
                            $scope.loginBox = false;
                        })
                    } else if (data.code == 2006) {
                        alert("验证码不一致")
                        $scope.lodinCen = false;
                        $scope.spinner = false;
                    }
            })
        }
    }]);
    //免年费年卡
    app.controller('freeController', ['$scope', '$http', function($scope, $http) {
        $http({
            method: 'GET',
            url: api_url + 'user/index'
        }).then(function(data) {
            //精品推荐
            $scope.bank_cards_hot = data.data.data.bank_cards_hot;
            //银行中心
            $scope.bank_list = data.data.data.bank_list;
            //主题精选
            $scope.index_theme = data.data.data.index_theme;
            //    热门精选
            $scope.bank_card = data.data.data.bank_card;
            //弹出登录框或者直接登录
        })
    }]);
    //白金信用卡
    app.controller("platinumController", ['$scope', '$http', function($scope, $http) {
        $http({
            method: 'post',
            url: api_url + 'user/card_list',
            data: { check: 2 },
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            transformRequest: function(obj) {
                var str = [];
                for (var p in obj) {
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data) {
            $scope.card_list = data.data.data;
        });
    }])
    //银行中心
    app.controller('bankCenterController', ['$scope', '$stateParams', '$http', function($scope, $stateParams, $http) {
        $scope.id = $stateParams.bankid;
        $http({
            method: 'post',
            url: api_url + 'user/bank_to_card',
            data: { id: $scope.id },
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            transformRequest: function(obj) {
                var str = [];
                for (var p in obj) {
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data) {
            $scope.bank = data.data.data.bank;
            $scope.bankcenter = data.data.data.bank_card;
        })
    }])
    //贷款的极速到账和低息贷款
    app.controller('loanController', ["$scope", "$http", function($scope, $http) {
        $scope.tz = function(id) {
            $scope.id = id;
            $http({
                method: 'GET',
                url: api_url + 'user/check_all_login'
            }).then(function(data) {
                console.log(data);
                //没在登录状态
                if (data.data.code == -5) {
                    // window.location.href="index#!/wodedxlogin";
                    $('.login-cen,.login-box').show();
                    rootTiao = 'speed';
                } else if (data.data.code == 1) {
                    window.location.href = "index#!/myLoan/creditCard";
                }
            });
        }
        //极速到账
        $http({
            method: 'post',
            url: api_url + 'user/loan_list',
            data: { check: 1 },
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            transformRequest: function(obj) {
                var str = [];
                for (var p in obj) {
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data) {
            $scope.daoz = data.data.data
        });
        // 低息贷款请求数据
        $http({
            method: 'post',
            url: api_url + 'user/loan_list',
            data: { check: 2 },
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            transformRequest: function(obj) {
                var str = [];
                for (var p in obj) {
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data) {
            $scope.dix = data.data.data
        });
    }])
    //贷款登录
    app.controller('loanLoginController', ['$scope', '$stateParams', '$http', function($scope, $stateParams, $http) {
        $scope.id = $stateParams.loanid;
        $scope.loginCen = false;
        $scope.loginBox = false;
        $scope.lodinCen = false;
        $scope.spinner = false;

        //点击提交申请
        $scope.dklogin = function(id) {
            $scope.id = id;
            sessionStorage.setItem("id", id);
            $http({
                method: 'GET',
                url: api_url + 'user/check_all_login'
            }).then(function(data) {
                //没在登录状态
                if (data.data.code == -5) {
                    isxindai = 'yes';
                    $scope.loginCen = true;
                    $scope.loginBox = true;
                    // window.location.href="index#!/xdlogin";
                } else {
                    isxindai = 'no'
                    $scope.lodinCen = true;
                    $scope.spinner = true;
                    //登录成功跳转
                    $scope.id = sessionStorage.getItem("id")
                    console.log($scope.id);
                    $http({
                        method: 'post',
                        url: api_url + 'user/loan_url',
                        data: { id: $scope.id },
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        transformRequest: function(obj) {
                            var str = [];
                            for (var p in obj) {
                                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                            }
                            return str.join("&");
                        }
                    }).then(function(data) {
                        console.log(data);
                        $scope.bankurl = data.data.data.url;
                        console.log($scope.bankurl);
                        window.location.href = data.data.data.url;
                        $scope.lodinCen = false;
                        $scope.spinner = false;
                    })
                }
            });
        }
        $scope.cancel = function() {
            $scope.loginCen = false;
            $scope.loginBox = false;
            $('.name').val('');
            $('#telphone').val('');
            $('#yzm').val('');
            rootTiao = '';
            isxindai = '';
        }
        $scope.numj = false;
        $scope.money = 0;
        //计算器数据
        $http({
            method: 'post',
            url: api_url + 'user/loan_product_url',
            data: { id: $scope.id }
        }).then(function(data) {
            console.log(data)
            $scope.loanLogin = data.data.data;
            $scope.flows = data.data.data.loan_flow;
            $scope.product_comment = data.data.data.product_comment;

            $scope.moon = data.data.data.repayment_cycle_range;//时间组
            $scope.interest_rate = (data.data.data.interest_rate)/100;//利率
            $scope.interest_free_days = data.data.data.interest_free_days ;//免息
            $scope.unit_rate = data.data.data.unit_rate;//状态
            $scope.typeName = $scope.unit_rate == '1'?'月':$scope.unit_rate == '2'?'周':'日';
            $scope.max_loan_price = data.data.data.max_loan_price;
            $scope.min_loan_price = data.data.data.min_loan_price;
            $scope.moons = $scope.moon[0];
        })
        // 计算器
        $scope.Calculator = function(){
            $scope.numj = true;
        }
        $scope.closeNum = function(){
            $scope.numj = false;
        }
        $scope.huankuan = 0;
        $scope.zonglixi = 0;
        $scope.pinjuyue = 0;

        $scope.selectMoon = function(){

            if($scope.unit_rate == '1'){
                $scope.zonglixi = (($scope.money * $scope.interest_rate * $scope.moons)/(30 * $scope.moons) * ((30 * $scope.moons) - $scope.interest_free_days)).toFixed(2);
                $scope.huankuan = (Number($scope.zonglixi)/3).toFixed(2);
            }else if($scope.unit_rate == '2'){
                $scope.zonglixi = (($scope.money * $scope.interest_rate * $scope.moons)/(7 * $scope.moons) * ((7 * $scope.moons) - $scope.interest_free_days)).toFixed(2);
                $scope.huankuan = (Number($scope.zonglixi)/3).toFixed(2);
            }else if($scope.unit_rate == '3'){
                $scope.huankuan = ($scope.money * $scope.interest_rate).toFixed(2);
                $scope.zonglixi = ($scope.huankuan * $scope.moons).toFixed(2);
            }
            $scope.pinjuyue = ((Number($scope.money) + Number($scope.zonglixi))/3).toFixed(2);

            if(isNaN($scope.huankuan)){
                $scope.tip = '请输入正确的金额';
                $scope.huankuan = 0;
                $scope.zonglixi = 0;
                $scope.pinjuyue = 0;
                return
            }else if(isNaN($scope.zonglixi)){
                $scope.tip = '请输入正确的金额';
                $scope.huankuan = 0;
                $scope.zonglixi = 0;
                $scope.pinjuyue = 0;
                return
            }else if(Number($scope.money) > $scope.max_loan_price || Number($scope.money) < $scope.min_loan_price){
                $scope.tip = '请输入范围内的金额';
                $scope.huankuan = 0;
                $scope.zonglixi = 0;
                $scope.pinjuyue = 0;
                return
            }else{
                $scope.tip = '';
            }

        }
        $scope.moneyfun = function(){

            if($scope.unit_rate == '1'){
                $scope.zonglixi = (($scope.money * $scope.interest_rate * $scope.moons)/(30 * $scope.moons) * ((30 * $scope.moons) - $scope.interest_free_days)).toFixed(2);
                $scope.huankuan = (Number($scope.zonglixi)/3).toFixed(2);
            }else if($scope.unit_rate == '2'){
                $scope.zonglixi = (($scope.money * $scope.interest_rate * $scope.moons)/(7 * $scope.moons) * ((7 * $scope.moons) - $scope.interest_free_days)).toFixed(2);
                $scope.huankuan = (Number($scope.zonglixi)/3).toFixed(2);
            }else if($scope.unit_rate == '3'){
                $scope.huankuan = ($scope.money * $scope.interest_rate).toFixed(2);
                $scope.zonglixi = ($scope.huankuan * $scope.moons).toFixed(2);
            }
            $scope.pinjuyue = ((Number($scope.money) + Number($scope.zonglixi))/3).toFixed(2);
            
            if(isNaN($scope.huankuan)){
                $scope.tip = '请输入正确的金额';
                $scope.huankuan = 0;
                $scope.zonglixi = 0;
                $scope.pinjuyue = 0;
                return
            }else if(isNaN($scope.zonglixi)){
                $scope.tip = '请输入正确的金额';
                $scope.huankuan = 0;
                $scope.zonglixi = 0;
                $scope.pinjuyue = 0;
                return
            }else if(Number($scope.money) > $scope.max_loan_price || Number($scope.money) < $scope.min_loan_price){
                $scope.tip = '请输入范围内的金额';
                $scope.huankuan = 0;
                $scope.zonglixi = 0;
                $scope.pinjuyue = 0;
                return
            }else{
                $scope.tip = '';
            }
        }
        function fun(){
            console.log(1)
        }
    }]);
    //我的贷款
    app.controller('myLoanController', ['$scope', '$stateParams', '$http', function($scope, $stateParams, $http) {
        $scope.xyk = true;
        $scope.dk = false;
        if ($stateParams.type == 'type1') {
            $scope.xyk = true;
            $scope.dk = false;
        } else if ($stateParams.type == 'type2') {
            $scope.xyk = false;
            $scope.dk = true;
        }
        //贷款请求数据
        $http({
            method: 'GET',
            url: api_url + 'user/loan_log'
        }).then(function(data) {
            $scope.loan_log = data.data.data
        })
        //信用卡请求数据
        $http({
            method: 'GET',
            url: api_url + 'user/my_card'
        }).then(function(data) {
            $scope.my_card = data.data.data
        })
        //申请请求数据
        $http({
            method: 'GET',
            url: api_url + 'user/applying_list'
        }).then(function(data) {
            $scope.applying_list = data.data.data
        })
        //否决请求数据
        $http({
            method: 'GET',
            url: api_url + 'user/reject_list'
        }).then(function(data) {
            $scope.reject_list = data.data.data
        })
    }]);
    //我的个人中心
    app.controller('myController', ['$scope', '$http', function($scope, $http) {
        $scope.islogin = false;
        $scope.isUserBox = false;
        $http({
            method: 'GET',
            url: api_url + 'user/check_all_login'
        }).then(function(data) {
            //没在登录状态
            if (data.data.code == -5) {
                $scope.islogin = true;
                $scope.isUserBox = false;
            } else {
                $scope.islogin = false;
                $scope.isUserBox = true;
            }
        });
        //倒计时
        var timers;
        var numss = 60;
        var isbtns = true;
        $scope.codes = '获取验证码';
        //点击获取验证码
        $scope.sendCodes = function(e) {
            var code = $('.' + e).find("#yzmcode");
            if ($scope.realname == "") {
                alert("请输入真实姓名");
                return
            } else if (!(/^1[34578]\d{9}$/.test($scope.telphone))) {
                alert("请正确填写手机号码")
                return;
            } else if ($scope.realname == "" && $scope.telphone == "") {
                alert("请输入手机号码和姓名")
                return
            }
            code.val(numss + ' s后重发');
            if (isbtns) {
                isbtns = false;
                $.ajax({
                    url: api_url + "user/crode",
                    type: 'POST',
                    data: {
                        phone: $scope.telphone
                    },
                    success: function(data) {
                        if (data.code == 2001 || $scope.telphone == '') {
                            $scope.realname = '';
                            alert("短信发送失败");
                            clearInterval(timers); //清除js定时器
                            code.disabled = true;
                            code.val('获取验证码');
                            isbtns = true;
                            numss = 60; //重置时间
                            return
                        } else if (data.code == 2002) {
                            $scope.realname = '';
                            alert("手机号码不能为空");
                            clearInterval(timers); //清除js定时器
                            code.disabled = true;
                            isbtns = true;
                            code.value = '获取验证码';
                            numss = 60; //重置时间
                            return
                        } else if (data.code == 2003 || $scope.telphone == '' || $scope.telphone.length < 11) {
                            $scope.realname = '';
                            alert("手机号码格式错误");
                            clearInterval(timers); //清除js定时器
                            code.disabled = true;
                            isbtns = true;
                            code.val('获取验证码');
                            numss = 60; //重置时间
                            return
                        } else if (data.code == 1) {
                            code.disabled = "disabled";
                            timers = setInterval(doLoop, 1000); //一秒执行一次
                            $('.' + e).find("#code").html("验证码已经发送到手机" + $('.' + e).find('input[name="name"]').val().substring(0, 3) + "****" + $('.' + e).find('input[name="name"]').val().substring(7, 11));
                        }
                    }
                });
            }
            //    验证码倒计时
            function doLoop() {
                numss--;
                if (numss > 0) {
                    code.val(numss + ' s后重发');
                    code.disabled = true;
                } else {
                    clearInterval(timers); //清除js定时器
                    code.disabled = true;
                    isbtns = true;
                    code.val('获取验证码');
                    numss = 60; //重置时间
                }
            }
        };
        // 3.点击立即登录
        $scope.sq = function() {
            if ($scope.realname == "") {
                alert('请输入真实姓名')
                return;
            } else if ($scope.telphone == "") {
                alert('请输入手机号码')
                return;
            } else if (!(/^1[34578]\d{9}$/.test($scope.telphone))) {
                alert("请正确填写手机号码")
                return;
            } else if ($scope.yzm == "") {
                alert('请输入正确验证码')
                return;
            }
            $.ajax({
                type: 'POST',
                url: api_url + 'user/check_login',
                data: {
                    phone: $scope.telphone,
                    code: $scope.yzm,
                    username: $scope.realname
                },
                headers: {
                    "Access-Control-Allow-Origin": "http://example.edu",
                    "Access-Control-Allow-Headers": "X-Requested-With"
                },
                //4、如果成功跳转页面
                success: function(data) {
                    if (data.code == 2005) {
                        // 登录成功跳转
                        $scope.isUserBox = true;
                        $scope.islogin = false;
                        window.location.reload();
                    } else if (data.code == 2006) {
                        alert("验证码不一致")
                    }
                }
            });
        }
        $scope.name = "登 录"
        //请求数据
        $http({
            method: 'GET',
            url: api_url + 'user/user_list'
        }).then(function(data) {
            //银行中心
            $scope.code = data.data
            $scope.user_list = data.data.data;
        })
        //退出
        $scope.pop = function() {
            $('.popup').css({ "display": "block" })
        }
        $scope.no = function() {
            $('.popup').css({ "display": "none" });
            $('.p3').css({ "display": "none" })
        }
        //    退出账号
        $scope.yes = function() {
            $http({
                method: 'GET',
                url: api_url + 'user/out_login'
            }).then(function(data) {
                console.log(data);
                if (data.data.code == 1) {
                    window.location.href = web_url + "user/index";
                } else if (data.data.code == -8) {
                    $('.p3').css({ "display": "block" })
                }
            })
        }
        $scope.close = function() {
            window.location.href = "index#!/card/free"; 
        }

        // 上传头像
        document.getElementById('upimg').onchange = function(e){
            var fileObj=e.target.files[0];
            var formData = new FormData();
            formData.append('file',fileObj);
            var ajax=new XMLHttpRequest();
            ajax.open("POST",api_url + 'user/head_img_base',true);
            ajax.send(formData);
            $('#tishi').text('正在上传');
            ajax.onreadystatechange=function(){
                if (ajax.readyState == 4) {
                    var data = JSON.parse(ajax.responseText);
                    if(data.code == 1){
                        alert('头像修改成功');
                        $('#tishi').text('');
                        $('#preview').attr('src','http://'+data.data.images)
                    }else{
                        alert(data.msg);
                    }
                }
            }
        }
    }]);
    app.config(["$stateProvider","$urlRouterProvider","cfpLoadingBarProvider",function($stateProvider,$urlRouterProvider,cfpLoadingBarProvider) {

        cfpLoadingBarProvider.spinnerTemplate = '<div class="loding-cen" style="display:block;"></div><div class="spinner" style="display:block;"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>';
        $urlRouterProvider.when("", "/card/free");
        $stateProvider
            .state('card', {
                url: '/card',
                templateUrl: web_url+'user/card'
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
                url: '/creditCard?type',
                templateUrl: web_url+'user/creditCard',
                controller:"myLoanController"

            })
            // 我的贷款-贷款
            .state('myLoan.money', {
                url: '/money?type',
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
            //注册
            .state('signIn', {
                url: '/signIn',
                templateUrl: web_url+'user/signIn',
                controller:"signInController"
            })
            .state('login', {
                url: '/login',
                templateUrl: web_url+'user/login',
                controller:"logincontor"
            })
    }]);
    app.directive('login',function () {
        return {
            restrict:'EA',
            templateUrl:web_url+'user/login',
            link:function(scope){
                $(".cancel").on("click",function () {
                    $('.all').css({
                        "display":"none"
                    })
                });
            }
        }
    });
})(angular)