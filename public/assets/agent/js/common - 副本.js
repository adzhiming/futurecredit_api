var indexapp = angular.module('indexapp',['ui.router']);
var user = store.get('userInfo');

if(!user){ jump(); }


//首页
indexapp.controller('subIndex',['$scope','$rootScope','$http',function($scope,$rootScope,$http){
    console.log(user)
    $http.post( rout+'getAgentInfoById',{id:user.id}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.subIndexInfo = resData.data;
            store.set('userOtherInfo',resData.data);
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })            
        }
    },function(error){
        console.log(error)
    }); 

    $scope.loginout = function(){
        $http.post( rout+'logout',{id:user.id}).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){                
                zdyAlert.init(resData.msg,function(){
                    store.clearAll();
                    window.location.href="login";
                })     
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })                       
            }
        },function(error){
            console.log(error)
        }); 
    }
}])
//分享链接
indexapp.controller('sharePage',['$scope','$http',function($scope,$http){
    $http.post( rout+'getAgentShareUrl',{id:user.id}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.card = resData.data;
            var cardurl = new QRCode(document.getElementById("cardurl"),resData.data.cardurl);
            var loanurl = new QRCode(document.getElementById("loanurl"),resData.data.loanurl);
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            }) 
        }
    },function(error){
        console.log(error)
    }); 
    var op2 = document.querySelectorAll('.op2');
    var clipboard = new Clipboard(op2);
    clipboard.on('success', function(e) {
        zdyAlert.init('复制成功',function(){
            console.log('复制成功')
        }) 
    });
    clipboard.on('error', function(e) {
        zdyAlert.init('手机不支持，请直接复制'+e.text,function(){})
    });
}])
//提现
indexapp.controller('withdraw',['$scope','$interval','$http',function($scope,$interval,$http){
    $scope.tip =  true;
    $scope.tips = true;
    $scope.otherUser = store.get('userOtherInfo');

    $scope.getMone = function(){
        $http.post( rout+'getAgentInfoById',{id:user.id}).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                $scope.subIndexInfo = resData.data;
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        }); 
    }
    $scope.getMone();

    $scope.timeS = 60;
    var timer = true;
    $scope.getyzm = function(iphone){
        if(timer){
            timer = false;
            console.log(timer)
            $http.post( routUser+'crode',{phone:iphone}).then(function(data){
                var resData = data.data;
                if(resData.code == 1){
                    $scope.tip = false;
                    var timerinter = $interval(function(){
                        $scope.timeS -- ;
                        if($scope.timeS == 0){
                            $interval.cancel(timerinter); 
                            $scope.tip = true;
                            $scope.timeS = 60;
                            timer = true;
                        }
                    },1000)
                }else{
                    zdyAlert.init(resData.msg,function(){
                        console.log(resData.msg)
                    })
                }
            },function(error){
                console.log(error)
            });
        }
    }
    $scope.money = '';
    $scope.code = '';
    $scope.tixian = function(){
        if($scope.money == ''){
            $scope.tips = false;
            $scope.tipsText = '请输入金额';
            return
        }else if($scope.code == ''){
            $scope.tipsText = '请输入验证码';
            $scope.tips = false;
            return
        }else{
            $scope.tips = true;
        }
        $http.post( rout+'withdraw',{
            id:user.id,
            money:$scope.money,
            code:$scope.code
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                zdyAlert.init('提现成功',function(){
                    $scope.getMone();
                    $scope.money = '';
                    $scope.code = '';
                })  
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        }); 
    }
}])
//报表
indexapp.controller('report',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'agentReport',{id:user.id,page:$scope.page,stime:$scope.stime,etime:$scope.etime}).then(function(data){
        var resData = data.data;
        $scope.conf = resData;
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'agentReport',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.baobiaolist = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.baobiaolist = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){})
        }
    },function(error){
        console.log(error)
    });
    $scope.search  = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){})
            return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){})
            return
        }
        $http.post( rout+'agentReport',{id:user.id,page:$scope.page,stime:$scope.stime,etime:$scope.etime}).then(function(data){
            var resData = data.data;
            $scope.conf = resData;
            if(resData.code == 1){
                if(resData.data.data.length <=0){
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.baobiaolist = [];
                    $scope.pageset = {
                        currentPage:0,
                        count:0,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){}
                    }
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'agentReport',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime}).then(function(data){
                                var resData = data.data;
                                if(resData.code == 1){
                                    $scope.baobiaolist = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };
                    $scope.baobiaolist = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            console.log(error)
        });
    }
}])
//我的用户
indexapp.controller('myuser',['$scope','$http','$location',function($scope,$http,$location){
    var href = $location.absUrl();
    var index = href.indexOf("#/");
    var str = href.substring(index,href.length);
    var navName;
    if(str.indexOf('yonghu') != -1){
        navName = "yonghu";
    }else if(str.indexOf('access') != -1){
        navName = "access";
    }else if(str.indexOf('crdApplyRecord') != -1){
        navName = "crdApplyRecord";
    }else if(str.indexOf('loanApplyRecord') != -1){
        navName = "loanApplyRecord";
    }
    $scope.tap = function(eVale){
        $scope.navName = eVale;
        // console.log($scope.navName)
    }
    $scope.navName = navName;
    $scope.page = 1;
    $scope.user = store.get('userInfo');
}])
//我的用户 - 用户
indexapp.controller('user',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserList',{id:user.id,page:$scope.page}).then(function(data){
        var resData = data.data;
        $scope.conf = resData;
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserList',{id:user.id,page:$scope.pageset.currentPage}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.userList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.userList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){})
        }
    },function(error){
        console.log(error)
    });

    $scope.search = function(){
        if(!$scope.keyup){
            zdyAlert.init('请输入内容',function(){})
            return
        }
        $http.post( rout+'myUserList',{id:user.id,page:$scope.page,keyword:$scope.keyup}).then(function(data){
            var resData = data.data;
            console.log(resData)
            $scope.conf = resData;
            if(resData.code == 1){
                if(resData.data.data.length <=0){
                    zdyAlert.init('没有查到数据',function(){})
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'myUserList',{id:user.id,page:$scope.pageset.currentPage,keyword:$scope.keyup}).then(function(data){
                                var resData = data.data;
                                if(resData.code == 1){
                                    $scope.userList = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.userList = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            console.log(error)
        });
    }
}])
//我的用户 - 访问
indexapp.controller('access',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserAccessLog',{id:user.id,page:$scope.page}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <= 0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserAccessLog',{id:user.id,page:$scope.pageset.currentPage}).then(function(data){
                            var resData = data.data;
                            console.log(resData)
                            if(resData.code == 1){
                                $scope.recordList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.recordList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });
    $scope.search = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){}); return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){}); return
        }
        console.log('开始:'+ $scope.stime+'----结束:'+$scope.etime)
        // $http.post( rout+'myUserAccessLog',{id:$scope.user.id,page:1,stime:$scope.startDate,etime:$scope.endDate}).then(function(data){
        //     console.log(data)
        // })
    }
}])
//我的用户 - 申卡记录
indexapp.controller('crdApplyRecord',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserApplyCardLog',{id:user.id,page:$scope.page}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <= 0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserApplyCardLog',{id:user.id,page:$scope.pageset.currentPage}).then(function(data){
                            var resData = data.data;
                            console.log(resData)
                            if(resData.code == 1){
                                $scope.recordList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.recordList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    $scope.search = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){}); return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){}); return
        }
        console.log('开始:'+ $scope.stime+'----结束:'+$scope.etime)
        // $http.post( rout+'myUserApplyCardLog',{id:$scope.user.id,page:1,stime:$scope.startDate,etime:$scope.endDate}).then(function(data){
        //     console.log(data)
        // })
    }
}])
//我的用户 - 申贷记录
indexapp.controller('loanApplyRecord',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserApplyLoanLog',{id:$scope.user.id,page:1}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <= 0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserApplyLoanLog',{id:user.id,page:$scope.pageset.currentPage}).then(function(data){
                            var resData = data.data;
                            console.log(resData)
                            if(resData.code == 1){
                                $scope.recordList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.recordList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    }); 

    $scope.search = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){}); return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){}); return
        }
        console.log('开始:'+ $scope.stime+'----结束:'+$scope.etime)
        // $http.post( rout+'myUserApplyLoanLog',{id:$scope.user.id,page:1,stime:$scope.startDate,etime:$scope.endDate}).then(function(data){
        //     console.log(data)
        // })
    }
}])
//收款账户
indexapp.controller('collectionAccount',['$scope','$http',function($scope,$http){
    var otherUser = store.get('userOtherInfo');
    $scope.istip = true;
    $scope.tip = '';
    console.log(otherUser)
    $scope.sk = {
        name:otherUser.name,
        id_card:otherUser.id_card,
        card_no:otherUser.card_no,
        card_name:otherUser.card_name,
        card_sub_name:otherUser.card_sub_name
    }
    $scope.submit = function(){
        var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/; 
        if($scope.sk.name == ''){
            $scope.tip = '开户名不能为空';
            $scope.istip = false;
            return
        }else if($scope.sk.id_card == ''){
            $scope.tip = '身份证不能为空';
            $scope.istip = false;
            return
        }else if( !(reg.test($scope.sk.id_card)) ){
            $scope.tip = '身份证格式错误';
            $scope.istip = false;
            return
        }else if($scope.sk.card_no == ''){
            $scope.tip = '卡号不能为空';
            $scope.istip = false;
            return
        }else if($scope.sk.card_name == ''){
            $scope.tip = '银行卡不能为空';
            $scope.istip = false;
            return
        }else if($scope.sk.card_sub_name == ''){
            $scope.tip = '支行不能为空';
            $scope.istip = false;
            return
        }else{
            $scope.istip = true;
            $scope.tip = '';
        }

        var data = {
            id:user.id,
            name:$scope.sk.name,
            id_card:$scope.sk.id_card,
            card_no:$scope.sk.card_no,
            card_name:$scope.sk.card_name,
            card_sub_name:$scope.sk.card_sub_name
        }
        console.log(data)
        $http.post( rout+'editUserCommit',data).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        }); 
    }
}])
//代理
indexapp.controller('daili',['$scope','$location',function($scope,$location){
    var href = $location.absUrl();
    var index = href.indexOf("#/");
    var str = href.substring(index,href.length);
    var navName;
    if(str.indexOf('myAgent') != -1){
        navName = "myAgent";
    }else if(str.indexOf('agentAdd') != -1){
        navName = "agentAdd";
    }else if(str.indexOf('quotedPrice') != -1){
        navName = "quotedPrice";
    }
    $scope.tap = function(eVale){
        $scope.navName = eVale;
    }
    $scope.navName = navName;
}])
//代理 - 我的代理
indexapp.controller('myAgent',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $scope.page = $stateParams.page || 1;
    $scope.dlList = [];
    $http.post( rout+'myAgenList',{id:user.id,page:$scope.page}).then(function(data){
        var resData = data.data;
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
                $scope.dlList = [];
                $scope.pageset = {
                    currentPage:0,
                    count:0,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){}
                }
            }else{
                $scope.pageset = {
                    currentPage:resData.data.current_page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $scope.dlList = [];
                        $http.post( rout+'myAgenList',{id:user.id,page:$scope.pageset.currentPage}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.dlList = resData.data.data;
                                $scope.page = resData.data.current_page
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.dlList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){console.log(resData.msg)})
        }
    },function(error){
        console.log(error)
    });

    $scope.clip = function(text){
        var clipboard = new Clipboard('.fenxian', {
            text: function() {
                return text;
            }
        });
        clipboard.on('success', function(e) {
            console.log(e);
        });
        clipboard.on('error', function(e) {
            console.log(e);
        });
    }

    // 重新授权
    $scope.restid = function(did){
        console.log(did)
        $http.post( rout+'editUserCommit',{id:did,status:1}).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        });
    }
}])
//代理 - 我的代理 - 修改
indexapp.controller('myAgent_edit',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    
    var did = $stateParams.did;
    $scope.page = $stateParams.page;
    $http.post( rout+'getAgentInfoById',{id:did}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.dl = {
                name:resData.data.name,
                phone:resData.data.phone
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    }); 
    $scope.submit = function(){

        if($scope.dl.name == ''){
            $scope.tip = '请输入姓名';
            return 
        }else if($scope.dl.phone == ''){
            $scope.tip = '请输入手机号';
            return
        }else if( !(/^1[34578]\d{9}$/.test($scope.dl.phone)) ){
            $scope.tip = '手机号有误';
            return
        }else if($scope.dl.password !== $scope.dl.repassword){
            $scope.tip = '密码不一致';
            return
        }else{
            $scope.tip = '';
        }

        $scope.dl.id =  did;
        $http.post( rout+'editUserCommit',$scope.dl).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    window.history.back();
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        });         
    }
}])
//代理 - 新增代理
indexapp.controller('agentAdd',['$scope','$http',function($scope,$http){
    $scope.tip = '';
    $scope.dl = {
        id:user.id,
        name:'',
        phone:'',
        password:'',
        repassword:''
    }    
    $scope.submit = function(){
        if($scope.dl.name == ''){
            $scope.tip = '请输入姓名';
            return 
        }else if($scope.dl.phone == ''){
            $scope.tip = '请输入手机号';
            return
        }else if( !(/^1[34578]\d{9}$/.test($scope.dl.phone)) ){
            $scope.tip = '手机号有误';
            return
        }else if($scope.dl.password !== $scope.dl.repassword){
            $scope.tip = '密码不一致';
            return
        }else{
            $scope.tip = '';
        }

        $http.post( rout+'addMyAgentCommit',$scope.dl).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    $scope.dl = {
                        id:user.id,
                        name:'',
                        phone:'',
                        password:'',
                        repassword:''
                    }  
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        }); 
    }
}])
//代理 - 报价单
indexapp.controller('quotedPrice',['$location','$stateParams','$scope','$http',function($location,$stateParams,$scope,$http){

    // $scope.tip = {
    //     text:'',
    //     isHide:true
    // };
    // $http.post( rout+'agentReport',{id:user.id,page:$scope.page,stime:$scope.stime,etime:$scope.etime}).then(function(data){
    //     var resData = data.data;
    //     $scope.conf = resData;
    //     if(resData.code == 1){
    //         if(resData.data.data.length <=0){
    //             $scope.tip = {
    //                 text:'暂时没有数据',
    //                 isHide:false
    //             };
    //         }else{
    //             $scope.pageset = {
    //                 currentPage:resData.data.page,
    //                 count:resData.data.page_count,
    //                 pageList:[],
    //                 selectArr:[],
    //                 onChange:function(){
    //                     $http.post( rout+'agentReport',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime}).then(function(data){
    //                         var resData = data.data;
    //                         if(resData.code == 1){
    //                             $scope.baobiaolist = resData.data.data;
    //                         }else{
    //                             zdyAlert.init(resData.msg,function(){})
    //                         }
    //                     })
    //                 }
    //             }
    //             $scope.tip = {
    //                 text:'',
    //                 isHide:true
    //             };
    //             $scope.baobiaolist = resData.data.data;
    //         }
    //     }else{
    //         zdyAlert.init(resData.msg,function(){})
    //     }
    // },function(error){
    //     console.log(error)
    // });

    $scope.user = store.get('userOtherInfo');
    console.log($stateParams.nav)
    if($stateParams.nav == 'xy'){
        $('.xinyongka-nav').addClass('active');
        $('.xinyongka').show();
    }else if($stateParams.nav == 'dk'){
        $('.dakuan-nav').addClass('active');
        $('.dakuan').show();
    }
    $scope.navtap = function(data){
        $('.baojia-box').hide();
        $('.'+data+'-nav').addClass('active').siblings().removeClass('active');
        $('.'+data).show();
        $('.more').removeClass('dh');
        $('.canpin-list-box').hide();
    }
    $('.close-bianji').bind('click',function(){
        $('.cen,.bianji-box').hide();
    })

    $scope.tip = {
        text:'',
        isHide:true
    };
    $scope.page = 0;
    // 请求数据 信用和贷款数据
    $scope.initxy = function(){
        $http.post( rout+'quotationList',{id:user.id,page:$scope.page,type:1}).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                // if(resData.data.data.length <=0){
                //     $scope.tip = {
                //         text:'暂时没有数据',
                //         isHide:false
                //     };
                // }else{

                // }
                $scope.xydefault = resData.data.default_pricelist;
                $scope.xygiven = resData.data.given_user_pricelist;
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        });
    }
    $scope.initdk = function(){
        $http.post( rout+'quotationList',{id:user.id,page:$scope.page,type:2}).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                $scope.dkdefault = resData.data.default_pricelist;
                $scope.dkgiven = resData.data.given_user_pricelist;
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        });
    }
    $scope.initxy();
    $scope.initdk();

    // 普通用户的编辑
    $scope.bianji = function(did,types,index){
        $('.cen,.bianji-box').show();
        if(types == 'xy'){
            console.log($scope.xydefault[index]);
            $scope.thisInfo = $scope.xydefault[index];
        }else if(types == 'dk'){
            console.log($scope.dkdefault[index]);
            $scope.thisInfo = $scope.dkdefault[index];
        }
    }
    $scope.bianjisubmit = function(did){
        // if($scope.thisInfo.level3_price > $scope.thisInfo.level2_price || $scope.thisInfo.level3_price > $scope.thisInfo.level1_price || $scope.thisInfo.level2_price > $scope.thisInfo.level2_price )

        $http.post( rout+'editSysPrice',{
            id:did,
            agent_id:user.id,
            level1_price:$scope.thisInfo.level1_price,
            level2_price:$scope.thisInfo.level2_price,
            level3_price:$scope.thisInfo.level3_price
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    $('.cen,.bianji-box').hide();
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        }); 
    }
    //特定用户删除 
    $scope.delete = function(did,types){
        zdycomfig.init('确认要删除吗？',function(){
            $http.post( rout+'delAgentPrice',{
                id:did
            }).then(function(data){
                var resData = data.data;
                console.log(resData)
                if(resData.code == 1){
                    zdyAlert.init(resData.msg,function(){
                        if(types == 'xy'){
                            $scope.initxy();
                        }else if(types == 'dk'){
                            $scope.initdk();
                        }
                    })
                }else{
                    zdyAlert.init(resData.msg,function(){
                        console.log(resData.msg)
                    })
                }
            },function(error){
                console.log(error)
            }); 
        })
    }
}])
//代理 - 报价单 - 编辑 - 信用卡
indexapp.controller('teding_edit',['$scope','$stateParams','$state','$http',function($scope,$stateParams,$state,$http){
    $scope.name = $stateParams.name;
    $scope.lev0_p = $stateParams.lv0_p;
    $scope.lev1_p = $stateParams.lv1_p;
    $scope.lev2_p = $stateParams.lv2_p;
    $scope.lev3_p = $stateParams.lv3_p;
    $scope.agentid = $stateParams.agentid;
    $scope.userlev = user.lev;
    $scope.tip = '';
    $scope.activeUser = $stateParams.name;
    $scope.activeUserId = $stateParams.agentid;
    $scope.activeBank = {
        bank_name : $stateParams.bank_name
    }
    $scope.activeBankId = $stateParams.bank_id;
    // 读取银行数据
    $http.post( rout+'getCreditList',{
        id:$stateParams.did
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.banklist = resData.data;
            var arr = $scope.banklist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.banklist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.banklist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });
    // 银行卡搜索
    $scope.bankSearch = function(){
        // $scope.bankKeup
    }
    // 用户搜索
    $scope.userSearch = function(){
        // $scope.userKeup
    }


    // 读取用户数据
    $http.post( rout+'myAgenList',{
        id:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.userlist = resData.data.data;
            var arr = $scope.userlist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.userlist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.userlist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    // 银行选择
    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.activeBankId = did;
        $scope.activeBankIndex = index;
    }
    $scope.bankSure = function(){
        $scope.activeBank = $scope.banklist[$scope.activeBankParentIndex][$scope.activeBankIndex];
        $('.cen,.teding_fixed_box').hide();
    }
    // 用户选择
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserkParentIndex = parentIndex;
        $scope.activeUserId = did;
        $scope.activeUserIndex = index;
    }
    $scope.userSure = function(){
        $scope.activeUser = $scope.userlist[$scope.activeUserkParentIndex][$scope.activeUserIndex].name;
        console.log($scope.activeUser)
        console.log($scope.activeUserId)
        $('.cen,.teding_fixed_box').hide();
    }
    //修改用户
    $scope.formSubmit = function(){
        var data = {
            level1_price:$scope.lev1_p,
            level2_price:$scope.lev2_p,
            level3_price:$scope.lev3_p,
            bank_id:$scope.activeBankId,
            id:$stateParams.did,  
            agent_id:$scope.activeUserId,
            parent_agent_id:user.id
        }
        var lev0 = Number($scope.lev0_p);
        var lev1 = Number($scope.lev1_p);
        var lev2 = Number($scope.lev2_p);
        var lev3 = Number($scope.lev3_p);
        if(lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev0 || lev2 >= lev1 || lev2 >= lev0 || lev1 >= lev0){
            $scope.tip = '价格不能高于上级价格';
        }else{
            $scope.tip = '';
            $http.post( rout+'editAgentPrice',data).then(function(data){
                var resData = data.data;
                console.log(resData)
                if(resData.code == 1){
                    zdyAlert.init(resData.msg,function(){
                        $state.go("daili.quotedPrice",{nav:'xy'});
                    })
                }else{
                    zdyAlert.init(resData.msg,function(){
                        console.log(resData.msg)
                    })
                }
            },function(error){
                console.log(error)
            });
        }
    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
    })
    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//代理 - 报价单 - 新增 - 信用卡
indexapp.controller('teding_add',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    $scope.type = $stateParams.type;
    $scope.userlev = user.lev;
    $scope.bankActive = '请选择银行';
    $scope.userActive = '请选择用户';
    // 读取银行数据
    $http.post( rout+'getCreditList',{
        id:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.banklist = resData.data;
            var arr = $scope.banklist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.banklist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.banklist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    // 读取用户数据
    $http.post( rout+'myAgenList',{
        id:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.userlist = resData.data.data;
            var arr = $scope.userlist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.userlist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.userlist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });


    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.bankIndex = index
        $scope.activeBankId = did;
    }
    $scope.bankSubmit = function(){
        $scope.bankActive = $scope.banklist[$scope.activeBankParentIndex][$scope.bankIndex].bank_name;
        $('.cen,.teding_fixed_box').hide();
        $http.post( rout+'getAgentPrice',{
            agent_id:user.id,
            type:1,
            bank_id:$scope.activeBankId
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                if(resData.data.data !== ''){
                    $scope.level_price = resData.data.level_price;
                    $scope.level1_price = resData.data.level1_price;
                    $scope.level2_price = resData.data.level2_price;
                    $scope.level3_price = resData.data.level3_price;
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        });
    }
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserParentIndex = parentIndex;
        $scope.userIndex = index;
        $scope.activeUserId = did;
    }
    $scope.userSubmit = function(){
        $scope.userActive = $scope.userlist[$scope.activeUserParentIndex][$scope.userIndex].name;
        $('.cen,.teding_fixed_box').hide();
    }

    $scope.xyAdd = function(){
        var data = {
            bank_id:$scope.activeBankId,
            agent_id:$scope.activeUserId,
            parent_agent_id:user.id,
            level1_price:$scope.level1_price,
            level2_price:$scope.level2_price,
            level3_price:$scope.level3_price,
            type:1
        }
        var lev0 = Number($scope.level0_price);
        var lev1 = Number($scope.level1_price);
        var lev2 = Number($scope.level2_price);
        var lev3 = Number($scope.level3_price);

        if(!$scope.activeBankId){
            $scope.tip = '请选择银行';
        }else if(!$scope.activeUserId){
            $scope.tip = '请选择用户';
        }else if(lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev0 || lev2 >= lev1 || lev2 >= lev0 || lev1 >= lev0){
            $scope.tip = '价格不能高于上级价格';
        }else{
            $http.post( rout+'addAgentPrice',data).then(function(data){
                var resData = data.data;
                console.log(resData)
                if(resData.code == 1){
                    if(resData.data.data !== ''){
                        zdyAlert.init(resData.msg,function(){
                            window.history.back();
                        })
                    }
                }else{
                    zdyAlert.init(resData.msg,function(){
                        console.log(resData.msg)
                    })
                }
            },function(error){
                console.log(error)
            });
        }
    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
        $scope.activeBankId = -1;
        $scope.activeUserId = -1;
    })
    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//代理 - 报价单 - 编辑 - 贷款
indexapp.controller('teding_edit_dk',['$scope','$stateParams','$state','$http',function($scope,$stateParams,$state,$http){
    $scope.name = $stateParams.name;
    $scope.lev0_p = $stateParams.lv0_p;
    $scope.lev1_p = $stateParams.lv1_p;
    $scope.lev2_p = $stateParams.lv2_p;
    $scope.lev3_p = $stateParams.lv3_p;
    $scope.agentid = $stateParams.agentid;
    $scope.userlev = user.lev;
    $scope.activeUser = $stateParams.name;
    $scope.activeUserId = $stateParams.agentid;
    $scope.activeBank = {
        name : $stateParams.bank_name
    }
    $scope.activeBankId = $stateParams.bank_id;

    // 读取贷款数据
    $http.post( rout+'getLoanList',{
        id:$stateParams.did
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.banklist = resData.data;
            var arr = $scope.banklist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.banklist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.banklist = ar;

        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    // 读取用户数据
    $http.post( rout+'myAgenList',{
        id:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.userlist = resData.data.data;
            var arr = $scope.userlist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.userlist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.userlist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    // 银行选择
    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.activeBankId = did;
        $scope.activeBankIndex = index;
    }
    $scope.bankSure = function(){
        $scope.activeBank = $scope.banklist[$scope.activeBankParentIndex][$scope.activeBankIndex];
        $('.cen,.teding_fixed_box').hide();
    }

    // 用户选择
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserParentIndex = parentIndex;
        $scope.activeUserId = did;
        $scope.activeUserIndex = index;

    }
    $scope.userSure = function(){
        $scope.activeUser = $scope.userlist[$scope.activeUserParentIndex][$scope.activeUserIndex].name;
        $('.cen,.teding_fixed_box').hide();
    }

    //修改用户
    $scope.formSubmit = function(){
        var data = {
            level1_price:$scope.lev1_p,
            level2_price:$scope.lev2_p,
            level3_price:$scope.lev3_p,
            bank_id:$scope.activeBankId,
            id:$stateParams.did,  
            agent_id:$scope.activeUserId,
            parent_agent_id:user.id
        }
        var lev0 = Number($scope.lev0_p);
        var lev1 = Number($scope.lev1_p);
        var lev2 = Number($scope.lev2_p);
        var lev3 = Number($scope.lev3_p);

        if(lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev0 || lev2 >= lev1 || lev2 >= lev0 || lev1 >= lev0){
            $scope.tip = '价格不能高于上级价格';
        }else{
            $http.post( rout+'editAgentPrice',data).then(function(data){
                var resData = data.data;
                console.log(resData)
                if(resData.code == 1){
                    zdyAlert.init(resData.msg,function(){
                        $state.go("daili.quotedPrice",{nav:'dk'});
                    })
                }else{
                    zdyAlert.init(resData.msg,function(){
                        console.log(resData.msg)
                    })
                }
            },function(error){
                console.log(error)
            });
        }

    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
    })
    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//代理 - 报价单 - 新增 - 贷款
indexapp.controller('teding_add_dk',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    $scope.type = $stateParams.type;
    $scope.userlev = user.lev;
    $scope.bankActive = '请选择银行';
    $scope.userActive = '请选择用户';
    // 读取银行数据
    $http.post( rout+'getLoanList',{
        id:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.banklist = resData.data;
            var arr = $scope.banklist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.banklist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.banklist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    // 读取用户数据
    $http.post( rout+'myAgenList',{
        id:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            $scope.userlist = resData.data.data;
            var arr = $scope.userlist;
            var ar = [];
            var s = [];
            for(var b=0,i=1;b<(arr.length)/6;b++,i++){
               s = [];
               s = $scope.userlist.slice(b*6, i*6);
               ar[b] = s
            }
            $scope.userlist = ar;
        }else{
            zdyAlert.init(resData.msg,function(){
                console.log(resData.msg)
            })
        }
    },function(error){
        console.log(error)
    });

    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.bankIndex = index
        $scope.activeBankId = did;
    }
    $scope.bankSubmit = function(){
        $scope.bankActive = $scope.banklist[$scope.activeBankParentIndex][$scope.bankIndex].name;
        $('.cen,.teding_fixed_box').hide();
        $http.post( rout+'getAgentPrice',{
            agent_id:user.id,
            type:2,
            bank_id:$scope.activeBankId
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                if(resData.data.data !== ''){
                    $scope.level_price = resData.data.level_price;
                    $scope.level1_price = resData.data.level1_price;
                    $scope.level2_price = resData.data.level2_price;
                    $scope.level3_price = resData.data.level3_price;
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    console.log(resData.msg)
                })
            }
        },function(error){
            console.log(error)
        });
    }
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserParentIndex = parentIndex;
        $scope.userIndex = index;
        $scope.activeUserId = did;
    }
    $scope.userSubmit = function(){
        $scope.userActive = $scope.userlist[$scope.activeUserParentIndex][$scope.userIndex].name;
        $('.cen,.teding_fixed_box').hide();
    }

    $scope.xyAdd = function(){
        var data = {
            bank_id:$scope.activeBankId,
            agent_id:$scope.activeUserId,
            parent_agent_id:user.id,
            level1_price:$scope.level1_price,
            level2_price:$scope.level2_price,
            level3_price:$scope.level3_price,
            type:2
        }
        var lev0 = Number($scope.level0_price);
        var lev1 = Number($scope.level1_price);
        var lev2 = Number($scope.level2_price);
        var lev3 = Number($scope.level3_price);

        if(!$scope.activeBankId){
            $scope.tip = '请选择银行';
        }else if(!$scope.activeUserId){
            $scope.tip = '请选择用户';
        }else if(lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev0 || lev2 >= lev1 || lev2 >= lev0 || lev1 >= lev0){
            $scope.tip = '价格不能高于上级价格';
        }else{
            $http.post( rout+'addAgentPrice',data).then(function(data){
                var resData = data.data;
                console.log(resData)
                if(resData.code == 1){
                    if(resData.data.data !== ''){
                        zdyAlert.init(resData.msg,function(){
                            window.history.back();
                        })
                    }
                }else{
                    zdyAlert.init(resData.msg,function(){
                        console.log(resData.msg)
                    })
                }
            },function(error){
                console.log(error)
            });
        }

    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
        $scope.activeBankId = -1;
        $scope.activeUserId = -1;
    })
}])
//我的
indexapp.controller('personal',['$scope','$http',function($scope,$http){
    var userOtherInfo = store.get('userOtherInfo');
    // console.log(userOtherInfo);
    // console.log(user)
    $scope.userInfo = userOtherInfo;
    $scope.username = userOtherInfo.name;
    $scope.tip = '';
    $scope.personalSub = function(){
        if($scope.userInfo.name == ''){
            $scope.tip = '请输入姓名';
            return 
        }else if($scope.userInfo.phone == ''){
            $scope.tip = '请输入手机号';
            return
        }else if( !(/^1[34578]\d{9}$/.test($scope.userInfo.phone)) ){
            $scope.tip = '手机号有误';
            return
        }else{
            $scope.tip = '';
        }
        $http.post( rout+'editUserCommit',{
            id:user.id,
            name:$scope.userInfo.name,
            phone:$scope.userInfo.phone,
            email:$scope.userInfo.email,
            remark:$scope.userInfo.remark
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                $http.post( rout+'getAgentInfoById',{id:user.id}).then(function(data){
                    var resData = data.data;
                    console.log(resData)
                    if(resData.code == 1){
                        store.set('userOtherInfo',resData.data);
                        $scope.username = userOtherInfo.name;
                    }else{
                        zdyAlert.init(resData.msg,function(){
                            console.log(resData.msg)
                        })            
                    }
                },function(error){
                    console.log(error)
                }); 
                zdyAlert.init(resData.msg,function(){})
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            console.log(error)
        });  
    }
}])
indexapp.config(function($stateProvider, $urlRouterProvider){
    $urlRouterProvider.otherwise('/subIndex');
    $stateProvider
        .state('subIndex',{
            url: '/subIndex',
            templateUrl: 'subIndex.html',
            controller: 'subIndex'
        })
        .state('sharePage',{
            url: '/sharePage',
            templateUrl: 'sharePage.html',
            controller: 'sharePage'
        })      
        .state('withdraw',{
            url: '/withdraw',
            templateUrl: 'withdraw.html',
            controller: 'withdraw'
        })
        .state('report',{
            url: '/report',
            templateUrl: 'report.html',
            controller: 'report'
        })
        .state('myuser',{
            url: '/myuser',
            templateUrl: 'myuser.html',
            controller: 'myuser'
        })  
        .state('collectionAccount',{
            url: '/collectionAccount',
            templateUrl: 'collectionAccount.html',
            controller: 'collectionAccount'
        })
        .state('myuser.yonghu',{
            url: '/yonghu',
            templateUrl: 'user.html',
            controller: 'user'
        })  
        .state('myuser.access',{
            url: '/access',
            templateUrl: 'access.html',
            controller: 'access'
        })  
        .state('myuser.crdApplyRecord',{
            url: '/crdApplyRecord',
            templateUrl: 'crdApplyRecord.html',
            controller: 'crdApplyRecord'
        })  
        .state('myuser.loanApplyRecord',{
            url: '/loanApplyRecord',
            templateUrl: 'loanApplyRecord.html',
            controller: 'loanApplyRecord'
        })
        .state('daili',{
            url: '/daili',
            templateUrl: 'daili.html',
            controller: 'daili'
        })
        .state('daili.myAgent',{
            url: '/myAgent?page',
            templateUrl: 'myAgent.html',
            controller: 'myAgent'
        })
        .state('myAgent_edit',{
            url: '/myAgent_edit?did?page',
            templateUrl: 'myAgent_edit.html',
            controller: 'myAgent_edit'
        })
        .state('daili.agentAdd',{
            url: '/agentAdd',
            templateUrl: 'agentAdd.html',
            controller: 'agentAdd'
        })
        .state('daili.quotedPrice',{
            url: '/quotedPrice?nav',
            templateUrl: 'quotedPrice.html',
            controller: 'quotedPrice'
        })
        .state('teding_edit',{ //特定用户编辑 - 信用卡
            url: '/teding_edit?agentid&did&name&lv0_p&lv1_p&lv2_p&lv3_p&bank_name&bank_id',
            templateUrl: 'teding_edit.html',
            controller: 'teding_edit'            
        })
        .state('teding_edit_dk',{ //特定用户编辑 - 贷款
            url: '/teding_edit_dk?agentid&did&name&lv0_p&lv1_p&lv2_p&lv3_p&bank_name&bank_id',
            templateUrl: 'teding_edit_dk.html',
            controller: 'teding_edit_dk'            
        })
        .state('teding_add',{ //特定用户增加 - 信用卡
            url: '/teding_add?type',
            templateUrl: 'teding_add.html',
            controller: 'teding_add'            
        })
        .state('teding_add_dk',{ //特定用户增加 - 贷款
            url: '/teding_add_dk?type',
            templateUrl: 'teding_add_dk.html',
            controller: 'teding_add_dk'            
        })
        .state('personal',{
            url: '/personal',
            templateUrl: 'personal.html',
            controller: 'personal'
        })
})
// 编辑+增加+信用卡
indexapp.directive('xinyongka',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                $('.cen,.teding_fixed_box.xinyong').show();
            })
        }
    }
}])
// 编辑+增加+用户
indexapp.directive('user',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                $('.cen,.teding_fixed_box.yonghu').show();
            })
        }
    }
}])
// 更多
indexapp.directive('more',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                var $canpinListBox = $(element).parent().find('.canpin-list-box');
                if($canpinListBox.css('display') == 'block'){
                    $(element).find('.more').removeClass('dh');
                    $canpinListBox.stop().slideUp(500);
                }else{
                    $(element).find('.more').addClass('dh');
                    $canpinListBox.stop().slideDown(500);
                }
            })
        }
    }
}])
// 日期控件
indexapp.directive('riqi',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                if($('.dateBox').css('display') == 'none'){
                    $('.dateBox').stop().slideDown();
                    $('.jt').addClass('jtdh');
                }else{
                    $('.dateBox').stop().slideUp();
                    $('.jt').removeClass('jtdh');
                }
            })

        }
    }
}])
// 日期控件 - 开始
indexapp.directive('stime',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.text('请选择开始时间')
            var calendar = new datePicker();
            calendar.init({
                'trigger': '#stime', 
                'type': 'date',
                'minDate':'1900-1-1',
                'maxDate':'2100-12-31',
                'onSubmit':function(){
                    element.text(calendar.value)
                    scope.stime = calendar.value
                }
            });
        }
    }
}])
// 日期控件 - 结束
indexapp.directive('etime',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.text('请选择结束时间')
            var calendar = new datePicker();
            calendar.init({
                'trigger': '#etime', 
                'type': 'date',
                'minDate':'1900-1-1',
                'maxDate':'2100-12-31',
                'onSubmit':function(){
                    element.text(calendar.value)
                    scope.etime = calendar.value
                }
            });

        }
    }
}])

