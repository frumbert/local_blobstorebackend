function md5(n){var r="0123456789abcdef";function t(n){var t,u="";for(t=0;t<=3;t++)u+=r.charAt(n>>8*t+4&15)+r.charAt(n>>8*t&15);return u}function u(n,r){var t=(65535&n)+(65535&r);return(n>>16)+(r>>16)+(t>>16)<<16|65535&t}function e(n,r,t,e,f,o){return u(function(n,r){return n<<r|n>>>32-r}(u(u(r,n),u(e,o)),f),t)}function f(n,r,t,u,f,o,c){return e(r&t|~r&u,n,r,f,o,c)}function o(n,r,t,u,f,o,c){return e(r&u|t&~u,n,r,f,o,c)}function c(n,r,t,u,f,o,c){return e(r^t^u,n,r,f,o,c)}function a(n,r,t,u,f,o,c){return e(t^(r|~u),n,r,f,o,c)}var i,h,v,g,l,A=function(n){var r,t=1+(n.length+8>>6),u=new Array(16*t);for(r=0;r<16*t;r++)u[r]=0;for(r=0;r<n.length;r++)u[r>>2]|=n.charCodeAt(r)<<r%4*8;return u[r>>2]|=128<<r%4*8,u[16*t-2]=8*n.length,u}(""+n),d=1732584193,b=-271733879,m=-1732584194,w=271733878;for(i=0;i<A.length;i+=16)h=d,v=b,g=m,l=w,d=f(d,b,m,w,A[i+0],7,-680876936),w=f(w,d,b,m,A[i+1],12,-389564586),m=f(m,w,d,b,A[i+2],17,606105819),b=f(b,m,w,d,A[i+3],22,-1044525330),d=f(d,b,m,w,A[i+4],7,-176418897),w=f(w,d,b,m,A[i+5],12,1200080426),m=f(m,w,d,b,A[i+6],17,-1473231341),b=f(b,m,w,d,A[i+7],22,-45705983),d=f(d,b,m,w,A[i+8],7,1770035416),w=f(w,d,b,m,A[i+9],12,-1958414417),m=f(m,w,d,b,A[i+10],17,-42063),b=f(b,m,w,d,A[i+11],22,-1990404162),d=f(d,b,m,w,A[i+12],7,1804603682),w=f(w,d,b,m,A[i+13],12,-40341101),m=f(m,w,d,b,A[i+14],17,-1502002290),d=o(d,b=f(b,m,w,d,A[i+15],22,1236535329),m,w,A[i+1],5,-165796510),w=o(w,d,b,m,A[i+6],9,-1069501632),m=o(m,w,d,b,A[i+11],14,643717713),b=o(b,m,w,d,A[i+0],20,-373897302),d=o(d,b,m,w,A[i+5],5,-701558691),w=o(w,d,b,m,A[i+10],9,38016083),m=o(m,w,d,b,A[i+15],14,-660478335),b=o(b,m,w,d,A[i+4],20,-405537848),d=o(d,b,m,w,A[i+9],5,568446438),w=o(w,d,b,m,A[i+14],9,-1019803690),m=o(m,w,d,b,A[i+3],14,-187363961),b=o(b,m,w,d,A[i+8],20,1163531501),d=o(d,b,m,w,A[i+13],5,-1444681467),w=o(w,d,b,m,A[i+2],9,-51403784),m=o(m,w,d,b,A[i+7],14,1735328473),d=c(d,b=o(b,m,w,d,A[i+12],20,-1926607734),m,w,A[i+5],4,-378558),w=c(w,d,b,m,A[i+8],11,-2022574463),m=c(m,w,d,b,A[i+11],16,1839030562),b=c(b,m,w,d,A[i+14],23,-35309556),d=c(d,b,m,w,A[i+1],4,-1530992060),w=c(w,d,b,m,A[i+4],11,1272893353),m=c(m,w,d,b,A[i+7],16,-155497632),b=c(b,m,w,d,A[i+10],23,-1094730640),d=c(d,b,m,w,A[i+13],4,681279174),w=c(w,d,b,m,A[i+0],11,-358537222),m=c(m,w,d,b,A[i+3],16,-722521979),b=c(b,m,w,d,A[i+6],23,76029189),d=c(d,b,m,w,A[i+9],4,-640364487),w=c(w,d,b,m,A[i+12],11,-421815835),m=c(m,w,d,b,A[i+15],16,530742520),d=a(d,b=c(b,m,w,d,A[i+2],23,-995338651),m,w,A[i+0],6,-198630844),w=a(w,d,b,m,A[i+7],10,1126891415),m=a(m,w,d,b,A[i+14],15,-1416354905),b=a(b,m,w,d,A[i+5],21,-57434055),d=a(d,b,m,w,A[i+12],6,1700485571),w=a(w,d,b,m,A[i+3],10,-1894986606),m=a(m,w,d,b,A[i+10],15,-1051523),b=a(b,m,w,d,A[i+1],21,-2054922799),d=a(d,b,m,w,A[i+8],6,1873313359),w=a(w,d,b,m,A[i+15],10,-30611744),m=a(m,w,d,b,A[i+6],15,-1560198380),b=a(b,m,w,d,A[i+13],21,1309151649),d=a(d,b,m,w,A[i+4],6,-145523070),w=a(w,d,b,m,A[i+11],10,-1120210379),m=a(m,w,d,b,A[i+2],15,718787259),b=a(b,m,w,d,A[i+9],21,-343485551),d=u(d,h),b=u(b,v),m=u(m,g),w=u(w,l);return t(d)+t(b)+t(m)+t(w)}

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
    return btoa(encodeURIComponent(str)).replace(/\//g,'_').replace(/\+/g,'-').replace(/\=/g,'-');
}

