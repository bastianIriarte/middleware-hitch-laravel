function toggleStar(icon) {
    // Toggle the star icon from outline to filled, and vice versa
    const starIcon = icon.querySelector('.mdi');

    starIcon.classList.toggle('mdi-star-outline');
    starIcon.classList.toggle('mdi-star');

    starIcon.classList.toggle('filled-star');
}