// 脚步切换高亮
indexapp.directive('footertap',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            var href=window.location.href;
            var index = href.indexOf("#/");
            var str = href.substring(index,href.length);
            var src,
                eqIndex;
            if(str.indexOf('subIndex') != -1 || str.indexOf('sharePage') != -1){
                src="/assets/agent/img/footer1_active.png";
                eqIndex = 0;
            }else if(str.indexOf('myuser') != -1){
                src="/assets/agent/img/footer2_active.png";
                eqIndex = 1;
            }else if(str.indexOf('daili') != -1 || str.indexOf('teding') != -1){
                src="/assets/agent/img/footer3_active.png";
                eqIndex = 2;
            }else if(str.indexOf('personal') != -1 || str.indexOf('withdraw') != -1 || str.indexOf('report') != -1 || str.indexOf('collectionAccount') != -1){
                src="/assets/agent/img/footer4_active.png";
                eqIndex = 3;
            }
            $('#footer a').eq(eqIndex).find('img').attr('src',src)
        }
    }
}])
indexapp.filter('lastcode', function() { //可以注入依赖
    return function(text) {
        return text.slice(text.length - 3)
    }
});
indexapp.filter('phonexin', function() { //可以注入依赖
    return function(text) {
        var first = text.slice(0,4);
        var last = text.slice(text.length - 3);
        return first +'*****'+ last
    }
});

