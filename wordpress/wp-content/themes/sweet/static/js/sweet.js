jQuery(document).ready(function($) {
$(function () {

    var right = $('.right');
    var bg = $('.bgDiv');
    var rightNav = $('.rightNav');

    showNav(right, rightNav, "right");


    function showNav(btn, navDiv, direction) {
        btn.on('click', function () {
            bg.css({
                display: "block",
                transition: "opacity .5s"
            });
            if (direction == "right") {
                navDiv.css({
                    right: "0px",
                    transition: "right 1s"
                });
            }


        });
    }

    bg.on('click', function () {
        hideNav();
    });

    function hideNav() {
        rightNav.css({
            right: "-50%",
            transition: "right .5s"
        });
        bg.css({
            display: "none",
            transition: "display 1s"
        });
    }
	
	//返回顶部
	function goTop(){
		$(window).scroll(function(e) {
			if($(window).scrollTop()>100)
				$(".gotop").fadeIn(350).css("display","block");
			else
				$(".gotop").fadeOut(350).css("display","none");
		});
			
		$(".gotop").click(function(e) {
			$('body,html').animate({scrollTop:0},500);
			return false;
		});		
	};
	goTop();
	
});
});