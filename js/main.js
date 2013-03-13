$(function(){
	$('.ball').click(function(){
		if ($(".text").is(":hidden")) {
	      $(".text").slideDown();
	    } else {
	      $(".text").slideUp();
	    }
	});
});