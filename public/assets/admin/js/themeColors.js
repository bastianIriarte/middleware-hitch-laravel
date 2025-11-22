const handleThemeUpdate = (cssVars) => {
    const root = document.querySelector(':root');
    const keys = Object.keys(cssVars);
    keys.forEach(key => {
        root.style.setProperty(key, cssVars[key]);
    });
}
const hex2rgb = (hex) => {
    const r = parseInt(hex.slice(1, 3), 16)
    const g = parseInt(hex.slice(3, 5), 16)
    const b = parseInt(hex.slice(5, 7), 16)
    return [r, g, b]
}
function dynamicPrimaryColor(primaryColor) {
    'use strict'

    primaryColor.forEach((item) => {
        item.addEventListener('input', (e) => {
            document.querySelector('html').style.setProperty('--primary-rgb', hex2rgb(e.target.value));
        });
    });
}
function darkdynamicPrimaryColor(darkprimaryColor) {
    'use strict'

    darkprimaryColor.forEach((item) => {
        item.addEventListener('input', (e) => {
            document.querySelector('html').style.setProperty('--primary-rgb', hex2rgb(e.target.value));
        });
    });
}

function imageBgdynamicPrimaryColor(imageprimaryColor) {
    'use strict'

    imageprimaryColor.forEach((item) => {
        item.addEventListener('input', (e) => {
            document.querySelector('html').style.setProperty('--transparent-bg-rgb', hex2rgb(e.target.value));
        });
    });
}
function transparentdynamicPrimaryColor(primaryColor) {
    'use strict'

    primaryColor.forEach((item) => {
        item.addEventListener('input', (e) => {

            document.querySelector('html').style.setProperty('--primary-rgb', hex2rgb(e.target.value));
        });
    });
}

(function () {
    'use strict'

    // Light theme color picker
    const dynamicPrimaryLight = document.querySelectorAll('input.color-primary-light');

    dynamicPrimaryColor(dynamicPrimaryLight);

    // dark theme color picker
    const DarkDynamicPrimaryLight = document.querySelectorAll('input.color-primary-dark');

    darkdynamicPrimaryColor(DarkDynamicPrimaryLight);

    // tranparent theme color picker
    const transparentDynamicPrimaryLight = document.querySelectorAll('input.color-primary-transparent');

    transparentdynamicPrimaryColor(transparentDynamicPrimaryLight);

    // tranparent theme bgcolor picker
    const transparentDynamicPBgLight = document.querySelectorAll('input.color-bg-transparent');

    imageBgdynamicPrimaryColor(transparentDynamicPBgLight);

    localStorageBackup();

    $('#myonoffswitch1').on('click', function () {
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('transparent-mode');
        document.querySelector('body')?.classList.remove('bg-img1');
        document.querySelector('body')?.classList.remove('bg-img2');
        document.querySelector('body')?.classList.remove('bg-img3');
        document.querySelector('body')?.classList.remove('bg-img4');

        localStorage.removeItem('vexelBgImage');
        $('#myonoffswitch1').prop('checked', true);

        localStorage.setItem('vexellightMode', true);
        localStorage.removeItem('vexeldarkMode');
        localStorage.removeItem('vexeltransparentMode');
    })
    $('#myonoffswitch2').on('click', function () {
        document.querySelector('body')?.classList.remove('light-mode');
        document.querySelector('body')?.classList.remove('transparent-mode');
        document.querySelector('body')?.classList.remove('bg-img1');
        document.querySelector('body')?.classList.remove('bg-img2');
        document.querySelector('body')?.classList.remove('bg-img3');
        document.querySelector('body')?.classList.remove('bg-img4');

        localStorage.setItem('vexeldarkMode', true);
        localStorage.removeItem('vexellightMode');
        localStorage.removeItem('vexeltransparentMode');

        localStorage.removeItem('vexelBgImage');
        $('#myonoffswitch2').prop('checked', true);
    })
    $('#myonoffswitchTransparent').on('click', function () {
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('light-mode');
        document.querySelector('body')?.classList.remove('bg-img1');
        document.querySelector('body')?.classList.remove('bg-img2');
        document.querySelector('body')?.classList.remove('bg-img3');
        document.querySelector('body')?.classList.remove('bg-img4');

        localStorage.removeItem('vexelBgImage');
        $('#myonoffswitchTransparent').prop('checked', true);

        localStorage.setItem('vexeltransparentMode', true);
        localStorage.removeItem('vexellightMode');
        localStorage.removeItem('vexeldarkMode');
    })

})();

