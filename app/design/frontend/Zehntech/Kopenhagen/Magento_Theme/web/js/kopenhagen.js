require(["jquery"], function($) {

$(document).ready(function(){
  if ($(window).scrollTop() == 0)
    {
      $(".page-scroller").hide();
    }
  $(window).on("scroll", function()
   {
    if ($(window).scrollTop() > 100)
     {
        $(".page-scroller").fadeIn();
      } 
      else 
      {
        $(".page-scroller").fadeOut();
      }
    });

  $(".page-scroller").on("click", function() {
    $("html,body").animate({ scrollTop: 0 }, 700);
    return false;
  });
});
  
  $(document).ready(function(){
    // console.log("loaded");
  
   $(".menu-dropdown-icon").children().addClass("dropdown-toggle");
   $(".level-top ").siblings().removeClass("dropdown-toggle");
   $(".level1").children().removeClass("dropdown-toggle");
   $(".submenu > li").children().removeClass("dropdown-toggle");
 });

  $(window).on("scroll", function () {
    navbar = $(".container-fluid.navtop")
    if ($(window).scrollTop() > 0) {
        navbar.addClass("sticky",50);
    } 
    else {
        navbar.removeClass("sticky",50);
    }
  });

$(document).ready(function(){
     //$('.menu-dropdown-icon').find('ul.submenu').appendTo('ul');
    $('.menu-dropdown-icon').find('.submenu').insertBefore('level1');
});

  var li = $('.column_mega_menu1');
  li.on("mouseover", function(){
      $('.category-list').addClass('megamenu-container');
      $(this).find('a').first().addClass('megamenu-link');
      height = 0;
      if($(this).has('ul').length==0)
      {
        $('.category-list').removeClass('megamenu-container');
        $(this).find('a').first().removeClass('megamenu-link');
      }
    var myindex = li.index(this);
      $( ".category-list > li").each(function(index,element) {
          if(index==myindex)
              return false;
          height = height + $(element).height();
      }); 
      elementTop = -Math.abs(height);
      $('.level1.submenu').css({"top":elementTop});
      height = 0;
  })

  li.on("mouseleave", function(){
      $(this).find('a').first().removeClass('megamenu-link');
      $('.category-list').removeClass('megamenu-container');
  });

  $(document).ready(function(){
      $('.level1.parent li:has( > ul)').find('a').first().addClass('font-weight-bold');
  });

              $(".button").on("click", function() {

              var $button = $(this);
              var oldValue = $button.parent().find("input").val();
              var interval = $button.parent().find("input").attr('step'); 
              if ($button.text() == "+") {
                  var newVal = parseFloat(oldValue) + parseFloat(interval);
                } else {
               // Don't allow decrementing below zero
                if (oldValue > interval) {
                  var newVal = parseFloat(oldValue) - parseFloat(interval);
                } else {
                  newVal = interval;
                }
              }

              $button.parent().find("input").val(newVal);

            });
       

});