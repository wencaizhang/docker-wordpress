
jQuery(document).ready(function ($) {
    "use strict";
    
    /* 搜索 */
    jQuery('.searchform.on-menu i').each(function () {
        var $currSearchIcon = jQuery(this);
        var $currInput = $currSearchIcon.siblings('input');
        $currSearchIcon.click(function () {
            var $currInput = jQuery(this).siblings('input');
            if (jQuery('body').hasClass('search-opened')) {
                $currInput.focusout();
            } else {
                $currInput.focus().select();
            }
        });
        $currInput.focus(function () {
            jQuery('body').addClass('search-opened');
        });
        $currInput.focusout(function () {
            jQuery('body').removeClass('search-opened');
        });
    });

    $('.feature-posts').each(function(){
        var $cFeatPost=$(this);
        var $cFeatPostItems=$cFeatPost.children('.post-item');
        var $auto=true;
        var $time=0;
        var $timeInt=1000;
        var $timeMax=3000;
        $cFeatPostItems.each(function(){
            var $cFeatPostItem=$(this);
            $cFeatPostItem.hover(function(){
                $cFeatPostItem.addClass('active').siblings('.post-item').removeClass('active');
                $auto=false;
            },function(){
                $time=0;
                $auto=true;
            });
        });
        if($cFeatPostItems.length>1){
            setInterval(function(){
               if($auto&&$time>$timeMax){
                   $time=0;
                   var $activeItem=$cFeatPost.children('.post-item.active');
                   var $nextItem=$activeItem.next('.post-item').hasClass('post-item')?$activeItem.next('.post-item'):$cFeatPostItems.eq(0);
                   $nextItem.addClass('active');
                   $activeItem.removeClass('active');
               }else{
                   $time+=$timeInt;
               }
            },$timeInt);
        }
    });


    /* 菜单 */
    $('.xintheme-menu ul.sf-menu').superfish({
        delay: 10,
        animation: {
            opacity: 'show',
            height: 'show'
        },
        speed: 'fast',
        autoArrows: false,
        dropShadows: false
    });

    /* 手机端菜单 */
    jQuery('.mobile-menu-icon').click(function () {
        if (jQuery(this).hasClass('active')) {
            jQuery(this).removeClass('active');
            jQuery('body').removeClass('show-mobile-menu');
        } else {
            jQuery(this).addClass('active');
            jQuery('body').addClass('show-mobile-menu');
        }
        return false;
    });
    jQuery('.xintheme-mobile-menu>i').click(function () {
        jQuery('.mobile-menu-icon.active').click();
    });
    /* 手机端 二级菜单 */
    jQuery('.xintheme-mobile-menu>nav ul.sub-menu').each(function () {
        var $subMenu = jQuery(this);
        var $parMenuLink = $subMenu.siblings('a');
        $parMenuLink.click(function (e) {
            e.preventDefault();
            var $parMenu = jQuery(this).closest('li');
            $parMenu.siblings('li.menu-open').removeClass('menu-open').children('.sub-menu').slideUp('fast');
            $parMenu.toggleClass('menu-open');
            if ($parMenu.hasClass('menu-open')) {
                $parMenu.children('.sub-menu').slideDown('fast');
            } else {
                $parMenu.children('.sub-menu').slideUp('fast');
            }
            return false;
        });
    });

    /* QQ */
    $('a.qq-share').click(function (e) {
        e.preventDefault();
        window.open('https://connect.qq.com/widget/shareqq/index.html?url=' + jQuery(this).attr('href') + '&title=' + $(this).attr('data-title') + '&pics=' + $(this).attr('data-image') + '&summary=' + $(this).attr('data-excerpt'), "qqWindow", "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0");
        return false;
    });

    /* 微信 */
    $('a.weixin-share').click(function (e) {
        e.preventDefault();
        window.open('https://bshare.optimix.asia/barCode?site=weixin&url=' + jQuery(this).attr('href'), "weixinWindow", "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0");
        return false;
    });

    /* 微博 */
    $('a.weibo-share').click(function (e) {
        e.preventDefault();
        window.open('https://service.weibo.com/share/share.php?url' + jQuery(this).attr('href') + '&type=button&language=zh_cn&title=' + '【' + $(this).attr('data-title') + '】' + $(this).attr('data-excerpt') + '&pic=' + $(this).attr('data-image') + '&searchPic=true', "weiboWindow", "height=640,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0");
        return false;
    });

    /* 向上滚动  显示导航栏 */
    var $scrollTopOld = jQuery(window).scrollTop();
    var $scrollUpMax = 100;
    var $scrollUp = 0;
    var $scrollDownMax = 50;
    var $scrollDown = 0;
    jQuery(window).scroll(function (e) {
        var $header = jQuery('header>.xintheme-menu-container');
        var $headerClone = $header.siblings('.header-clone');
        var $headerCloneOT = $headerClone.offset().top;
        var $scrollTop = jQuery(window).scrollTop();
        /* START - Header resize */
        /* Important - Is HeaderScrollUp Check First */
        if(jQuery('#wpadminbar').attr('id')==='wpadminbar'){$headerCloneOT-=jQuery('#wpadminbar').height();}
        var $diff = $scrollTopOld - $scrollTop;
        if ($diff > 0) {/* Scroll Up */
            $scrollUp += $diff;
            $scrollDown = 0;
        } else {/* Scroll Down */
            $scrollUp = 0;
            $scrollDown -= $diff;
        }
        $scrollTopOld = $scrollTop;
        if ($scrollUpMax <= $scrollUp && $scrollTop > 0 && $headerCloneOT < $scrollTop && !jQuery('body').hasClass('header-small')) {
            jQuery('body').addClass('header-small');
            $header.css('margin-top', ('-' + $header.height() + 'px'));
            $header.stop().animate({marginTop: 0}, 200, 'linear', function () {
                $header.css({'margin-top': ''});
            });
        } else if (($scrollDownMax <= $scrollDown || $scrollTop === 0 || $headerCloneOT>$scrollTop) && jQuery('body').hasClass('header-small') && !$header.hasClass('hidding')) {
            if ($scrollTop === 0 || $headerCloneOT>$scrollTop) {
                jQuery('body').removeClass('header-small').removeClass('hidding');
            } else {
                $header.stop().addClass('hidding').animate({marginTop: ('-' + $header.height() + 'px')}, 200, 'linear', function () {
                    jQuery('body').removeClass('header-small');
                    $header.css({'margin-top': ''}).removeClass('hidding');
                });
            }
        }
    });

});