// triggers on changing the color picker
function changePrimaryColor() {
    'use strict'

    $('#myonoffswitch3').prop('checked', true);
    $('#myonoffswitch6').prop('checked', true);
    checkOptions();

    var userColor = document.getElementById('colorID').value;
    localStorage.setItem('vexelprimaryColor', hex2rgb(userColor));

    // removing dark theme properties
    localStorage.removeItem('vexeldarkPrimary')
    localStorage.removeItem('vexeltransparentBgColor');
    localStorage.removeItem('vexeltransparentThemeColor');
    localStorage.removeItem('vexeltransparentPrimary');
    localStorage.removeItem('vexeltransparentBgImgPrimary');
    localStorage.removeItem('vexeltransparentBgImgprimaryTransparent');
    localStorage.removeItem('vexeldarkprimaryTransparent');
    document.querySelector('body').classList.add('light-mode');
    document.querySelector('body').classList.remove('transparent-mode');
    document.querySelector('body').classList.remove('dark-mode');
    localStorage.removeItem('vexelBgImage');

    $('#myonoffswitch1').prop('checked', true);
    names()

    localStorage.setItem('vexellightMode', true);
    localStorage.removeItem('vexeldarkMode');
    localStorage.removeItem('vexeltransparentMode');
}

function darkPrimaryColor() {
    'use strict'
    var userColor = document.getElementById('darkPrimaryColorID').value;
    localStorage.setItem('vexeldarkPrimary', hex2rgb(userColor));

    $('#myonoffswitch5').prop('checked', true);
    $('#myonoffswitch8').prop('checked', true);
    checkOptions();

    // removing light theme data
    localStorage.removeItem('vexelprimaryColor')
    localStorage.removeItem('vexelprimaryHoverColor')
    localStorage.removeItem('vexelprimaryBorderColor')
    localStorage.removeItem('vexelprimaryTransparent');
    localStorage.removeItem('vexeltransparentBgImgPrimary');
    localStorage.removeItem('vexeltransparentBgImgprimaryTransparent');

    localStorage.removeItem('vexeltransparentBgColor');
    localStorage.removeItem('vexeltransparentThemeColor');
    localStorage.removeItem('vexeltransparentPrimary');
    localStorage.removeItem('vexelBgImage');

    document.querySelector('body').classList.add('dark-mode');
    document.querySelector('body').classList.remove('light-mode');
    document.querySelector('body').classList.remove('transparent-mode');

    $('#myonoffswitch2').prop('checked', true);
    names()

    localStorage.setItem('vexeldarkMode', true);
    localStorage.removeItem('vexellightMode');
    localStorage.removeItem('vexeltransparentMode');
}

function transparentPrimaryColor() {
    'use strict'

    $('#myonoffswitch3').prop('checked', false);
    $('#myonoffswitch6').prop('checked', false);
    $('#myonoffswitch5').prop('checked', false);
    $('#myonoffswitch8').prop('checked', false);

    var userColor = document.getElementById('transparentPrimaryColorID').value;
    localStorage.setItem('vexeltransparentPrimary', hex2rgb(userColor));

    // removing light theme data
    localStorage.removeItem('vexeldarkPrimary');
    localStorage.removeItem('vexelprimaryColor')
    localStorage.removeItem('vexelprimaryHoverColor')
    localStorage.removeItem('vexelprimaryBorderColor')
    localStorage.removeItem('vexelprimaryTransparent');
    localStorage.removeItem('vexeltransparentBgImgPrimary');
    localStorage.removeItem('vexeltransparentBgImgprimaryTransparent');
    document.querySelector('body').classList.add('transparent-mode');
    document.querySelector('body').classList.remove('light-mode');
    document.querySelector('body').classList.remove('dark-mode');
    document.querySelector('body')?.classList.remove('bg-img1');
    document.querySelector('body')?.classList.remove('bg-img2');
    document.querySelector('body')?.classList.remove('bg-img3');
    document.querySelector('body')?.classList.remove('bg-img4');

    $('#myonoffswitchTransparent').prop('checked', true);
    checkOptions();
    names()

    localStorage.setItem('vexeltransparentMode', true);
    localStorage.removeItem('vexellightMode');
    localStorage.removeItem('vexeldarkMode');
}

