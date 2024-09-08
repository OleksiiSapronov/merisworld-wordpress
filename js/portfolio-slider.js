jQuery(document).ready(function ($) {
  let isDragging = false;
  let startX, startY;

  // Initialize Slick Slider
  $(".portfolio-slider").slick({
    infinite: false, // Prevent infinite looping to avoid malfunctioning
    draggable: false, // Disable dragging functionality
    slidesToShow: 4, // Display one slide at a time
    slidesToScroll: 1, // Scroll one slide at a time
    autoplay: false,
    dots: true, // Enable dots for easier navigation
    arrows: true, // Enable arrows
    nextArrow: '<button class="slick-next">Next</button>',
    prevArrow: '<button class="slick-prev">Prev</button>',
    adaptiveHeight: true, // Adjust the height based on content
    speed: 2000, // Set the transition speed between slides
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
        },
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
        },
      },
    ],
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
      $(this).find(".category-info").toggle();
      $(this).find(".portfolio-info").toggle();
    }
  });

  var categoryIndices = {};

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
  // Prevent default behavior on drag
  $(".portfolio-slide").on("click", function (e) {
    if (isDragging) {
      e.preventDefault();
      return false;
    } else {
      var $currentSlide = $(this);
      var categoryId = $currentSlide.data("category-id");
      var $categoryInfo = $currentSlide.find(".category-info");
      var $portfolioInfo = $(
        '.portfolio-info[data-category-id="' + categoryId + '"]'
      );

      // Hide category-info
      $categoryInfo.hide();

      // Show the portfolio-info section for the clicked slide
      $portfolioInfo.show();

      // Initialize the index if not already set
      if (!categoryIndices.hasOwnProperty(categoryId)) {
        categoryIndices[categoryId] = 0;
      }

      // Show the next portfolio item in rotation
      showNextPortfolioItem($portfolioInfo, categoryIndices[categoryId]);
    }
  });
});