indexapp.directive('paginations', function() {
    return {
      restrict : 'EA',
      templateUrl : 'pagination.html',
      replace: true,
      scope:{
        conf:'='
      },
      link:function(scope, ele , attrs){
        var conf = scope.conf;
        if(!scope.conf){
            scope.conf = {
                currentPage:0,
                count:0
            }
        }
        scope.$watch('conf',function(nVal,oVal){
            conf = nVal;
            scope.pageListFn();
        })

        scope.next = function(){
            if(conf.currentPage >= conf.count) return ;
            conf.currentPage += 1;
            scope.pageListFn();
            conf.onChange();
        }
        scope.prev = function(){
            if(conf.currentPage <= 1) return ;
            conf.currentPage -= 1;
            scope.pageListFn();
            conf.onChange();
        }
        scope.sPage = function(e){
            conf.currentPage = e;
            scope.pageListFn();
            conf.onChange();
        }

        scope.pageListFn = function(){
            conf.count = Number(conf.count)
            conf.currentPage = Number(conf.currentPage)
            conf.pageList = [];
            conf.selectArr = [];
            if(conf.count > 6){
                if(conf.currentPage < 5){
                    for(var i=1;i<=5;i++){
                        conf.pageList.push(i)
                    }
                    conf.pageList.push('...')
                }else if(conf.currentPage >= conf.count - 3){
                    for(var i=conf.count - 3;i<=conf.count;i++){
                        conf.pageList.push(i)
                    }
                    conf.pageList.unshift('...')
                }else{
                    for(var i= conf.currentPage -2 ; i<= conf.currentPage + 2 ; i++){
                        conf.pageList.push(i);
                    }
                    conf.pageList.push('...');
                    conf.pageList.unshift('...');
                }
            }else{
                for(var i=1;i<= conf.count;i++){
                    conf.pageList.push(i)
                }
            }
            for(var i=1;i<=conf.count;i++){
                conf.selectArr.push(i)
            }
        }
      }
    }
})

function jump(){
    zdyAlert.init('请登录',function(){
        window.location.href="login";
    })
}
var zdyAlert = {
    init:function(e,callback,iscen){
        var html = '<div class="alertcen"></div>';
        html += '<div class="alertnei"><p class="title">提示</p><div class="alert-con">'+ e +'</div><div class="alert-btn">确定</div></div>';
        $('body').append(html);
        $('.alert-btn').click(function(){
            $('.alertcen,.alertnei').remove();
            callback();
        })
    }
}
var zdycomfig = {
        init:function(e,callback){
        var html = '';
        html += '<div class="alertnei"><p class="title">提示</p><div class="alert-con">'+ e +'</div><div class="two-btn"><div class="queren">确定</div><div class="close">取消</div></div></div>';
        $('body').append(html);
        $('.close').click(function(){
            $('.alertcen,.alertnei').remove();
        })
        $('.queren').click(function(){
            callback();
        })
    }
}