function transparentBgImgPrimaryColor() {
    'use strict'

    $('#myonoffswitch3').prop('checked', false);
    $('#myonoffswitch6').prop('checked', false);
    $('#myonoffswitch5').prop('checked', false);
    $('#myonoffswitch8').prop('checked', false);

    var userColor = document.getElementById('transparentBgImgPrimaryColorID').value;
    localStorage.setItem('vexeltransparentBgImgPrimary', hex2rgb(userColor));
    if (
        document.querySelector('body')?.classList.contains('bg-img1') == false &&
        document.querySelector('body')?.classList.contains('bg-img2') == false &&
        document.querySelector('body')?.classList.contains('bg-img3') == false &&
        document.querySelector('body')?.classList.contains('bg-img4') == false
    ) {
        document.querySelector('body')?.classList.add('bg-img1');
        localStorage.setItem('vexelBgImage', 'bg-img1')
    }
    // removing light theme data
    localStorage.removeItem('vexeldarkPrimary');
    localStorage.removeItem('vexelprimaryColor')
    localStorage.removeItem('vexelprimaryHoverColor')
    localStorage.removeItem('vexelprimaryBorderColor')
    localStorage.removeItem('vexelprimaryTransparent');
    localStorage.removeItem('vexeldarkprimaryTransparent');
    localStorage.removeItem('vexeltransparentPrimary')
    localStorage.removeItem('vexeltransparentprimaryTransparent');
    document.querySelector('body').classList.add('transparent-mode');
    document.querySelector('body')?.classList.remove('light-mode');
    document.querySelector('body')?.classList.remove('dark-mode');

    $('#myonoffswitchTransparent').prop('checked', true);
    checkOptions();
    names();

    localStorage.setItem('vexeltransparentMode', true);
    localStorage.removeItem('vexellightMode');
    localStorage.removeItem('vexeldarkMode');
}


function transparentBgColor() {
    'use strict'

    $('#myonoffswitch3').prop('checked', false);
    $('#myonoffswitch6').prop('checked', false);
    $('#myonoffswitch5').prop('checked', false);
    $('#myonoffswitch8').prop('checked', false);

    var userColor = document.getElementById('transparentBgColorID').value;
    localStorage.setItem('vexeltransparentBgColor', hex2rgb(userColor));
    localStorage.removeItem('vexeltransparentBgImgPrimary');
    localStorage.removeItem('vexeltransparentBgImgprimaryTransparent');

    // removing light theme data
    localStorage.removeItem('vexeldarkPrimary');
    localStorage.removeItem('vexelprimaryColor')
    localStorage.removeItem('vexelprimaryHoverColor')
    localStorage.removeItem('vexelprimaryBorderColor')
    localStorage.removeItem('vexelprimaryTransparent');
    localStorage.removeItem('vexelBgImage');
    document.querySelector('body').classList.add('transparent-mode');
    document.querySelector('body').classList.remove('light-mode');
    document.querySelector('body').classList.remove('dark-mode');
    document.querySelector('body')?.classList.remove('bg-img1');
    document.querySelector('body')?.classList.remove('bg-img2');
    document.querySelector('body')?.classList.remove('bg-img3');
    document.querySelector('body')?.classList.remove('bg-img4');

    $('#myonoffswitchTransparent').prop('checked', true);
    checkOptions();

    localStorage.setItem('vexeltransparentMode', true);
    localStorage.removeItem('vexellightMode');
    localStorage.removeItem('vexeldarkMode');
}


function bgImage(e) {
    'use strict'

    $('#myonoffswitch3').prop('checked', false);
    $('#myonoffswitch6').prop('checked', false);
    $('#myonoffswitch5').prop('checked', false);
    $('#myonoffswitch8').prop('checked', false);
    let imgID = e.getAttribute('class');
    localStorage.setItem('vexelBgImage', imgID);

    // removing light theme data
    localStorage.removeItem('vexeldarkPrimary');
    localStorage.removeItem('vexelprimaryColor')
    localStorage.removeItem('vexeltransparentBgColor');
    localStorage.removeItem('vexeltransparentThemeColor');
    localStorage.removeItem('vexeltransparentprimaryTransparent');
    document.querySelector('body').classList.add('transparent-mode');
    document.querySelector('body')?.classList.remove('light-mode');
    document.querySelector('body')?.classList.remove('dark-mode');

    $('#myonoffswitchTransparent').prop('checked', true);
    checkOptions();

    localStorage.setItem('vexeltransparentMode', true);
    localStorage.removeItem('vexellightMode');
    localStorage.removeItem('vexeldarkMode');
}

