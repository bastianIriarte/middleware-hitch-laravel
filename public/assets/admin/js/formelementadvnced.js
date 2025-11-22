(function () {
    'use strict'

    /* dropzone */
    let myDropzone = new Dropzone(".dropzone");
        myDropzone.on("addedfile", file => {
    });

    /* multiple upload */
    const MultipleElement = document.querySelector('.multiple-filepond');
    FilePond.create(MultipleElement,);
    
    /* single upload */
    FilePond.create(
        document.querySelector('.single-fileupload'),
        {
            labelIdle: `Drag & Drop your picture or <span class="filepond--label-action">Browse</span>`,
            imagePreviewHeight: 170,
            imageCropAspectRatio: '1:1',
            imageResizeTargetWidth: 200,
            imageResizeTargetHeight: 200,
            stylePanelLayout: 'compact circle',
            styleLoadIndicatorPosition: 'center bottom',
            styleButtonRemoveItemPosition: 'center bottom'
        }
    );
	 
    /* To choose date */
    flatpickr("#date", {});
	
    /* For Date Range Picker */
    flatpickr("#daterange", {
        mode: "range",
        dateFormat: "Y-m-d",
    });
	
    /* For Time Picker */
    flatpickr("#timepikcr", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
    });

    /* For Time Picker With 24hr Format */
    flatpickr("#timepickr1", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
    });

    /* For Time Picker With Limits */
    flatpickr("#limittime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        minTime: "16:00",
        maxTime: "22:30",
    });

    /* For DateTimePicker with Limited Time Range */
    flatpickr("#limitdatetime", {
        enableTime: true,
        minTime: "16:00",
        maxTime: "22:00"
    });

	/* default multi select */
	const secondElement = new Choices('#choices-multiple-default', { allowSearch: false }).setValue(['Choice 2', 'Choice 3']);

	/* multi select with remove button */
	const multipleCancelButton = new Choices(
		'#choices-multiple-remove-button',
		{
		allowHTML: true,
		removeItemButton: true,
		}
	);
	/* multi select with option groups */
	const multipleDefault = new Choices(
	  document.getElementById('choices-multiple-groups'),
	  { allowHTML: true }
	);
  
	/* email address only */
	var textEmailFilter = new Choices('#choices-text-email-filter', {
	  allowHTML: true,
	  editItems: true,
	  addItemFilter: function (value) {
		if (!value) {
		  return false;
		}
		const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		const expression = new RegExp(regex.source, 'i');
		return expression.test(value);
	  },
	}).setValue(['abc@hotmail.com']);
  
	/* passing through values */
	var textPresetVal = new Choices('#choices-text-preset-values', {
	  allowHTML: true,
	  items: [
		'one',
		{
		  value: 'two',
		  label: 'two',
		  customProperties: {
			description: 'Numbers are infinite',
		  },
		},
	  ],
	});
  
	/* options added via config with no search */
	var singleNoSearch = new Choices('#choices-single-no-search', {
	  allowHTML: true,
	  searchEnabled: false,
	  removeItemButton: true,
	  choices: [
		{ value: 'One', label: 'Label One' },
		{ value: 'Two', label: 'Label Two' },
		{ value: 'Three', label: 'Label Three' },
	  ],
	}).setChoices(
	  [
		{ value: 'Four', label: 'Label Four' },
		{ value: 'Five', label: 'Label Five' },
		{ value: 'Six', label: 'Label Six', selected: true },
	  ],
	  'value',
	  'label',
	  false
	);
  
	/* passing unique values */
	var textUniqueVals = new Choices('#choices-text-unique-values', {
	  allowHTML: true,
	  paste: false,
	  duplicateItemsAllowed: false,
	  editItems: true,
	});
	
	 document.addEventListener("DOMContentLoaded", function () {
		var genericExamples = document.querySelectorAll("[data-trigger]");
		for (let i = 0; i < genericExamples.length; ++i) {
		  var element = genericExamples[i];
		  new Choices(element, {
			allowHTML: true,
			placeholderValue: "This is a placeholder set in the config",
			searchPlaceholderValue: "Search",
		  });
		}
	  });


})();