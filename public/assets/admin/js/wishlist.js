let invoicebtn = document.querySelectorAll(".btn-delete");
invoicebtn.forEach((eleBtn) => {
    eleBtn.onclick = () => {
        let invoice = eleBtn.closest(".wishlist-product")
        invoice.remove();
    }
})