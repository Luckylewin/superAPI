<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 18:50
 */
use Breeze\Route;

// 路由列表

Route::group(['middleware' => 'json'], function() {
    Route::post('/', 'App\Controller\OttController@index');

    // 需鉴权路由群组
    Route::group(['middleware' => 'auth'], function() {
        // 升级APP
        Route::post('/getNewApp', 'App\Controller\OttController@getNewApp');
        // 升级固件(dvb)
        Route::post('/getFirmware', 'App\Controller\OttController@getFirmware');
        // 取直播列表
        Route::post('/getOttNewList', 'App\Controller\OttController@getOttList');
    });

    // 鉴权
    Route::post('/getClientToken', 'App\Controller\OttController@getClientToken');

// 获取预告
    Route::post('/getEPG', 'App\Controller\OttController@getEPG');

// 获取主要分类
    Route::post('/getMainClass', 'App\Controller\OttController@getMainClass');

// 升级固件(android)
    Route::post('/getAndroidFirmware', 'App\Controller\OttController@getAndroidFirmware');

// APP市场
    Route::post('/getAppMarket', 'App\Controller\OttController@getAppMarket');

// APP下载
    Route::post('/getConcreteApp', 'App\Controller\OttController@getConcreteApp');

// 获取直播主要分类
    Route::post('/getCountryList', 'App\Controller\OttController@getMainClass');

// 获取正则表达式
    Route::post('/getRegex', 'App\Controller\OttController@getRegex');

// 轮播图片
    Route::post('/getOttBanners', 'App\Controller\OttController@getOttBanners');

// 推荐列表
    Route::post('/getOttRecommend', 'App\Controller\OttController@getOttRecommend');

// 未来n小时的主要赛事
    Route::post('/getRecentEvent', 'App\Controller\OttController@getRecentEvent');

// 卡拉ok列表
    Route::post('/getKaraokeList', 'App\Controller\OttController@getKaraokeList');

// 续费
    Route::post('/renew', 'App\Controller\OttController@renew');

// dvb注册
    Route::post('/register', 'App\Controller\OttController@register');

// 获取过期时间
    Route::post('/getExpireTime', 'App\Controller\OttController@getExpireTime');

// 获取用户信息
    Route::post('/getAccountInfo', 'App\Controller\OttController@getAccountInfo');

// 取服务器列表
    Route::post('/getServerList', 'App\Controller\OttController@getServerList');

// 取服务器时间
    Route::post('/getServerTime', 'App\Controller\OttController@getServerTime');

// 取预告列表
    Route::post('/getEPG', 'App\Controller\OttController@getEPG');

// 直播分类价目表
    Route::post('/getOttPriceList', 'App\Controller\OttController@getOttPriceList');

// 下单接口
    Route::post('/ottCharge', 'App\Controller\OttController@ottCharge');

// 支付接口
    Route::post('/pay', 'App\Controller\PayController@pay');

// 查询订单接口
    Route::post('/getOrderStatus', 'App\Controller\OttController@getOrderStatus');

// 开机图片
    Route::post('/getBootPic', 'App\Controller\OttController@getBootPic');

// 升级APP
    Route::post('/getApp', 'App\Controller\OttController@getNewApp');

// 获取主要赛事
    Route::post('/getMajorEvent','App\Controller\OttController@getMajorEvent' );

// 获取预告列表
    Route::post('/getParadeList','App\Controller\OttController@getParadeList' );

// 获取频道图标
    Route::post('/getChannelIcon','App\Controller\OttController@getChannelIcon' );

// 取分类详细信息
    Route::post('/getGenre', 'App\Controller\OttController@getGenre' );

// 获取分类使用状态
    Route::post('/getGenreUsageInfo', 'App\Controller\OttController@getGenreUsageInfo' );

// 获取分类
    Route::post('/getGenrePrice', 'App\Controller\OttController@getGenrePrice');

// 激活卡激活分类使用权限
    Route::post('/activateGenre', 'App\Controller\OttController@activateGenre');

// 获取隐藏内容隐藏状态
    Route::post('/getLockStatus', 'App\Controller\OttController@getLockStatus');

// 解锁隐藏内容
    Route::post('/relieveLock', 'App\Controller\OttController@relieveLock');

});


// 获取客户端ip
Route::get('/getip', 'App\Controller\UserController@getip');

// 点播
Route::get('/', 'App\Controller\IptvController@index');

// 支付同步回调通知
Route::get('paypalCallback', 'App\Controller\PayController@paypalCallback');

// dokypay 同步请求返回
Route::get('/return/dokypay','App\Controller\PayController@notifyByGet' );

// 异步
Route::post('/notify/dokypay','App\Controller\PayController@notifyByPost' );

// hello world
Route::get('/index','App\Controller\IndexController@index' );

