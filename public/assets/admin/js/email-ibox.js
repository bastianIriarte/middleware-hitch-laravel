
// for Category selection
const multipleCancelButton = new Choices(
    '#email',
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
let mailContainer = document.querySelectorAll(".user-list")

let i = true 

mailContainer.forEach((element)=>{
    element.onclick = ()=>{
        if(window.screen.width < 1399){
            document.querySelector(".mails-information").style.display = "block"
            document.querySelector(".total-mails2").classList.add('d-none')
            i = false
        }
    }
})


let closeButton = document.querySelectorAll(".responsive-mail-action-icons > button")[0]

if(closeButton){
    closeButton.onclick = ()=>{
        document.querySelector(".total-mails2").classList.remove('d-none')
        document.querySelector(".mails-information").style.display = "none"
        i = true
    }
}


window.addEventListener("resize",()=>{
    if(window.screen.width > 1399 ){
        document.querySelector(".mails-information").style.display = "block"
        document.querySelector(".total-mails2").classList.remove('d-none')
    }else{
        if(i){
            document.querySelector(".mails-information").style.display = "none"
        }
    }

    if(window.screen.width < 1399 && i == false){
        document.querySelector(".total-mails2").classList.add("d-none")
    }else{
        // if(document.querySelector(".mail-navigation").style.display != "block"){
            document.querySelector(".total-mails2").classList.remove("d-none")
        // }
    }

    if(window.screen.width > 991){
        document.querySelector(".mail-navigation").style.display = "block"
        document.querySelector(".total-mails2").style.display = "block"
    }
    else{
        if(document.querySelector(".total-mails2").style.display == 'block' && i == false){
        document.querySelector(".mail-navigation").style.display = "none"
        }
        if(document.querySelector(".mail-navigation").style.display = 'none'){
            // document.querySelector(".total-mails2").style.display = "none"
        }
    }

    if(window.screen.width < 991){
        document.querySelector(".total-mails2").style.display = 'block'
    }
})


document.querySelectorAll(".mail-type").forEach((element)=>{
    element.onclick = ()=>{
        if(window.screen.width <= 991){
            document.querySelector(".total-mails2").style.display = "block"
            document.querySelector(".total-mails2").classList.remove("d-none")
            document.querySelector(".mail-navigation").style.display = "none"
            i = true
        }
    }
})


document.querySelector(".total-mails-close").onclick = ()=>{
    if(window.screen.width < 992){
        document.querySelector(".mail-navigation").style.display = "block"
        document.querySelector(".total-mails2").classList.add("d-none")
        i = true
    }
}
