/*
 * this script loads and stores ONE variable to an external server
 * it is designed to be embedded in a storyline block in rise
 * data is abstracted by course, page, question and user
 * it can be used multiple times on the same course without overwriting data (by using the block id, so don't duplicate pages)
 * it is multi-user aware, so it can be used by multiple users on the same course without overwriting data
 */

// This script is generic and exeucutes on timeline start AND on submit button click
// the following variable denotes the action to take
const Action = 'load'; // load || store || download

/* ------------- don't edit below here ------------- */

const STORYLINE = GetPlayer();
const VARIABLE = 'TextEntry';
const BLOCK = window.name;

// a reasonably fast md5 implementation for strings
function md5(n){var r="0123456789abcdef";function t(n){var t,u="";for(t=0;t<=3;t++)u+=r.charAt(n>>8*t+4&15)+r.charAt(n>>8*t&15);return u}function u(n,r){var t=(65535&n)+(65535&r);return(n>>16)+(r>>16)+(t>>16)<<16|65535&t}function e(n,r,t,e,f,o){return u(function(n,r){return n<<r|n>>>32-r}(u(u(r,n),u(e,o)),f),t)}function f(n,r,t,u,f,o,c){return e(r&t|~r&u,n,r,f,o,c)}function o(n,r,t,u,f,o,c){return e(r&u|t&~u,n,r,f,o,c)}function c(n,r,t,u,f,o,c){return e(r^t^u,n,r,f,o,c)}function a(n,r,t,u,f,o,c){return e(t^(r|~u),n,r,f,o,c)}var i,h,v,g,l,A=function(n){var r,t=1+(n.length+8>>6),u=new Array(16*t);for(r=0;r<16*t;r++)u[r]=0;for(r=0;r<n.length;r++)u[r>>2]|=n.charCodeAt(r)<<r%4*8;return u[r>>2]|=128<<r%4*8,u[16*t-2]=8*n.length,u}(""+n),d=1732584193,b=-271733879,m=-1732584194,w=271733878;for(i=0;i<A.length;i+=16)h=d,v=b,g=m,l=w,d=f(d,b,m,w,A[i+0],7,-680876936),w=f(w,d,b,m,A[i+1],12,-389564586),m=f(m,w,d,b,A[i+2],17,606105819),b=f(b,m,w,d,A[i+3],22,-1044525330),d=f(d,b,m,w,A[i+4],7,-176418897),w=f(w,d,b,m,A[i+5],12,1200080426),m=f(m,w,d,b,A[i+6],17,-1473231341),b=f(b,m,w,d,A[i+7],22,-45705983),d=f(d,b,m,w,A[i+8],7,1770035416),w=f(w,d,b,m,A[i+9],12,-1958414417),m=f(m,w,d,b,A[i+10],17,-42063),b=f(b,m,w,d,A[i+11],22,-1990404162),d=f(d,b,m,w,A[i+12],7,1804603682),w=f(w,d,b,m,A[i+13],12,-40341101),m=f(m,w,d,b,A[i+14],17,-1502002290),d=o(d,b=f(b,m,w,d,A[i+15],22,1236535329),m,w,A[i+1],5,-165796510),w=o(w,d,b,m,A[i+6],9,-1069501632),m=o(m,w,d,b,A[i+11],14,643717713),b=o(b,m,w,d,A[i+0],20,-373897302),d=o(d,b,m,w,A[i+5],5,-701558691),w=o(w,d,b,m,A[i+10],9,38016083),m=o(m,w,d,b,A[i+15],14,-660478335),b=o(b,m,w,d,A[i+4],20,-405537848),d=o(d,b,m,w,A[i+9],5,568446438),w=o(w,d,b,m,A[i+14],9,-1019803690),m=o(m,w,d,b,A[i+3],14,-187363961),b=o(b,m,w,d,A[i+8],20,1163531501),d=o(d,b,m,w,A[i+13],5,-1444681467),w=o(w,d,b,m,A[i+2],9,-51403784),m=o(m,w,d,b,A[i+7],14,1735328473),d=c(d,b=o(b,m,w,d,A[i+12],20,-1926607734),m,w,A[i+5],4,-378558),w=c(w,d,b,m,A[i+8],11,-2022574463),m=c(m,w,d,b,A[i+11],16,1839030562),b=c(b,m,w,d,A[i+14],23,-35309556),d=c(d,b,m,w,A[i+1],4,-1530992060),w=c(w,d,b,m,A[i+4],11,1272893353),m=c(m,w,d,b,A[i+7],16,-155497632),b=c(b,m,w,d,A[i+10],23,-1094730640),d=c(d,b,m,w,A[i+13],4,681279174),w=c(w,d,b,m,A[i+0],11,-358537222),m=c(m,w,d,b,A[i+3],16,-722521979),b=c(b,m,w,d,A[i+6],23,76029189),d=c(d,b,m,w,A[i+9],4,-640364487),w=c(w,d,b,m,A[i+12],11,-421815835),m=c(m,w,d,b,A[i+15],16,530742520),d=a(d,b=c(b,m,w,d,A[i+2],23,-995338651),m,w,A[i+0],6,-198630844),w=a(w,d,b,m,A[i+7],10,1126891415),m=a(m,w,d,b,A[i+14],15,-1416354905),b=a(b,m,w,d,A[i+5],21,-57434055),d=a(d,b,m,w,A[i+12],6,1700485571),w=a(w,d,b,m,A[i+3],10,-1894986606),m=a(m,w,d,b,A[i+10],15,-1051523),b=a(b,m,w,d,A[i+1],21,-2054922799),d=a(d,b,m,w,A[i+8],6,1873313359),w=a(w,d,b,m,A[i+15],10,-30611744),m=a(m,w,d,b,A[i+6],15,-1560198380),b=a(b,m,w,d,A[i+13],21,1309151649),d=a(d,b,m,w,A[i+4],6,-145523070),w=a(w,d,b,m,A[i+11],10,-1120210379),m=a(m,w,d,b,A[i+2],15,718787259),b=a(b,m,w,d,A[i+9],21,-343485551),d=u(d,h),b=u(b,v),m=u(m,g),w=u(w,l);return t(d)+t(b)+t(m)+t(w)}

