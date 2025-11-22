  // for product features
  var toolbarOptions = [
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [{ 'font': [] }],
    ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
    ['blockquote', 'code-block'],

    [{ 'header': 1 }, { 'header': 2 }],               // custom button values
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],

    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
    [{ 'align': [] }],
    ['clean']                                         // remove formatting button
];
var quill = new Quill('#product-features', {
    modules: {
        toolbar: toolbarOptions
    },
    theme: 'snow'
});

// for upload product images
const MultipleElement1 = document.querySelector('.product-Images');
FilePond.create(MultipleElement1,);


// for Category selection
const multipleCancelButton = new Choices(
    '#Category',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);
// for Brands selection
const multipleCancelButton1 = new Choices(
    '#Brands',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);
// for Sizes selection
const multipleCancelButton2 = new Choices(
    '#Sizes',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);
// for color selection
const multipleCancelButton3 = new Choices(
    '#Color',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);
// for status selection
const multipleCancelButton4 = new Choices(
    '#status',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);
// for Visibility selection
const multipleCancelButton5 = new Choices(
    '#Visibility',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);

    // for publish date picker
    flatpickr("#publish-date", {});

    // for publish time
    flatpickr("#publish-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
    });