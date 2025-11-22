  //checkboxes select all js//

  (function () {
    'use strict'

    let checkAll = document.querySelector('.check-all');
    checkAll.addEventListener('click', checkAllFn)

    function checkAllFn() {
        if (checkAll.checked) {
            document.querySelectorAll('.user-checkbox input').forEach(function (e) {
                e.closest('.user-list').classList.add('selected');
                e.checked = true;
            });
        }
        else {
            document.querySelectorAll('.user-checkbox input').forEach(function (e) {
                e.closest('.user-list').classList.remove('selected');
                e.checked = false;
            });
        }
    }

})();

//checkboxes select all js//