function GetServerLocation() {
    return fetch('/local/blobstorebackend/', { method: 'HEAD' })
        .then(r => {
            if (r.ok) return '/local/blobstorebackend/'; // moodle plugin exists
            throw new Error;
        }).catch((error) => {
            return 'https://blob.frumbert.org'; // fallback to public server
        });
}
let SERVER = GetServerLocation();


// test for cross domain script access
function isCrossDomain() {
    try {
        window.parent.document;
    } catch (e) {
        return true;
    }
    return false;
}

function temporaryId(kind) {
    let identifier = sessionStorage.getItem(`${kind}_identifier`);
    if (!identifier) {
        identifier = crypto.randomUUID().slice(-12);
        sessionStorage.setItem(`${kind}_identifier`, identifier);
    }
    return identifier;
}

function findRuntimeWindow(win) {
    try {
        if (win.hasOwnProperty("courseData") || win.hasOwnProperty("courseId")) return win;
        else if (win.parent == win) return null;
        else return findRuntimeWindow(win.parent);
    } catch (e) {
        return window;
    }
}

function findLMSAPI(win) {
    try {
     if (win.hasOwnProperty("GetStudentID")) return win;
     else if (win.parent == win) return null;
     else return findLMSAPI(win.parent);
    } catch(e) {
        return null;
    }
}

