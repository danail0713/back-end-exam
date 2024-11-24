(function ($) {
  $(document).ready(function () {
    // Attach click event to all toggle buttons
    $(".toggle-btn").on("click", function () {
      var $this = $(this); // Cache the button element
      var contentId = $this.attr("aria-controls"); // Get the related content ID
      var $themeBody = $("#" + contentId); // Select the corresponding theme-body element

      // Toggle visibility of the theme-body
      if ($themeBody.is(":visible")) {
        $themeBody.slideUp(300); // Smoothly hide content
        $this.text("+"); // Change button text to "+"
        $this.attr("aria-expanded", "false"); // Update ARIA attribute
      } else {
        $themeBody.slideDown(300); // Smoothly show content
        $this.text("-"); // Change button text to "-"
        $this.attr("aria-expanded", "true"); // Update ARIA attribute
      }
    });
  });
})(jQuery);
