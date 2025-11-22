 
// for Category selection
const multipleCancelButton = new Choices(
    '#Category',
    {
        allowHTML: true,
        removeItemButton: true,
    }
);
 
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