function localStorageBackup() {
    'use strict'

    // if there is a value stored, update color picker and background color
    // Used to retrive the data from local storage
    if (localStorage.vexelprimaryColor) {
        if (document.getElementById('colorID')) {
            document.getElementById('colorID').value = localStorage.vexelprimaryColor;
        }
        document.querySelector('html').style.setProperty('--primary-rgb', localStorage.vexelprimaryColor);
        document.querySelector('body')?.classList.add('light-mode');
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('transparent-mode');

        $('#myonoffswitch3').prop('checked', true);
        $('#myonoffswitch6').prop('checked', true);
        $('#myonoffswitch1').prop('checked', true);
    }

    if (localStorage.vexeldarkPrimary) {
        if (document.getElementById('darkPrimaryColorID')) {
            document.getElementById('darkPrimaryColorID').value = localStorage.vexeldarkPrimary;
        }
        document.querySelector('html').style.setProperty('--primary-rgb', localStorage.vexeldarkPrimary);
        document.querySelector('body')?.classList.add('dark-mode');
        document.querySelector('body')?.classList.remove('light-mode');
        document.querySelector('body')?.classList.remove('transparent-mode');

        $('#myonoffswitch2').prop('checked', true);

    }


    if (localStorage.vexeltransparentPrimary) {
        if (document.getElementById('transparentPrimaryColorID')) {
            document.getElementById('transparentPrimaryColorID').value = localStorage.vexeltransparentPrimary;
        }
        document.querySelector('html').style.setProperty('--primary-rgb', localStorage.vexeltransparentPrimary);
        document.querySelector('body')?.classList.add('transparent-mode');
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('light-mode');

        $('#myonoffswitchTransparent').prop('checked', true);
    }

    if (localStorage.vexeltransparentBgImgPrimary) {
        if (document.getElementById('transparentBgImgPrimaryColorID')) {
            document.getElementById('transparentBgImgPrimaryColorID').value = localStorage.vexeltransparentBgImgPrimary;
        }
        document.querySelector('html').style.setProperty('--primary-rgb', localStorage.vexeltransparentBgImgPrimary);
        document.querySelector('body')?.classList.add('transparent-mode');
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('light-mode');

        $('#myonoffswitchTransparent').prop('checked', true);
    }

    if (localStorage.vexeltransparentBgColor) {
        if (document.getElementById('transparentBgColorID')) {
            document.getElementById('transparentBgColorID').value = localStorage.vexeltransparentBgColor;
        }
        document.querySelector('html').style.setProperty('--transparent-bg-rgb', localStorage.vexeltransparentBgColor);
        document.querySelector('body').classList.add('transparent-mode');
        document.querySelector('body').classList.remove('dark-mode');
        document.querySelector('body').classList.remove('light-mode');


        $('#myonoffswitchTransparent').prop('checked', true);
    }
    if (localStorage.vexelBgImage) {
        document.querySelector('body')?.classList.add('transparent-mode');
        let bgImg = localStorage.vexelBgImage.split(' ')[0];
        document.querySelector('body')?.classList.add(bgImg);
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('light-mode');

        $('#myonoffswitchTransparent').prop('checked', true);
    }
    if (localStorage.vexellightMode) {
        document.querySelector('body')?.classList.add('light-mode');
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.remove('transparent-mode');
        $('#myonoffswitch1').prop('checked', true);
        $('#myonoffswitch3').prop('checked', true);
        $('#myonoffswitch6').prop('checked', true);
    }
    if (localStorage.vexeldarkMode) {
        document.querySelector('body')?.classList.remove('light-mode');
        document.querySelector('body')?.classList.add('dark-mode');
        document.querySelector('body')?.classList.remove('transparent-mode');
        $('#myonoffswitch2').prop('checked', true);
        $('#myonoffswitch5').prop('checked', true);
        $('#myonoffswitch8').prop('checked', true);
    }
    if (localStorage.vexeltransparentMode) {
        document.querySelector('body')?.classList.remove('light-mode');
        document.querySelector('body')?.classList.remove('dark-mode');
        document.querySelector('body')?.classList.add('transparent-mode');
        $('#myonoffswitchTransparent').prop('checked', true);
        $('#myonoffswitch3').prop('checked', false);
        $('#myonoffswitch6').prop('checked', false);
        $('#myonoffswitch5').prop('checked', false);
        $('#myonoffswitch8').prop('checked', false);
    }
    if (localStorage.vexelhorizontal) {
        document.querySelector('body').classList.add('horizontal')
    }
    if (localStorage.vexelhorizontalHover) {
        document.querySelector('body').classList.add('horizontal-hover')
    }
    if (localStorage.vexelrtl) {
        document.querySelector('body').classList.add('rtl')
    }
    if (localStorage.vexelclosedmenu) {
        document.querySelector('body').classList.add('closed-leftmenu')
    }
    if (localStorage.vexelicontextmenu) {
        document.querySelector('body').classList.add('icontext-menu')
    }
    if (localStorage.vexelsideiconmenu) {
        document.querySelector('body').classList.add('icon-overlay')
    }
    if (localStorage.vexelhoversubmenu) {
        document.querySelector('body').classList.add('hover-submenu')
    }
    if (localStorage.vexelhoversubmenu1) {
        document.querySelector('body').classList.add('hover-submenu1')
    }
    if (localStorage.vexeldoublemenu) {
        document.querySelector('body').classList.add('double-menu')
    }
    if (localStorage.vexeldoublemenutabs) {
        document.querySelector('body').classList.add('double-menu-tabs')
    }
    if (localStorage.vexelscrollable) {
        document.querySelector('body').classList.add('scrollable-layout')
    }
    if (localStorage.vexelboxedwidth) {
        document.querySelector('body').classList.add('layout-boxed')
    }
    if (localStorage.vexellightheader) {
        document.querySelector('body').classList.add('header-light')
    }
    if (localStorage.vexelcolorheader) {
        document.querySelector('body').classList.add('color-header')
    }
    if (localStorage.vexeldarkheader) {
        document.querySelector('body').classList.add('dark-header')
    }
    if (localStorage.vexelgradientheader) {
        document.querySelector('body').classList.add('gradient-header')
    }

    if (localStorage.vexellightmenu) {
        document.querySelector('body').classList.add('light-menu')
    }
    if (localStorage.vexelcolormenu) {
        document.querySelector('body').classList.add('color-menu')
    }
    if (localStorage.vexeldarkmenu) {
        document.querySelector('body').classList.add('dark-menu')
    }
    if (localStorage.vexelgradientmenu) {
        document.querySelector('body').classList.add('gradient-menu')
    }
    if (localStorage.vexeldefaultlogo) {
        document.querySelector('body').classList.add('default-logo')
    }
    if (localStorage.vexelcenterlogo) {
        document.querySelector('body').classList.add('center-logo')
    }


}

