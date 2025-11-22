/* Choices JS */
/* multi select with remove button */
const multipleCancelButton = new Choices("#choices-multiple-remove-button", {
  allowHTML: true,
  removeItemButton: true,
});

/* multi select with remove button */
const multipleCancelButton2 = new Choices("#choices-multiple-remove-button2", {
  allowHTML: true,
  removeItemButton: true,
});

// for Category selection
const multipleCancelButton3 = new Choices("#country-select", {
  allowHTML: true,
  removeItemButton: true,
});

(function () {
  "use strict";

  let loadFile = function (event) {
    var reader = new FileReader();
    reader.onload = function () {
      var output = document.getElementById("profile-img");
      if (event.target.files[0].type.match("image.*")) {
        output.src = reader.result;
      } else {
        event.target.value = "";
        alert("please select a valid image");
      }
    };
    reader.readAsDataURL(event.target.files[0]);
  };
  // for profile photo update
  let ProfileChange = document.querySelector("#profile-change");
  ProfileChange.addEventListener("change", loadFile);
})();
