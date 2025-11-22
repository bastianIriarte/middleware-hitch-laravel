(function () {
    "use strict";

    var lightboxVideo = GLightbox({
        selector: '.glightbox'
    });
    lightboxVideo.on('slide_changed', ({ prev, current }) => {
        console.log('Prev slide', prev);
        console.log('Current slide', current);

        const { slideIndex, slideNode, slideConfig, player } = current;
    });
    
    let loadFile = function (event) {
        var reader = new FileReader();
        reader.onload = function () {
            var output = document.getElementById('profile-img');
            if (event.target.files[0].type.match('image.*')) {
                output.src = reader.result;
            } else {
                event.target.value = '';
                alert('please select a valid image');
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    };
    // for profile photo update
    let ProfileChange = document.querySelector('#profile-change');
    ProfileChange.addEventListener('change', loadFile);

})();