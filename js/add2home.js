var addToHome=function(e){function t(){if(k){var t,i=Date.now();if(e.addToHomeConfig)for(t in e.addToHomeConfig)$[t]=e.addToHomeConfig[t];$.autostart||($.hookOnLoad=!1),d=/ipad/gi.test(C.platform),h=e.devicePixelRatio&&e.devicePixelRatio>1,p=/Safari/i.test(C.appVersion)&&!/CriOS/i.test(C.appVersion),f=C.standalone,m=C.appVersion.match(/OS (\d+_\d+)/i),m=m&&m[1]?+m[1].replace("_","."):0,E=+e.localStorage.getItem("addToHome"),v=e.sessionStorage.getItem("addToHomeSession"),y=$.returningVisitor?E&&E+24192e5>i:!0,E||(E=i),g=y&&i>=E,$.hookOnLoad?e.addEventListener("load",n,!1):!$.hookOnLoad&&$.autostart&&n()}}function n(){if(e.removeEventListener("load",n,!1),y?$.expire&&g&&e.localStorage.setItem("addToHome",Date.now()+6e4*$.expire):e.localStorage.setItem("addToHome",Date.now()),w||p&&g&&!v&&!f&&y){var t="",o=C.platform.split(" ")[0],a=C.language.replace("-","_");b=document.createElement("div"),b.id="addToHomeScreen",b.style.cssText+="left:-9999px;-webkit-transition-property:-webkit-transform,opacity;-webkit-transition-duration:0;-webkit-transform:translate3d(0,0,0);position:"+(5>m?"absolute":"fixed"),$.message in I&&(a=$.message,$.message=""),""===$.message&&($.message=a in I?I[a]:I.en_us),$.touchIcon&&(t=h?document.querySelector('head link[rel^=apple-touch-icon][sizes="114x114"],head link[rel^=apple-touch-icon][sizes="144x144"],head link[rel^=apple-touch-icon]'):document.querySelector('head link[rel^=apple-touch-icon][sizes="57x57"],head link[rel^=apple-touch-icon]'),t&&(t='<span style="background-image:url('+t.href+')" class="addToHomeTouchIcon"></span>')),b.className=(d?"addToHomeIpad":"addToHomeIphone")+(t?" addToHomeWide":""),b.innerHTML=t+$.message.replace("%device",o).replace("%icon",m>=4.2?'<span class="addToHomeShare"></span>':'<span class="addToHomePlus">+</span>')+($.arrow?'<span class="addToHomeArrow"></span>':"")+($.closeButton?'<span class="addToHomeClose">×</span>':""),document.body.appendChild(b),$.closeButton&&b.addEventListener("click",r,!1),!d&&m>=6&&window.addEventListener("orientationchange",u,!1),setTimeout(i,$.startDelay)}}function i(){var t,n=208;if(d)switch(5>m?(S=e.scrollY,T=e.scrollX):6>m&&(n=160),b.style.top=S+$.bottomOffset+"px",b.style.left=T+n-Math.round(b.offsetWidth/2)+"px",$.animationIn){case"drop":t="0.6s",b.style.webkitTransform="translate3d(0,"+-(e.scrollY+$.bottomOffset+b.offsetHeight)+"px,0)";break;case"bubble":t="0.6s",b.style.opacity="0",b.style.webkitTransform="translate3d(0,"+(S+50)+"px,0)";break;default:t="1s",b.style.opacity="0"}else switch(S=e.innerHeight+e.scrollY,5>m?(T=Math.round((e.innerWidth-b.offsetWidth)/2)+e.scrollX,b.style.left=T+"px",b.style.top=S-b.offsetHeight-$.bottomOffset+"px"):(b.style.left="50%",b.style.marginLeft=-Math.round(b.offsetWidth/2)-(e.orientation%180&&m>=6?40:0)+"px",b.style.bottom=$.bottomOffset+"px"),$.animationIn){case"drop":t="1s",b.style.webkitTransform="translate3d(0,"+-(S+$.bottomOffset)+"px,0)";break;case"bubble":t="0.6s",b.style.webkitTransform="translate3d(0,"+(b.offsetHeight+$.bottomOffset+50)+"px,0)";break;default:t="1s",b.style.opacity="0"}b.offsetHeight,b.style.webkitTransitionDuration=t,b.style.opacity="1",b.style.webkitTransform="translate3d(0,0,0)",b.addEventListener("webkitTransitionEnd",s,!1),_=setTimeout(a,$.lifespan)}function o(e){k&&!b&&(w=e,n())}function a(){if(clearInterval(x),clearTimeout(_),_=null,b){var t=0,n=0,i="1",o="0";switch($.closeButton&&b.removeEventListener("click",r,!1),!d&&m>=6&&window.removeEventListener("orientationchange",u,!1),5>m&&(t=d?e.scrollY-S:e.scrollY+e.innerHeight-S,n=d?e.scrollX-T:e.scrollX+Math.round((e.innerWidth-b.offsetWidth)/2)-T),b.style.webkitTransitionProperty="-webkit-transform,opacity",$.animationOut){case"drop":d?(o="0.4s",i="0",t+=50):(o="0.6s",t+=b.offsetHeight+$.bottomOffset+50);break;case"bubble":d?(o="0.8s",t-=b.offsetHeight+$.bottomOffset+50):(o="0.4s",i="0",t-=50);break;default:o="0.8s",i="0"}b.addEventListener("webkitTransitionEnd",s,!1),b.style.opacity=i,b.style.webkitTransitionDuration=o,b.style.webkitTransform="translate3d("+n+"px,"+t+"px,0)"}}function r(){e.sessionStorage.setItem("addToHomeSession","1"),v=!0,a()}function s(){return b.removeEventListener("webkitTransitionEnd",s,!1),b.style.webkitTransitionProperty="-webkit-transform",b.style.webkitTransitionDuration="0.2s",_?(5>m&&_&&(x=setInterval(l,$.iterations)),void 0):(b.parentNode.removeChild(b),b=null,void 0)}function l(){var t=new WebKitCSSMatrix(e.getComputedStyle(b,null).webkitTransform),n=d?e.scrollY-S:e.scrollY+e.innerHeight-S,i=d?e.scrollX-T:e.scrollX+Math.round((e.innerWidth-b.offsetWidth)/2)-T;(n!=t.m42||i!=t.m41)&&(b.style.webkitTransform="translate3d("+i+"px,"+n+"px,0)")}function c(){e.localStorage.removeItem("addToHome"),e.sessionStorage.removeItem("addToHomeSession")}function u(){b.style.marginLeft=-Math.round(b.offsetWidth/2)-(e.orientation%180&&m>=6?40:0)+"px"}var d,h,p,f,m,g,v,y,b,w,x,_,C=e.navigator,k="platform"in C&&/iphone|ipod|ipad/gi.test(C.platform),T=0,S=0,E=0,$={autostart:!0,returningVisitor:!1,animationIn:"drop",animationOut:"fade",startDelay:2e3,lifespan:15e3,bottomOffset:14,expire:0,message:"",touchIcon:!1,arrow:!0,hookOnLoad:!0,closeButton:!0,iterations:100},I={ar:'<span dir="rtl">قم بتثبيت هذا التطبيق على <span dir="ltr">%device:</span>انقر<span dir="ltr">%icon</span> ،<strong>ثم اضفه الى الشاشة الرئيسية.</strong></span>',ca_es:"Per instal·lar aquesta aplicació al vostre %device premeu %icon i llavors <strong>Afegir a pantalla d'inici</strong>.",cs_cz:"Pro instalaci aplikace na Váš %device, stiskněte %icon a v nabídce <strong>Přidat na plochu</strong>.",da_dk:"Tilføj denne side til din %device: tryk på %icon og derefter <strong>Føj til hjemmeskærm</strong>.",de_de:"Installieren Sie diese App auf Ihrem %device: %icon antippen und dann <strong>Zum Home-Bildschirm</strong>.",el_gr:"Εγκαταστήσετε αυτήν την Εφαρμογή στήν συσκευή σας %device: %icon μετά πατάτε <strong>Προσθήκη σε Αφετηρία</strong>.",en_us:"Install this web app on your %device: tap %icon and then <strong>Add to Home Screen</strong>.",es_es:"Para instalar esta app en su %device, pulse %icon y seleccione <strong>Añadir a pantalla de inicio</strong>.",fi_fi:"Asenna tämä web-sovellus laitteeseesi %device: paina %icon ja sen jälkeen valitse <strong>Lisää Koti-valikkoon</strong>.",fr_fr:"Ajoutez cette application sur votre %device en cliquant sur %icon, puis <strong>Ajouter à l'écran d'accueil</strong>.",he_il:'<span dir="rtl">התקן אפליקציה זו על ה-%device שלך: הקש %icon ואז <strong>הוסף למסך הבית</strong>.</span>',hr_hr:"Instaliraj ovu aplikaciju na svoj %device: klikni na %icon i odaberi <strong>Dodaj u početni zaslon</strong>.",hu_hu:"Telepítse ezt a web-alkalmazást az Ön %device-jára: nyomjon a %icon-ra majd a <strong>Főképernyőhöz adás</strong> gombra.",it_it:"Installa questa applicazione sul tuo %device: premi su %icon e poi <strong>Aggiungi a Home</strong>.",ja_jp:"このウェブアプリをあなたの%deviceにインストールするには%iconをタップして<strong>ホーム画面に追加</strong>を選んでください。",ko_kr:'%device에 웹앱을 설치하려면 %icon을 터치 후 "홈화면에 추가"를 선택하세요',nb_no:"Installer denne appen på din %device: trykk på %icon og deretter <strong>Legg til på Hjem-skjerm</strong>",nl_nl:"Installeer deze webapp op uw %device: tik %icon en dan <strong>Voeg toe aan beginscherm</strong>.",pl_pl:"Aby zainstalować tę aplikacje na %device: naciśnij %icon a następnie <strong>Dodaj jako ikonę</strong>.",pt_br:"Instale este aplicativo em seu %device: aperte %icon e selecione <strong>Adicionar à Tela Inicio</strong>.",pt_pt:"Para instalar esta aplicação no seu %device, prima o %icon e depois o <strong>Adicionar ao ecrã principal</strong>.",ru_ru:"Установите это веб-приложение на ваш %device: нажмите %icon, затем <strong>Добавить в «Домой»</strong>.",sv_se:"Lägg till denna webbapplikation på din %device: tryck på %icon och därefter <strong>Lägg till på hemskärmen</strong>.",th_th:"ติดตั้งเว็บแอพฯ นี้บน %device ของคุณ: แตะ %icon และ <strong>เพิ่มที่หน้าจอโฮม</strong>",tr_tr:"Bu uygulamayı %device'a eklemek için %icon simgesine sonrasında <strong>Ana Ekrana Ekle</strong> düğmesine basın.",uk_ua:"Встановіть цей веб сайт на Ваш %device: натисніть %icon, а потім <strong>На початковий екран</strong>.",zh_cn:"您可以将此应用程式安装到您的 %device 上。请按 %icon 然后点选<strong>添加至主屏幕</strong>。",zh_tw:"您可以將此應用程式安裝到您的 %device 上。請按 %icon 然後點選<strong>加入主畫面螢幕</strong>。"};return t(),{show:o,close:a,reset:c}}(window);