// to check the value is hexa or not
const isValidHex = (hexValue) => /^#([A-Fa-f0-9]{3,4}){1,2}$/.test(hexValue)

const getChunksFromString = (st, chunkSize) => st.match(new RegExp(`.{${chunkSize}}`, "g"))
// convert hex value to 256
const convertHexUnitTo256 = (hexStr) => parseInt(hexStr.repeat(2 / hexStr.length), 16)
// get alpha value is equla to 1 if there was no value is asigned to alpha in function
const getAlphafloat = (a, alpha) => {
    if (typeof a !== "undefined") { return a / 255 }
    if ((typeof alpha != "number") || alpha < 0 || alpha > 1) {
        return 1
    }
    return alpha
}
// convertion of hex code to rgba code
function hexToRgba(hexValue, alpha) {
    'use strict'

    if (!isValidHex(hexValue)) { return null }
    const chunkSize = Math.floor((hexValue.length - 1) / 3)
    const hexArr = getChunksFromString(hexValue.slice(1), chunkSize)
    const [r, g, b, a] = hexArr.map(convertHexUnitTo256)
    return `rgba(${r}, ${g}, ${b}, ${getAlphafloat(a, alpha)})`
}


let myVarVal, myVarVal1, myVarVal2, myVarVal3,primaryColorVal

function names() {
    'use strict'

    primaryColorVal = getComputedStyle(document.documentElement).getPropertyValue('--primary-rgb').trim();

    //get variable
    myVarVal = localStorage.getItem("vexelprimaryColor") || localStorage.getItem("vexeldarkPrimary") || localStorage.getItem("vexeltransparentPrimary") || localStorage.getItem("vexeltransparentBgImgPrimary") ||primaryColorVal;
    myVarVal1 = localStorage.getItem("vexelprimaryColor") || localStorage.getItem("vexeldarkPrimary") || localStorage.getItem("vexeltransparentPrimary") || localStorage.getItem("vexeltransparentBgImgPrimary") || "#05c3fb";

    if (document.querySelector('#transactions') !== null) {
        index(myVarVal, myVarVal1);
    }
}
names();