let user = temporaryId('user');
let [myName, myId] = [user, user];
const lmsAPI = findLMSAPI(this);
if (lmsAPI) {
    myName = lmsAPI.GetStudentName();
    myId = lmsAPI.GetStudentID();
}
const digest = createDigest(myName + myId);

if (window.parent !== window.self && !isCrossDomain()) {

    if (parent.document.querySelector("#NOTES_APP")) {
        const iframe = parent.document.querySelector(`iframe[name="${window.name}"]`);
        if (iframe) iframe.closest('div[data-block-id]').remove();
    } else {
        script = parent.document.createElement('script');
        script.id = 'NOTES_APP';
        script.textContent = `
const NOTES = {
  digest: '` + digest + `',
  blockId: '` + window.name + `',
  authorization: '` + md5(parent.location.origin) + `',
  contextId: JSON.parse(atob(window.courseData)).course.id,
  courseTitle: JSON.parse(atob(window.courseData)).course.title,
  show: function () {
    const text = document.querySelector('.lesson-header-wrap h1').textContent;
    let value = (new Date().toLocaleString()) + "\\n"; // default note is timestamp
    NOTES.request(btoa(text), 'load')
      .then(r => {
        if (r.ok) return r.json();
        throw new Error;
      })
      .then(r => {
        if (r.note) NOTES.open(r.note + "\\n\\n" + value); // existing note plus new identifier
      })
      .catch(e => {
        console.error(e);
        NOTES.open(value);
      });
  },
  open: function(value) {
    document.body.classList.add('note-visible');
    document.querySelector('#notepad textarea').value = value;
    document.querySelector('#notepad textarea').focus();
  },
  hide: function() {
    document.body.classList.remove('note-visible');
  },
  clear: function () {
    NOTES.request('clear', 'clear')
      .then(r => {
        if (r.ok) {
          document.querySelector('#notepad textarea').value = '';
          NOTES.hide();
        }
      })
      .catch(e => {
        console.error(e);
      });
  },
  save: function() {
    const page = document.querySelector('.lesson-header-wrap h1').textContent;
    NOTES.request(btoa(page), 'save', {
        course: NOTES.courseTitle,
        page,
        note: document.querySelector('#notepad textarea').value
      })
      .then(r => {
        if (r.ok) {
          document.querySelector('#notepad textarea').value = '';
          NOTES.hide();
        }
      })
      .catch(e => {
        console.error(e);
      });
  },
  toggle: function() {
    if (document.body.classList.contains('note-visible')) {
      NOTES.hide();
    } else {
      NOTES.show();
    }
  },
  pdf: function () {
    NOTES.request('download', 'download')
      .then(r => {
        if (r.ok) return r.json();
        throw new Error;
      })
      .then(r => {
        const a = document.createElement('a');
        a.href = r.link;
        a.target = '_blank';
        a.click();
      })
      .catch(e => {
        console.error(e);
      });
  },
  server: function () {
    return fetch('/local/blobstorebackend/', { method: 'HEAD' })
      .then(r => {
          if (r.ok) return '/local/blobstorebackend/'; // moodle plugin exists
          throw new Error;
      }).catch((error) => {
          return 'https://blob.frumbert.org'; // fallback to public server
      });
  },
  request: function(block, action, body) {
    return new Promise((resolve,reject) => {
      NOTES.server().then((endpoint) => {
        const url = new URL([endpoint,NOTES.digest,NOTES.contextId,block].join('/'));
        url.searchParams.append('kind', 'note');
        const headers = {
          'Content-Type': 'application/json',
          'Authorization': NOTES.authorization,
          'Cache-Control': 'no-cache'
        };
        const method = action === 'download' || action === 'load' ? 'GET' : action === 'clear' ? 'DELETE' : 'PUT';
        let options = {method,headers,cache:"no-store"};
        if (body) options.body = JSON.stringify(body);
        return fetch(url.toString(), options);
      })
      .then(r => {
        if (r.ok) resolve(r);
        reject(r);
      })
    });
  },
  init: function() {
    const ns=document.createElement('style');
    ns.textContent='body.note-visible {& #notepad {pointer-events: unset !important;visibility: visible;}& #notebutton {background: yellow;}}#notebutton, #notepad {position: fixed;z-index: 2147483646;top:1rem;right:1rem;transition: background 0.5s;& a {text-decoration: none;color: black;}}#notepad::before{content:"";border:5px solid transparent;border-bottom-color:white;position:absolute;right:10px;top:-10px;}#notepad {top: 4rem;min-width: 25vw;height: 150px;padding: 10px;border-radius: 10px;box-shadow: 0 5px 5px #00000030;pointer-events: none;visibility: hidden;background:white;& textarea {font-family: sans-serif;font-size: 12px;width: 100%;height: 112px;resize: none;border: none;box-shadow: inset 0 0 1px 0 grey;}& div {font-family: sans-serif;font-size: 12px;display: flex;& span {flex: 1;}& a:not(:last-of-type) {padding-right: 12px;margin-right: 12px;}& a:nth-child(1) {border-right: 1px solid grey;}& a:nth-child(2) {border-right: 1px solid grey;}}}';
    document.body.appendChild(ns);
    document.querySelector('#app').insertAdjacentHTML('afterend', '<div id="notebutton"><a href="javascript:;" onclick="NOTES.toggle()">🗒️</a></div><div id="notepad"><div><a href="javascript:;" onclick="NOTES.save()" title="Save note and close">💾 Save</a><a href="javascript:;" onclick="NOTES.pdf()" title="Download all notes as PDF">🖨️ PDF</a><a href="javascript:;" onclick="NOTES.clear()" title="Clear ALL notes">🗑️ Clear all</a><span></span><a href="javascript:;" onclick="NOTES.hide()" title="Close notepad">&times; Close</a></div><textarea>enter your note here</textarea></div>');
    const iframe = document.querySelector('iframe[name="' + NOTES.blockId + '"]');
    if (iframe) iframe.closest('div[data-block-id]').remove(); // removes the storyline block that injected this
  }
 }
 NOTES.init();
        `;
        parent.document.body.appendChild(script);
    }
}