function createDigest(str) {
    // could also md5 the value to ensure the data is abstracted before being transmitted
    return btoa(encodeURIComponent(str)).replace(/\//g,'_').replace(/\+/g,'-').replace(/\=/g,'-');
}

// set some properties based on content in the PARENT frame, where accessible
let courseTitle = window.document.title;
let contextId = temporaryId('context');
let pageTitle = '-';
let questionText = '-';
const runtimeWindow = findRuntimeWindow(this);

if (window.parent !== window.self && !isCrossDomain()) {
    const IFRAME = parent.document.querySelector(`iframe[name="${BLOCK}"]`);

    // try matching the background colour of the RISE block
    try {
        if (!document.querySelector('#stylepatch')) {
            let style = parent.window.getComputedStyle(IFRAME.closest('.blocks-storyline'));
            let mystyle = document.createElement('style');
            mystyle.id = 'stylepatch';
            mystyle.textContent = 'body,#wrapper,.slide .dropin-wrap rect[id^="slide-bg-"] { background-color: ' + style.backgroundColor + ' !important; fill: rgba(0,0,0,0) !important; }';
            document.body.appendChild(mystyle);
            mystyle = parent.document.createElement('style'); // shrink the padding on the parent frame
            mystyle.textContent = '.blocks-storyline__wrapper { padding: 0 1.9999998rem !important; }';
            parent.document.body.appendChild(mystyle);
        }
    } catch(e) {}

    // try getting the course title
    // during development/preview, window.courseId is set. It will match to window.courseData.course.id when the course is exported/in review.
    try {
        if (runtimeWindow.hasOwnProperty('courseData')) {
            let c = JSON.parse(atob(runtimeWindow.courseData)).course;
            // console.log('found course data (published)', c);
            courseTitle = c.title;
            contextId = c.id;
        } else if (runtimeWindow.hasOwnProperty('courseId')) {
            // console.log('found course id (runtime?)');
            courseTitle = runtimeWindow.courseId;
            contextId = runtimeWindow.courseId;
        } else {
            // console.log('did not find course title');
            courseTitle = parent.document.title;
        }
    } catch(e) {}

    // try getting the page heading
    try {
        pageTitle = parent.document.querySelector('.lesson-header-wrap h1').textContent;
        // actually this matches parent.document.title as well
        // console.log('Found parent frame heading', pageTitle);
    } catch (e) {}

    // try getting the question text (block previous to this one)
    try {
        questionText = IFRAME.closest('[data-block-id]').previousElementSibling.textContent;
        // console.log('Found question text', questionText);
    } catch(e) {}

}

// if the player can find a scorm runtime, get it so that we can identify the user (otherwise we'll use a sessionStorage id)
let user = temporaryId('user');
let [myName, myId] = [user, user];
const lmsAPI = findLMSAPI(this);
if (lmsAPI) {
    myName = lmsAPI.GetStudentName();
    myId = lmsAPI.GetStudentID();
}
const digest = createDigest(myName + myId);

// load the data from the server and set the storyline variable
function LOAD_DATA() {
    SERVER.then((endpoint) => {
        let url = new URL(`${endpoint}/${digest}/${contextId}/${BLOCK}/`);
        return fetch(url.toString(), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': md5(location.origin),
                'Cache-Control': 'no-cache'
            },
            cache: "no-store"
        });
    }).then((response) => {
        if (!response.ok) {
            console.error(response);
            throw new Error('Network response was not ok');
        }
        return response.json();
    }).then((json) => {
        // console.info('Success:', json);
        STORYLINE.SetVar(VARIABLE, json.answer);
    }).catch((error) => {
        console.error('Error:', error);
    });
}

// store the storyline variable on the server
function STORE_DATA() {
    SERVER.then((endpoint) => {
        let url = new URL(`${endpoint}/${digest}/${contextId}/${BLOCK}/`);
        return fetch(url.toString(), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': md5(location.origin),
                'Cache-Control': 'no-cache'
            },
            body: JSON.stringify({
                course: courseTitle,
                page: pageTitle,
                question: questionText,
                answer: STORYLINE.GetVar(VARIABLE)
            }),
            cache: "no-store"
        });
    }).then((response) => {
        if (!response.ok) {
            console.error(response);
            throw new Error('Network response was not ok');
        }
        return response.json();
    }).then((json) => {
        // console.info('Success:', json);
    }).catch((error) => {
        console.error('Error:', error);
    });
}

// main
switch (Action) {
    case "store":
        STORE_DATA();
        break;
    case "load":
        LOAD_DATA();
        STORYLINE.SetVar("fld_name", myName);
        STORYLINE.SetVar("fld_iud", myId);
        break;
}
