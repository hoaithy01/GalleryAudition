
$(document).ready(function($){
	"use strict";
	
	
	
	$(window).scroll(function() {    
    var scroll = $(window).scrollTop();

    if (scroll >= 630) {
        $(".header").addClass("navbar-fixed-top");
    } else {
        $(".header").removeClass("navbar-fixed-top");
    }
	});
	
	
	 $(".navbar").offset().top > 150 && $(".navbar-fixed-top").addClass("top-nav-collapse"), $(window).scroll(function() {
                $(".navbar").offset().top > 150 ? $(".navbar-fixed-top").addClass("top-nav-collapse") : $(".navbar-fixed-top").removeClass("top-nav-collapse")
            });
            var t = 0;
            t = $(".navbar-fixed-top").height()- 55, 
			$(".js_nav-item a").bind("click", function(o) {
                var a = $($(this).attr("href")).offset().top;
                $("html, body").stop().animate({
                    scrollTop: a - t
                }, 800), o.preventDefault()
            });
            $("body").scrollspy({
                target: ".navbar-fixed-top",
                offset: t + 2
            });
            $(window).scroll(function() {
                $(".navbar-collapse.in").collapse("hide")
            })
	
			
			
			
			
			
			
			
			
			
	
    $(document).on('click', '#back_to_top', function(e) {
        e.preventDefault();
        $('body,html').animate({
            scrollTop: 0
        }, $(window).scrollTop() / 8, 'linear')
    })
	

	
	
	


			//nicescroll
				$("html").niceScroll({
				 scrollspeed: 100,	
				mousescrollstep: 50,
				cursorminheight: 220,
				cursorcolor: "#6037b0",
				cursorwidth: "8px",
				cursorborderradius: "10px",
				cursorborder: "none",
				autohidemode: false,
				background:"rgba(0,0,0,0.5)",
				zindex: 10002
			});	
	
	//scroll to div
	$(function() {
  $('.reg-now-btn').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html,body').animate({
          scrollTop: target.offset().top
        }, 2000);
        return false;
      }
    }
  });
	});
	
	$(window).load(function() {
	 $('body').addClass('loaded');
	});
	
	
	

});

 

$(window).bind('resize', function (){

})

function validate() {
	var isSuccess = true;
	$(".ss-q-short").each(function() {
		if($(this).val() == "") {
			if (isSuccess) {
				$(this).focus();
				isSuccess = false;
			}
			// $(this).parent().find(".help-block").fadeIn("slow");
			$(this).parent().addClass("has-error").find(".help-block").html("This is a required question").fadeIn("slow");
		} else {
			// $(this).parent().find(".help-block").fadeOut("slow");
			$(this).parent().removeClass("has-error").find(".help-block").html("This is a required question").hide();
		}
	})
	var email = $("#entry_1470279020");
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!regex.test(email.val()) && email.val() != "") {
		isSuccess = false;
		$(this).focus();
		email.parent().addClass("has-error").find(".help-block").html("Must be a valid email address").fadeIn("slow");
	}
	return isSuccess;
}

function clearForm() {
	$('input[type=file]').val("");
	$('input[type=text]').val("");
	$("#browse_wrap").removeAttr("src");
}
$("#ss-submit").click(function() {
	var isSuccess = validate();
	if (isSuccess) {
		$('body').addClass('loading');
		/*$(".bs-example-modal-sm").modal("show");
		$(".modal-header .modal-title").html("Đăng ký");
		$(".modal-content .modal-body p").html("Xin đợi quá trình đăng ký được hoàn thành!");*/
		var form = document.forms.namedItem("ss-form");
		var data = new FormData(form);

	    $.ajax({
			type: 'POST',
			url: 'register.php',
			dataType: 'json',
			data: data,
			processData: false, // Don't process the files
	        contentType: false,
			success: function(res) {
				if (res.status == "ng") {
					$(".modal-content .modal-body p").html(res.msg);
					// $(".modal-header .modal-title").html("Lỗi");
				} else {
					clearForm();
					// $(".modal-header .modal-title").html("Thành Công");
					$(".modal-content .modal-body p").html("Đăng ký thành công!<br>Chúng tôi đã gửi mail tới bạn.<br> Xin hãy kiểm tra lại");
				}
			}, complete: function() {
				$('body').removeClass('loading');
				$(".modal-block").modal("show");
			}
		})
	}
	return false;
})

// var files;
// $('input[type=file]').on('change', function(e) {
// 	files = e.target.files;
// })

$(".ss-q-short").each(function() {
	$(this).keyup(function() {
		if($(this).val() != "")
			$(this).parent().removeClass("has-error").find(".help-block").html("This is a required question").hide();
	})
});

$("#browse_wrap").click(function() {
	$("#entry_1470279090").click();
});

$("#entry_1470279090").change(function() {
	readURL(this);
})

function readURL(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#browse_wrap').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}


