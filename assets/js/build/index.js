!function(){"use strict";var e,t={655:function(e,t,o){var n=window.wp.element,i=window.wp.i18n,r=window.wp.blockEditor,s=window.wp.components,a=window.wp.compose,l=window.wp.data,m=window.wp.hooks,c=window.wp.api,d=o.n(c),u=()=>{const{createNotice:e}=(0,l.dispatch)("core/notices"),[t,o]=(0,n.useState)({}),[r,s]=(0,n.useState)("loading"),a=()=>{d().loadPromise.then((async()=>{try{const e=new(d().models.Settings),t=await e.fetch();o(t)}catch(e){s("error")}finally{s("loaded")}}))};return(0,n.useEffect)((()=>{a()}),[]),[e=>null==t?void 0:t[e],function(t,o){let n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:(0,i.__)("Settings saved.","textdomain");s("saving");const r=new(d().models.Settings)({[t]:o}).save();r.success(((t,o)=>{"success"===o&&(s("loaded"),e("success",n,{isDismissible:!0,type:"snackbar"})),"error"===o&&(s("error"),e("error",(0,i.__)("An unknown error occurred.","textdomain"),{isDismissible:!0,type:"snackbar"})),a()})),r.error((t=>{s("error"),e("error",t.responseJSON.message?t.responseJSON.message:(0,i.__)("An unknown error occurred.","textdomain"),{isDismissible:!0,type:"snackbar"})}))},r]};const p=e=>new Promise((t=>{wp.updates.ajax("install-plugin",{slug:e,success:()=>{t({success:!0})},error:e=>{t({success:!1,code:e.errorCode})}})})),h=e=>new Promise((t=>{jQuery.get(e).done((()=>{t({success:!0})})).fail((()=>{t({success:!1})}))})),w=(e,t)=>{const o={};return Object.keys(t).forEach((function(e){"innerBlocks"!==e&&(o[e]=t[e])})),e.push(o),Array.isArray(t.innerBlocks)?(o.innerBlocks=t.innerBlocks.map((e=>e.id)),t.innerBlocks.reduce(w,e)):e},g={button:{display:"flex",justifyContent:"center",width:"100%"},image:{padding:"20px 0"},skip:{container:{display:"flex",flexDirection:"column",alignItems:"center"},button:{fontSize:"9px"},poweredby:{fontSize:"9px",textTransform:"uppercase"}}},_={"blocks-css":{title:(0,i.__)("Custom CSS","textdomain"),description:(0,i.__)("Enable Otter Blocks to add Custom CSS for this block."),image:"css.jpg"},"blocks-animation":{title:(0,i.__)("Animations","textdomain"),description:(0,i.__)("Enable Otter Blocks to add Animations for this block."),image:"animation.jpg"},"blocks-conditions":{title:(0,i.__)("Visibility Conditions","textdomain"),description:(0,i.__)("Enable Otter Blocks to add Visibility Conditions for this block."),image:"conditions.jpg"}},f=e=>{let{onClick:t}=e;return(0,n.createElement)("div",{style:g.skip.container},(0,n.createElement)(s.Button,{style:g.skip.button,variant:"tertiary",onClick:t},(0,i.__)("Skip for now")),(0,n.createElement)("span",{style:g.skip.poweredby},(0,i.__)("Recommended by ")+window.themeisleSDKPromotions.product))},y=(0,a.createHigherOrderComponent)((e=>t=>{if(t.isSelected&&Boolean(window.themeisleSDKPromotions.showPromotion)){const[o,a]=(0,n.useState)(!1),[l,m]=(0,n.useState)("default"),[c,d]=(0,n.useState)(!1),[w,y,E]=u(),k=async()=>{a(!0),await p("otter-blocks"),y("themeisle_sdk_promotions_otter_installed",!Boolean(w("themeisle_sdk_promotions_otter_installed"))),await h(window.themeisleSDKPromotions.otterActivationUrl),a(!1),m("installed")},v=()=>"installed"===l?(0,n.createElement)("p",null,(0,n.createElement)("strong",null,(0,i.__)("Awesome! Refresh the page to see Otter Blocks in action."))):(0,n.createElement)(s.Button,{variant:"secondary",onClick:k,isBusy:o,style:g.button},(0,i.__)("Install & Activate Otter Blocks")),b=()=>{const e={...window.themeisleSDKPromotions.option};e[window.themeisleSDKPromotions.showPromotion]=(new Date).getTime()/1e3|0,y("themeisle_sdk_promotions",JSON.stringify(e)),window.themeisleSDKPromotions.showPromotion=!1};return(0,n.useEffect)((()=>{c&&b()}),[c]),c?(0,n.createElement)(e,t):(0,n.createElement)(n.Fragment,null,(0,n.createElement)(e,t),(0,n.createElement)(r.InspectorControls,null,Object.keys(_).map((e=>{if(e===window.themeisleSDKPromotions.showPromotion){const t=_[e];return(0,n.createElement)(s.PanelBody,{key:e,title:t.title,initialOpen:!1},(0,n.createElement)("p",null,t.description),(0,n.createElement)(v,null),(0,n.createElement)("img",{style:g.image,src:window.themeisleSDKPromotions.assets+t.image}),(0,n.createElement)(f,{onClick:()=>d(!0)}))}}))))}return(0,n.createElement)(e,t)}),"withInspectorControl");(0,l.select)("core/edit-site")||(0,m.addFilter)("editor.BlockEdit","themeisle-sdk/with-inspector-controls",y);var E=window.wp.plugins,k=window.wp.editPost;function v(e){let{stacked:t=!1,noImage:o=!1,type:r,onDismiss:a}=e;const{assets:l,title:m,email:c,option:d,optionKey:w,optimoleActivationUrl:g,optimoleApi:_,optimoleDash:f,nonce:y}=window.themeisleSDKPromotions,[E,k]=(0,n.useState)(!1),[v,b]=(0,n.useState)(c||""),[P,S]=(0,n.useState)(!1),[x,O]=(0,n.useState)(null),[A,D]=u(),B=async()=>{S(!0);const e={...d};e[r]=(new Date).getTime()/1e3|0,window.themeisleSDKPromotions.option=e,await D(w,JSON.stringify(e)),a&&a()},C=()=>{k(!E)},N=e=>{b(e.target.value)},K=async e=>{e.preventDefault(),O("installing"),await p("optimole-wp"),O("activating"),await h(g),D("themeisle_sdk_promotions_optimole_installed",!Boolean(A("themeisle_sdk_promotions_optimole_installed"))),O("connecting");try{await fetch(_,{method:"POST",headers:{"X-WP-Nonce":y,"Content-Type":"application/json"},body:JSON.stringify({email:v})}),O("done")}catch(e){O("done")}};if(P)return null;const j=()=>"done"===x?(0,n.createElement)("div",{className:"done"},(0,n.createElement)("p",null,(0,i.__)("Awesome! You are all set!","textdomain")),(0,n.createElement)(s.Button,{icon:"external",isPrimary:!0,href:f,target:"_blank"},(0,i.__)("Go to Optimole dashboard","textdomain"))):x?(0,n.createElement)("p",{className:"om-progress"},(0,n.createElement)("span",{className:"dashicons dashicons-update spin"}),(0,n.createElement)("span",null,"installing"===x&&(0,i.__)("Installing","textdomain"),"activating"===x&&(0,i.__)("Activating","textdomain"),"connecting"===x&&(0,i.__)("Connecting to API","textdomain"),"…")):(0,n.createElement)(n.Fragment,null,(0,n.createElement)("span",null,(0,i.__)("Enter your email address to create & connect your account","textdomain")),(0,n.createElement)("form",{onSubmit:K},(0,n.createElement)("input",{defaultValue:v,type:"email",onChange:N,placeholder:(0,i.__)("Email address","textdomain")}),(0,n.createElement)(s.Button,{isPrimary:!0,type:"submit"},(0,i.__)("Start using Optimole","textdomain")))),I=()=>(0,n.createElement)(s.Button,{disabled:x&&"done"!==x,onClick:B,isLink:!0,className:"om-notice-dismiss"},(0,n.createElement)("span",{className:"dashicons-no-alt dashicons"}),(0,n.createElement)("span",{className:"screen-reader-text"},"Dismiss this notice."));return t?(0,n.createElement)("div",{className:"ti-om-stack-wrap"},(0,n.createElement)("div",{className:"om-stack-notice"},I(),(0,n.createElement)("img",{src:l+"/optimole-logo.svg",alt:(0,i.__)("Optimole logo","textdomain")}),(0,n.createElement)("h2",null,(0,i.__)("Get more with Optimole","textdomain")),(0,n.createElement)("p",null,"om-editor"===r?(0,i.__)("Increase this page speed and SEO ranking by optimizing images with Optimole.","textdomain"):(0,i.__)("Leverage Optimole's full integration with Elementor to automatically lazyload, resize, compress to AVIF/WebP and deliver from 400 locations around the globe!","textdomain")),!E&&(0,n.createElement)(s.Button,{isPrimary:!0,onClick:C,className:"cta"},(0,i.__)("Get Started Free","textdomain")),E&&j(),(0,n.createElement)("i",null,m))):(0,n.createElement)(n.Fragment,null,I(),(0,n.createElement)("div",{className:"content"},!o&&(0,n.createElement)("img",{src:l+"/optimole-logo.svg",alt:(0,i.__)("Optimole logo","textdomain")}),(0,n.createElement)("div",null,(0,n.createElement)("p",null,m),(0,n.createElement)("p",{className:"description"},"om-media"===r?(0,i.__)("Save your server space by storing images to Optimole and deliver them optimized from 400 locations around the globe. Unlimited images, Unlimited traffic.","textdomain"):(0,i.__)("Optimize, store and deliver this image with 80% less size while looking just as great, using Optimole.","textdomain")),!E&&(0,n.createElement)("div",{className:"actions"},(0,n.createElement)(s.Button,{isPrimary:!0,onClick:C},(0,i.__)("Get Started Free","textdomain")),(0,n.createElement)(s.Button,{isLink:!0,target:"_blank",href:"https://wordpress.org/plugins/optimole-wp"},(0,i.__)("Learn more","textdomain"))),E&&(0,n.createElement)("div",{className:"form-wrap"},j()))))}const b=()=>{const[e,t]=(0,n.useState)(!0),{getBlocks:o}=(0,l.useSelect)((e=>{const{getBlocks:t}=e("core/block-editor");return{getBlocks:t}}));var i;if((i=o(),"core/image",i.reduce(w,[]).filter((e=>"core/image"===e.name))).length<2)return null;const r="ti-sdk-optimole-post-publish "+(e?"":"hidden");return(0,n.createElement)(k.PluginPostPublishPanel,{className:r},(0,n.createElement)(v,{stacked:!0,type:"om-editor",onDismiss:()=>{t(!1)}}))};new class{constructor(){const{showPromotion:e,debug:t}=window.themeisleSDKPromotions;this.promo=e,this.debug="1"===t,this.domRef=null,this.run()}run(){if(this.debug)this.runAll();else switch(this.promo){case"om-attachment":this.runAttachmentPromo();break;case"om-media":this.runMediaPromo();break;case"om-editor":this.runEditorPromo();break;case"om-elementor":this.runElementorPromo()}}runAttachmentPromo(){wp.media.view.Attachment.Details.prototype.on("ready",(()=>{setTimeout((()=>{this.removeAttachmentPromo(),this.addAttachmentPromo()}),100)})),wp.media.view.Modal.prototype.on("close",(()=>{setTimeout(this.removeAttachmentPromo,100)}))}runMediaPromo(){if(window.themeisleSDKPromotions.option["om-media"])return;const e=document.querySelector("#ti-optml-notice");e&&(0,n.render)((0,n.createElement)(v,{type:"om-media",onDismiss:()=>{e.style.opacity=0}}),e)}runEditorPromo(){(0,E.registerPlugin)("post-publish-panel-test",{render:b})}runElementorPromo(){if(!window.elementor)return;const e=this;elementor.on("preview:loaded",(()=>{elementor.panel.currentView.on("set:page:editor",(t=>{e.domRef&&(0,n.unmountComponentAtNode)(e.domRef),t.activeSection&&"section_image"===t.activeSection&&e.runElementorActions(e)}))}))}addAttachmentPromo(){if(this.domRef&&(0,n.unmountComponentAtNode)(this.domRef),window.themeisleSDKPromotions.option["om-attachment"])return;const e=document.querySelector("#ti-optml-notice-helper");e&&(this.domRef=e,(0,n.render)((0,n.createElement)("div",{className:"notice notice-info ti-sdk-om-notice",style:{margin:0}},(0,n.createElement)(v,{noImage:!0,type:"om-attachment",onDismiss:()=>{e.style.opacity=0}})),e))}removeAttachmentPromo(){const e=document.querySelector("#ti-optml-notice-helper");e&&(0,n.unmountComponentAtNode)(e)}runElementorActions(e){if(window.themeisleSDKPromotions.option["om-elementor"])return;const t=document.querySelector("#elementor-panel__editor__help"),o=document.createElement("div");o.id="ti-optml-notice",e.domRef=o,t&&(t.parentNode.insertBefore(o,t),(0,n.render)((0,n.createElement)(v,{stacked:!0,type:"om-elementor",onDismiss:()=>{o.style.opacity=0}}),o))}runAll(){this.runAttachmentPromo(),this.runMediaPromo(),this.runEditorPromo(),this.runElementorPromo()}}}},o={};function n(e){var i=o[e];if(void 0!==i)return i.exports;var r=o[e]={exports:{}};return t[e](r,r.exports,n),r.exports}n.m=t,e=[],n.O=function(t,o,i,r){if(!o){var s=1/0;for(c=0;c<e.length;c++){o=e[c][0],i=e[c][1],r=e[c][2];for(var a=!0,l=0;l<o.length;l++)(!1&r||s>=r)&&Object.keys(n.O).every((function(e){return n.O[e](o[l])}))?o.splice(l--,1):(a=!1,r<s&&(s=r));if(a){e.splice(c--,1);var m=i();void 0!==m&&(t=m)}}return t}r=r||0;for(var c=e.length;c>0&&e[c-1][2]>r;c--)e[c]=e[c-1];e[c]=[o,i,r]},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,{a:t}),t},n.d=function(e,t){for(var o in t)n.o(t,o)&&!n.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={826:0,431:0};n.O.j=function(t){return 0===e[t]};var t=function(t,o){var i,r,s=o[0],a=o[1],l=o[2],m=0;if(s.some((function(t){return 0!==e[t]}))){for(i in a)n.o(a,i)&&(n.m[i]=a[i]);if(l)var c=l(n)}for(t&&t(o);m<s.length;m++)r=s[m],n.o(e,r)&&e[r]&&e[r][0](),e[r]=0;return n.O(c)},o=self.webpackChunkthemeisle_sdk=self.webpackChunkthemeisle_sdk||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))}();var i=n.O(void 0,[431],(function(){return n(655)}));i=n.O(i)}();