/* CABINET ACLOG — comportements du site public (repris de la maquette cabinet-aclog_accueil_dynamique_4.html) */
(function(){
 var RM=window.matchMedia('(prefers-reduced-motion:reduce)').matches;
 var B=document.body,WA=B.getAttribute('data-wa')||'242068287915',MAIL=B.getAttribute('data-mail')||'contact@cabinet-aclog.com';

 /* ===== Catalogue : onglets par filière + recherche ===== */
 var ct=document.getElementById('ctabs'),cg=document.getElementById('cgrid'),cq=document.getElementById('cq');
 if(ct&&cg&&cq){
  var cur=ct.getAttribute('data-cur')||'all',cards=[].slice.call(cg.querySelectorAll('.fc')),nores=cg.querySelector('.nores');
  function draw(){
   var q=(cq.value||'').toLowerCase().trim(),n=0;
   cards.forEach(function(c){
    var ok=q?(c.getAttribute('data-s')||'').indexOf(q)>-1:(cur==='all'||c.getAttribute('data-g')===String(cur));
    c.style.display=ok?'':'none';
    if(ok){n++;c.style.animation='none';void c.offsetWidth;c.style.animation=''}
   });
   if(nores)nores.style.display=n?'none':'';
   ct.querySelectorAll('button').forEach(function(b){b.classList.toggle('on',q?false:String(b.getAttribute('data-g'))===String(cur))});
   var cr=document.querySelector('.cres');if(cr)cr.innerHTML=n?'<b>'+n+'</b> formation'+(n>1?'s':'')+' affichée'+(n>1?'s':''):'';
  }
  ct.addEventListener('click',function(e){var b=e.target.closest('button');if(!b)return;cur=b.getAttribute('data-g');cq.value='';draw()});
  cq.addEventListener('input',draw);
  var srch=document.querySelector('.srch');if(srch){var cr=document.createElement('p');cr.className='cres';srch.after(cr)}
  draw();
 }

 /* ===== Nos services : En ligne / En présentiel ===== */
 var T=[{i:'💻',t:'Formation en ligne',d:'Suivez vos cours à distance, depuis où vous voulez, avec l\'appui de formateurs experts du secteur.',l:['Cours dispensés par des experts du secteur','Apprenez à votre rythme','Accompagnement individualisé']},{i:'🏫',t:'Formation en présentiel',d:'Retrouvez nos formateurs dans nos salles de formation à Brazzaville pour des échanges directs et des cas pratiques.',l:['Salles de formation à Brazzaville','Échanges directs avec les formateurs','Ateliers et mises en situation']}];
 var pane=document.getElementById('svpane');
 function showT(k){var x=T[k];pane.innerHTML='<div class="big">'+x.i+'</div><div><h4>'+x.t+'</h4><p>'+x.d+'</p><ul>'+x.l.map(function(y){return '<li>'+y+'</li>'}).join('')+'</ul></div>';pane.style.animation='none';void pane.offsetWidth;pane.style.animation='';document.querySelectorAll('#svtabs button').forEach(function(b){b.classList.toggle('on',+b.getAttribute('data-t')===k)});}
 if(pane){document.querySelectorAll('#svtabs button').forEach(function(b){b.onclick=function(){showT(+b.getAttribute('data-t'))}});showT(0)}

 /* ===== Contact : envoi par WhatsApp ou e-mail ===== */
 var n=document.getElementById('cn'),m=document.getElementById('cm');
 function txt(){return 'Bonjour CABINET ACLOG, je suis '+((n&&n.value)||'…')+'. '+((m&&m.value)||'')}
 var w=document.getElementById('cw'),em=document.getElementById('ce');
 if(w)w.onclick=function(){window.open('https://wa.me/'+WA+'?text='+encodeURIComponent(txt()),'_blank','noopener')};
 if(em)em.onclick=function(){location.href='mailto:'+MAIL+'?subject='+encodeURIComponent('Demande d\'information')+'&body='+encodeURIComponent(txt())};

 /* ===== Annuaire des certifiés : recherche instantanée ===== */
 document.querySelectorAll('.ann-c').forEach(function(box){
  var q=box.querySelector('.ann-q[data-live]'),rows=[].slice.call(box.querySelectorAll('tbody tr[data-s]')),none=box.querySelector('tr.ann-none');
  if(!q)return;
  q.addEventListener('input',function(){var v=q.value.toLowerCase().trim(),k=0;rows.forEach(function(r){var ok=!v||r.getAttribute('data-s').indexOf(v)>-1;r.style.display=ok?'':'none';if(ok)k++});if(none)none.style.display=k?'none':''});
 });

 /* ===================== ÉLÉMENTS DYNAMIQUES ===================== */
 var hdr=document.querySelector('header.site');
 /* 1. barre de progression, en-tête compact, bouton retour en haut */
 var pr=document.createElement('div');pr.id='prog';document.body.appendChild(pr);
 var tt=document.createElement('button');tt.id='totop';tt.type='button';tt.setAttribute('aria-label','Revenir en haut de la page');tt.textContent='↑';document.body.appendChild(tt);
 tt.onclick=function(){window.scrollTo({top:0,behavior:RM?'auto':'smooth'})};
 var tick=false;
 function onScroll(){var h=document.documentElement,max=h.scrollHeight-h.clientHeight,y=h.scrollTop||document.body.scrollTop;
  pr.style.width=(max>0?y/max*100:0)+'%';if(hdr)hdr.classList.toggle('scrolled',y>40);tt.classList.toggle('on',y>600);tick=false}
 window.addEventListener('scroll',function(){if(!tick){tick=true;requestAnimationFrame(onScroll)}},{passive:true});onScroll();

 /* 2. apparition des blocs au défilement (avec décalage en cascade) */
 var SEL=['.pc-t','.pc-l>li','.ann-h','.ann-s>div','.ann-c','.sh','.hm-h','.faq-h','.card','.hf','.hm-feat>*','.hm-why>div','.tl>div','.tl','.step','.dg','.fq','.sc','.band .g4>div','.split>div','.showcase','.feat','.cc2','.dark','.hm-tok','.hm-pil','.cta>*','.chal','.fm','.tabs','.srch','.biz>*','.plan','.ttl'];
 var io=null;
 function prep(root){
  root.querySelectorAll(SEL.join(',')).forEach(function(el){
   if(el.classList.contains('rv')||el.closest('.hero')||el.closest('.chatw'))return;
   el.classList.add('rv');
   if(el.matches('.split>div:first-child,.hm-feat>*:first-child,.biz>*:first-child'))el.classList.add('rv-l');
   if(el.matches('.split>div:last-child,.hm-feat>*:last-child,.biz>*:last-child,.showcase'))el.classList.add('rv-r');
   if(el.matches('.sc,.dg,.hm-tok'))el.classList.add('rv-z');
   var sib=el.parentElement?[].filter.call(el.parentElement.children,function(c){return c.matches(SEL.join(','))}):[];
   var i=sib.indexOf(el);if(i>0)el.style.setProperty('--d',Math.min(i,6)*0.1+'s');
   if(io)io.observe(el);else el.classList.add('in');
  });
 }
 if('IntersectionObserver' in window&&!RM){
  // un bloc plus haut que l'écran (plan de formation) n'atteint jamais 12 % visible : on le révèle dès qu'il occupe un quart de l'écran
  io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting&&(e.intersectionRatio>=.12||e.intersectionRect.height>innerHeight*.25)){e.target.classList.add('in');io.unobserve(e.target)}})},{threshold:[0,.05,.12],rootMargin:'0px 0px -40px 0px'});
  // défilement rapide (ancre, touche Fin) : les blocs déjà dépassés s'affichent aussi
  window.addEventListener('scroll',function(){document.querySelectorAll('.rv:not(.in)').forEach(function(el){if(el.getBoundingClientRect().bottom<0){el.classList.add('in');io.unobserve(el)}})},{passive:true});
 }
 prep(document);

 /* 3. compteurs animés */
 function countUp(el){
  if(el.dataset.done)return;el.dataset.done=1;
  var raw=el.textContent.trim(),m=raw.match(/^([^\d]*)([\d.,]+)(.*)$/);if(!m)return;
  var target=parseFloat(m[2].replace(',','.')),dec=(m[2].split(/[.,]/)[1]||'').length,t0=null,D=1600;
  if(RM){return}
  function step(ts){if(!t0)t0=ts;var u=Math.min(1,(ts-t0)/D),e=1-Math.pow(1-u,3);
   el.textContent=m[1]+(target*e).toFixed(dec).replace('.',dec&&raw.indexOf(',')>-1?',':'.')+m[3];if(u<1)requestAnimationFrame(step);else el.textContent=raw}
  el.textContent=m[1]+(0).toFixed(dec)+m[3];requestAnimationFrame(step);
 }
 var nums=document.querySelectorAll('.sc b,.band b,.hst b,.stt b,.ann-s b');
 if('IntersectionObserver' in window){
  var io2=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){countUp(e.target);io2.unobserve(e.target)}})},{threshold:.6});
  nums.forEach(function(n){io2.observe(n)});
 }

 /* 4. message défilant de la barre du haut */
 var msg=document.querySelector('.top .msg');
 if(msg&&!RM){var MS=[msg.textContent,'Paiement simple par MTN Mobile Money et Airtel Money (+242)','Certificats vérifiables par QR Code',(msg.getAttribute('data-nb')||'')+' formations certifiantes avec certificat','En ligne ou en salle à Brazzaville'],mi=0;
  setInterval(function(){msg.classList.add('out');setTimeout(function(){mi=(mi+1)%MS.length;msg.textContent=MS[mi];msg.classList.remove('out')},450)},4500)}

 /* 5. mot qui s'écrit dans le bandeau d'accroche */
 var kick=document.querySelector('#pg-accueil .kick');
 if(kick&&!RM){var W=['Logistique','Transport','Douane & Transit','Commerce International','Supply Chain'],wi=0,ci=0,del=false;
  kick.innerHTML='Hub Digital Panafricain • Formez-vous en <span class="rot"></span>';var rot=kick.querySelector('.rot');
  (function type(){var w=W[wi];if(!del){ci++;rot.textContent=w.slice(0,ci);if(ci===w.length){del=true;return setTimeout(type,1800)}}
   else{ci--;rot.textContent=w.slice(0,ci);if(ci===0){del=false;wi=(wi+1)%W.length}}setTimeout(type,del?45:85)})();}

 /* 6. certificat et cartes : inclinaison 3D qui suit la souris (écrans avec souris seulement) */
 if(!RM&&window.matchMedia('(hover:hover)').matches){
  var cert=document.querySelector('.hero .cert');
  if(cert){var img=cert.querySelector('img');cert.addEventListener('mousemove',function(e){var r=cert.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
   img.style.transform='perspective(900px) rotateY('+(x*14)+'deg) rotateX('+(-y*10)+'deg) rotate(-2deg) scale(1.03)'});
   cert.addEventListener('mouseleave',function(){img.style.transform=''})}
  document.querySelectorAll('.hf,.sc,.feat,.dg').forEach(function(c){c.classList.add('tilt');
   c.addEventListener('mousemove',function(e){var r=c.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
    c.style.transform='perspective(1000px) rotateY('+(x*6)+'deg) rotateX('+(-y*6)+'deg) translateY(-6px)'});
   c.addEventListener('mouseleave',function(){c.style.transform=''})});
 }

 /* 7. effet d'onde au clic sur les boutons */
 document.addEventListener('pointerdown',function(e){var b=e.target.closest('.btn');if(!b||RM)return;
  var r=b.getBoundingClientRect(),s=Math.max(r.width,r.height),sp=document.createElement('span');sp.className='rip';
  sp.style.cssText='width:'+s+'px;height:'+s+'px;left:'+(e.clientX-r.left-s/2)+'px;top:'+(e.clientY-r.top-s/2)+'px';b.appendChild(sp);setTimeout(function(){sp.remove()},700)});

 /* 8. FAQ / modules : ouverture et fermeture en douceur, un seul élément ouvert à la fois */
 document.querySelectorAll('.fq').forEach(function(d){var s=d.querySelector('summary'),c=d.querySelector('.fa');if(!s||!c)return;
  s.addEventListener('click',function(e){if(RM)return;e.preventDefault();
   if(d.open){c.style.height=c.scrollHeight+'px';requestAnimationFrame(function(){c.style.height='0px'});setTimeout(function(){d.open=false;c.style.height=''},350)}
   else{var grp=d.parentElement;grp.querySelectorAll(':scope>.fq[open]').forEach(function(o){if(o!==d)o.querySelector('summary').click()});
    d.open=true;var h=c.scrollHeight;c.style.height='0px';requestAnimationFrame(function(){c.style.height=h+'px'});setTimeout(function(){c.style.height=''},360)}})});
})();
