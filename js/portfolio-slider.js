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

  // Add a touchend event specifically for the <a> elements
  $(".portfolio-slide a").on("touchend click", function (e) {
    e.stopPropagation(); // Prevent the event from bubbling up to the .portfolio-slide
    // Allow the default action for the link (navigation, etc.)
  });

  // Detect end of drag and decide whether to trigger click event
  $(".portfolio-slide").on("mouseup touchend", function (e) {
    if (!isDragging) {
      // Ensure that the click/tap is not on an <a> element
      if ($(e.target).is("a")) {
        return; // Skip the toggle behavior if <a> was clicked
      }
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

    const aspectRatio = 2 / 3.1; // Adjust based on desired aspect ratio
    let slideWidth;
    if (viewportWidth > viewportHeight && viewportWidth <= 950) {
      slideWidth = viewportHeight * 0.6 * aspectRatio;
      document.getElementById("main").style.height =
        viewportHeight - 50 - 47 + "px !important";
      document.getElementsByClassName("post-content")[0].style.marginTop = 0;
    } else if (viewportWidth < viewportHeight && viewportWidth <= 950) {
      slideWidth = viewportHeight * 0.8 * aspectRatio;
      document.getElementById("main").style.height =
        viewportHeight - 50 - 47 + "px !important";
    } else {
      slideWidth = viewportHeight * 0.8 * aspectRatio;
      document.getElementById("main").style.height =
        viewportHeight - 100 - 47 + "px !important";
      document.getElementsByClassName("post-content")[0].style.marginTop = "3%";
    }

    let slidesToShow = Math.round(viewportWidth / slideWidth);
    if (slidesToShow < 1) slidesToShow = 1;
    if (slidesToShow > 5) slidesToShow = 5;

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

  // Function to generate a random number between min and max (inclusive)
  function getRandomRotation(min, max) {
    return Math.random() * (max - min) + min;
  }

  // Function to create 3 background divs for each target div and rotate them
  function resizeBackgroundDivsForAllTargets() {
    const slideDiv = document.querySelector(".portfolio-slide");
    const ratio = 2 / 3.1;

    const slideWidth = slideDiv.offsetWidth;
    const slideHeight = slideWidth / ratio;

    const slideDivs = document.querySelectorAll(".portfolio-slide");

    slideDivs.forEach((slide) => {
      slide.style.height = `${slideHeight}px`;
    });

    const targetDiv = document.querySelector(".target-div");
    const targetWidth = targetDiv.offsetWidth;
    const targetHeight = targetWidth / ratio;

    const backgroundDivs = document.querySelectorAll(".background-div");
    backgroundDivs.forEach((backgroundDiv) => {
      backgroundDiv.style.width = `${targetWidth}px`;
      backgroundDiv.style.height = `${targetHeight}px`;
    });
  }
  function initialBackgroundDivsForAllTargets() {
    const targetDivs = document.querySelectorAll(".target-div"); // Select all target divs

    targetDivs.forEach((targetDiv) => {
      for (let i = 0; i < 3; i++) {
        // Create a new div element
        const backgroundDiv = document.createElement("div");
        backgroundDiv.classList.add("background-div");

        // Random rotation between -5 and +5 degrees
        const randomRotation = (-1) ** i * getRandomRotation(i * 2, i * 2 + 1);
        backgroundDiv.style.transform = `rotate(${randomRotation}deg) translateY(-5%)`;

        // Append the new background div before the current target div
        targetDiv.parentNode.insertBefore(backgroundDiv, targetDiv);
      }
    });
  }

  initialBackgroundDivsForAllTargets();

  // Get all close buttons
  const closeButtons = document.querySelectorAll(".close");

  // Function to trigger resize event
  function triggerResizeEvent() {
    // Trigger the resize event
    window.dispatchEvent(new Event("resize"));
  }

  // Add event listener to each close button
  closeButtons.forEach((button) => {
    button.addEventListener("click", triggerResizeEvent);
  });

  function updateFontSizeBySlideWidth() {
    const div = document.getElementsByClassName("portfolio-slide")[0];
    const divWidth = div.offsetWidth;

    let fontSize;
    if (divWidth <= 200) {
      fontSize = "10px";
    } else if (divWidth <= 300) {
      fontSize = "12px";
    } else if (divWidth <= 400) {
      fontSize = "14px";
    } else if (divWidth <= 500) {
      fontSize = "16px";
    } else if (divWidth <= 600) {
      fontSize = "18px";
    }

    const cateDivs = document.querySelectorAll(".cate_desc_font");
    cateDivs.forEach((cateDiv) => (cateDiv.style.fontSize = fontSize));

    const titleDivs = document.querySelectorAll(".port_title");
    titleDivs.forEach((titleDiv) => (titleDiv.style.fontSize = fontSize));

    const desc1Divs = document.querySelectorAll(".port_description1");
    desc1Divs.forEach((desc1Div) => (desc1Div.style.fontSize = fontSize));

    const desc2Divs = document.querySelectorAll(".port_description2");
    desc2Divs.forEach((desc2Div) => (desc2Div.style.fontSize = fontSize));
  }

  // Update slide dimensions and reinitialize on window resize
  $(window).on("resize", function () {
    $(".portfolio-slider")[0].slick.refresh();
    updateSlideDimensions();
    updateFontSizeBySlideWidth();
    resizeBackgroundDivsForAllTargets();
  });
});
