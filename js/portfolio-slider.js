jQuery(document).ready(function ($) {
  let isDragging = false;
  let startX, startY;
  var categoryIndices = {};

  // Initialize Slick Slider
  $(".portfolio-slider").slick({
    infinite: false, // Prevent infinite looping to avoid malfunctioning
    draggable: false, // Disable dragging functionality
    slidesToScroll: 1, // Scroll one slide at a time
    autoplay: false,
    dots: true, // Enable dots for easier navigation
    arrows: true, // Enable arrows
    nextArrow: '<button class="slick-next">Next</button>',
    prevArrow: '<button class="slick-prev">Prev</button>',
    adaptiveHeight: true, // Adjust the height based on content
    speed: 500, // Set the transition speed between slides
  });

  // Detect the start of dragging
  $(".portfolio-slide").on("mousedown touchstart", function (e) {
    isDragging = false;
    startX = e.pageX || e.originalEvent.touches[0].pageX;
    startY = e.pageY || e.originalEvent.touches[0].pageY;
  });

  // Detect the movement
  $(".portfolio-slide").on("mousemove touchmove", function (e) {
    const x = e.pageX || e.originalEvent.touches[0].pageX;
    const y = e.pageY || e.originalEvent.touches[0].pageY;
    if (Math.abs(x - startX) > 5 || Math.abs(y - startY) > 5) {
      isDragging = true;
    }
  });

  // Detect end of drag and decide whether to trigger click event
  $(".portfolio-slide").on("mouseup touchend", function (e) {
    if (!isDragging) {
      // Toggle between category info and portfolio info
      $(this).find(".portfolio-info").toggle();
    }
  });

  // Function to rotate through portfolio items
  function showNextPortfolioItem($container, index) {
    var $items = $container.find(".portfolio-item");
    if ($items.length === 0) return;

    var currentIndex = index;
    var totalItems = $items.length;

    // Hide all items
    $items.hide();

    // Show the current item
    $items.eq(currentIndex).fadeIn(500);

    // Update the index for next click
    categoryIndices[$container.data("category-id")] =
      (currentIndex + 1) % totalItems;
  }

  // Handle click on the portfolio slide
  $(".portfolio-slide").on("click", function (e) {
    if (isDragging) {
      e.preventDefault();
      return false;
    } else {
      var $currentSlide = $(this);
      var categoryId = $currentSlide.data("category-id");
      var $portfolioInfo = $(
        '.portfolio-info[data-category-id="' + categoryId + '"]'
      );

      // Show the portfolio-info section for the clicked slide
      $portfolioInfo.show();

      // Initialize the index if not already set
      if (!categoryIndices.hasOwnProperty(categoryId)) {
        categoryIndices[categoryId] = 1;
      }

      // Show the next portfolio item in rotation
      showNextPortfolioItem($portfolioInfo, categoryIndices[categoryId]);
    }
  });

  // Function to dynamically update slides only for mobile landscape
  function updateSlideDimensions() {
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    console.log(viewportHeight);
    console.log(viewportWidth);

    const aspectRatio = 2 / 3.1; // Adjust based on desired aspect ratio
    let slideWidth;
    if (viewportWidth > viewportHeight && viewportWidth <= 1024)
      slideWidth = viewportHeight * 0.6 * aspectRatio;
    else slideWidth = viewportHeight * 0.8 * aspectRatio;

    // Number of slides that can fit on the screen in landscape mode
    let slidesToShow = Math.floor(viewportWidth / slideWidth);
    console.log(slidesToShow);

    if (slidesToShow < 1) slidesToShow = 1; // At least 1 slide should be shown
    if (slidesToShow > 5) slidesToShow = 5;
    // Update Slick Slider options dynamically
    $(".portfolio-slider").slick(
      "slickSetOption",
      {
        slidesToShow: slidesToShow,
        slidesToScroll: 1,
        adaptiveHeight: true,
      },
      true
    );
  }

  // Update slide dimensions and reinitialize on window resize
  $(window).on("resize", function () {
    updateSlideDimensions();
    $(".portfolio-slider")[0].slick.refresh();
  });

  // Run on load
  // updateSlideDimensions();
});
