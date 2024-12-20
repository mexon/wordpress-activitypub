(()=>{"use strict";var e,t={505:(e,t,a)=>{const n=window.wp.blocks,r=window.React,l=window.wp.blockEditor,o=window.wp.element,s=window.wp.i18n,i=window.wp.components,c=window.wp.apiFetch;var u=a.n(c);const{namespace:m,defaultAvatarUrl:h}=window._activityPubOptions,p=({reactions:e})=>{const[t,a]=(0,o.useState)(new Set),[n,l]=(0,o.useState)(new Map),s=(0,o.useRef)([]),i=()=>{s.current.forEach((e=>clearTimeout(e))),s.current=[]},c=(t,n)=>{i();const r=100,o=e.length;n&&l((e=>{const a=new Map(e);return a.set(t,"clockwise"),a}));const c=e=>{const i="right"===e,c=i?o-1:0,u=i?1:-1;for(let e=i?t:t-1;i?e<=c:e>=c;e+=u){const o=Math.abs(e-t),i=setTimeout((()=>{a((t=>{const a=new Set(t);return n?a.add(e):a.delete(e),a})),n&&e!==t&&l((t=>{const a=new Map(t),n=e-u,r=a.get(n);return a.set(e,"clockwise"===r?"counter":"clockwise"),a}))}),o*r);s.current.push(i)}};if(c("right"),c("left"),!n){const e=Math.max((o-t)*r,t*r),a=setTimeout((()=>{l(new Map)}),e+r);s.current.push(a)}};return(0,o.useEffect)((()=>()=>i()),[]),(0,r.createElement)("ul",{className:"reaction-avatars"},e.map(((e,a)=>{const l=n.get(a),o=["reaction-avatar",t.has(a)?"wave-active":"",l?`rotate-${l}`:""].filter(Boolean).join(" "),s=e.avatar||h;return(0,r.createElement)("li",{key:a},(0,r.createElement)("a",{href:e.url,target:"_blank",rel:"noopener noreferrer",onMouseEnter:()=>c(a,!0),onMouseLeave:()=>c(a,!1)},(0,r.createElement)("img",{src:s,alt:e.name,className:o,width:"32",height:"32"})))})))},f=({reactions:e,type:t})=>(0,r.createElement)("ul",{className:"reaction-list"},e.map(((e,t)=>(0,r.createElement)("li",{key:t},(0,r.createElement)("a",{href:e.url,className:"reaction-item",target:"_blank",rel:"noopener noreferrer"},(0,r.createElement)("img",{src:e.avatar,alt:e.name,width:"32",height:"32"}),(0,r.createElement)("span",null,e.name)))))),d=({items:e,label:t})=>{const[a,n]=(0,o.useState)(!1),[l,s]=(0,o.useState)(null),[c,u]=(0,o.useState)(e.length),m=(0,o.useRef)(null);(0,o.useEffect)((()=>{if(!m.current)return;const t=()=>{const t=m.current;if(!t)return;const a=t.offsetWidth-(l?.offsetWidth||0)-12,n=Math.max(1,Math.floor((a-32)/22));u(Math.min(n,e.length))};t();const a=new ResizeObserver(t);return a.observe(m.current),()=>{a.disconnect()}}),[l,e.length]);const h=e.slice(0,c);return(0,r.createElement)("div",{className:"reaction-group",ref:m},(0,r.createElement)(p,{reactions:h}),(0,r.createElement)(i.Button,{ref:s,className:"reaction-label is-link",onClick:()=>n(!a),"aria-expanded":a},t),a&&l&&(0,r.createElement)(i.Popover,{anchor:l,onClose:()=>n(!1)},(0,r.createElement)(f,{reactions:e})))};function g({title:e="",postId:t=null,reactions:a=null,titleComponent:n=null}){const[l,s]=(0,o.useState)(a),[i,c]=(0,o.useState)(!a);return(0,o.useEffect)((()=>{if(a)return s(a),void c(!1);t?(c(!0),u()({path:`/${m}/posts/${t}/reactions`}).then((e=>{s(e),c(!1)})).catch((()=>c(!1)))):c(!1)}),[t,a]),i?null:l&&Object.values(l).some((e=>e.items?.length>0))?(0,r.createElement)("div",{className:"activitypub-reactions"},n||e&&(0,r.createElement)("h6",null,e),Object.entries(l).map((([e,t])=>t.items?.length?(0,r.createElement)(d,{key:e,items:t.items,label:t.label}):null))):null}const v=e=>{const t=["#FF6B6B","#4ECDC4","#45B7D1","#96CEB4","#FFEEAD","#D4A5A5","#9B59B6","#3498DB","#E67E22"],a=(()=>{const e=["Bouncy","Cosmic","Dancing","Fluffy","Giggly","Hoppy","Jazzy","Magical","Nifty","Perky","Quirky","Sparkly","Twirly","Wiggly","Zippy"],t=["Badger","Capybara","Dolphin","Echidna","Flamingo","Giraffe","Hedgehog","Iguana","Jellyfish","Koala","Lemur","Manatee","Narwhal","Octopus","Penguin"];return`${e[Math.floor(Math.random()*e.length)]} ${t[Math.floor(Math.random()*t.length)]}`})(),n=t[Math.floor(Math.random()*t.length)],r=a.charAt(0),l=document.createElement("canvas");l.width=64,l.height=64;const o=l.getContext("2d");return o.fillStyle=n,o.beginPath(),o.arc(32,32,32,0,2*Math.PI),o.fill(),o.fillStyle="#FFFFFF",o.font="32px sans-serif",o.textAlign="center",o.textBaseline="middle",o.fillText(r,32,32),{name:a,url:"#",avatar:l.toDataURL()}},w=JSON.parse('{"UU":"activitypub/reactions"}');(0,n.registerBlockType)(w.UU,{edit:function({attributes:e,setAttributes:t,__unstableLayoutClassNames:a}){const n=(0,l.useBlockProps)({className:a}),[i]=(0,o.useState)({likes:{label:"9 likes",items:Array.from({length:9},((e,t)=>v()))},reposts:{label:"6 reposts",items:Array.from({length:6},((e,t)=>v()))}}),c=(0,r.createElement)(l.RichText,{tagName:"h6",value:e.title,onChange:e=>t({title:e}),placeholder:(0,s.__)("Fediverse reactions","activitypub"),disableLineBreaks:!0,allowedFormats:[]});return(0,r.createElement)("div",{...n},(0,r.createElement)(g,{titleComponent:c,reactions:i}))}})}},a={};function n(e){var r=a[e];if(void 0!==r)return r.exports;var l=a[e]={exports:{}};return t[e](l,l.exports,n),l.exports}n.m=t,e=[],n.O=(t,a,r,l)=>{if(!a){var o=1/0;for(u=0;u<e.length;u++){a=e[u][0],r=e[u][1],l=e[u][2];for(var s=!0,i=0;i<a.length;i++)(!1&l||o>=l)&&Object.keys(n.O).every((e=>n.O[e](a[i])))?a.splice(i--,1):(s=!1,l<o&&(o=l));if(s){e.splice(u--,1);var c=r();void 0!==c&&(t=c)}}return t}l=l||0;for(var u=e.length;u>0&&e[u-1][2]>l;u--)e[u]=e[u-1];e[u]=[a,r,l]},n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var a in t)n.o(t,a)&&!n.o(e,a)&&Object.defineProperty(e,a,{enumerable:!0,get:t[a]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={608:0,104:0};n.O.j=t=>0===e[t];var t=(t,a)=>{var r,l,o=a[0],s=a[1],i=a[2],c=0;if(o.some((t=>0!==e[t]))){for(r in s)n.o(s,r)&&(n.m[r]=s[r]);if(i)var u=i(n)}for(t&&t(a);c<o.length;c++)l=o[c],n.o(e,l)&&e[l]&&e[l][0](),e[l]=0;return n.O(u)},a=self.webpackChunkwordpress_activitypub=self.webpackChunkwordpress_activitypub||[];a.forEach(t.bind(null,0)),a.push=t.bind(null,a.push.bind(a))})();var r=n.O(void 0,[104],(()=>n(505)));r=n.O(r)})();