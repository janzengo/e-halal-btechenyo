$(document).ready(function () {
  let $backToTop = $(".back-to-top");

  // Show or hide the button based on scroll position
  $(window).scroll(function () {
      if ($(this).scrollTop() > 20) {
          $backToTop.addClass("active");
      } else {
          $backToTop.removeClass("active");
      }
  });

  // Scroll to the top when the button is clicked
  $backToTop.click(function (e) {
      e.preventDefault(); // Prevent default anchor behavior
      $("html, body").animate({ scrollTop: 0 }, 500);
  });
});
