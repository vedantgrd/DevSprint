/* DevSprint — Shared Script v2 */
(function() {
    'use strict';

    // ── Custom Cursor ──
    const cursor = document.getElementById('cursor');
    const cursorRing = document.getElementById('cursorRing');
    if (cursor && cursorRing) {
        let mx = 0, my = 0, rx = 0, ry = 0;
        document.addEventListener('mousemove', e => {
            mx = e.clientX; my = e.clientY;
            cursor.style.left = (mx - 6) + 'px';
            cursor.style.top = (my - 6) + 'px';
        });
        (function animRing() {
            rx += (mx - rx) * 0.12;
            ry += (my - ry) * 0.12;
            cursorRing.style.left = (rx - 18) + 'px';
            cursorRing.style.top = (ry - 18) + 'px';
            requestAnimationFrame(animRing);
        })();
        document.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('mouseenter', () => { cursorRing.style.width='60px';cursorRing.style.height='60px';cursor.style.transform='scale(0.4)'; });
            el.addEventListener('mouseleave', () => { cursorRing.style.width='36px';cursorRing.style.height='36px';cursor.style.transform='scale(1)'; });
        });
    }

    // ── Three.js Cosmos Background ──
    if (typeof THREE !== 'undefined') {
        (function initThree() {
            try {
                const canvas = document.getElementById('cosmos-canvas');
                if (!canvas) return;
                const renderer = new THREE.WebGLRenderer({ canvas, antialias:true, alpha:true });
                renderer.setSize(window.innerWidth, window.innerHeight);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 3000);
                camera.position.z = 600;

                // Stars
                const starCount = 7000;
                const pos = new Float32Array(starCount*3);
                const col = new Float32Array(starCount*3);
                const sz  = new Float32Array(starCount);
                for (let i=0;i<starCount;i++) {
                    pos[i*3]   = (Math.random()-0.5)*3500;
                    pos[i*3+1] = (Math.random()-0.5)*3500;
                    pos[i*3+2] = (Math.random()-0.5)*2500;
                    const t = Math.random();
                    if (t<0.6){ col[i*3]=0.9;col[i*3+1]=0.93;col[i*3+2]=1.0; }
                    else if(t<0.8){ col[i*3]=0.3;col[i*3+1]=0.76;col[i*3+2]=0.97; }
                    else { col[i*3]=0.48;col[i*3+1]=0.30;col[i*3+2]=1.0; }
                    sz[i] = Math.random()*2.5+0.5;
                }
                const geo = new THREE.BufferGeometry();
                geo.setAttribute('position',new THREE.BufferAttribute(pos,3));
                geo.setAttribute('color',new THREE.BufferAttribute(col,3));
                const stars = new THREE.Points(geo, new THREE.PointsMaterial({size:1.5,vertexColors:true,transparent:true,opacity:0.8,sizeAttenuation:true}));
                scene.add(stars);

                // Rings
                function makeRing(r,color,rx,ry) {
                    const m = new THREE.Mesh(
                        new THREE.TorusGeometry(r,0.6,16,200),
                        new THREE.MeshBasicMaterial({color,transparent:true,opacity:0.06})
                    );
                    m.rotation.x=rx; m.rotation.y=ry;
                    scene.add(m); return m;
                }
                const r1=makeRing(320,0x4fc3f7,1.2,0.3);
                const r2=makeRing(480,0x7c4dff,0.5,1.0);

                // Shooting stars
                const shoots=[];
                function spawnShoot() {
                    const g=new THREE.BufferGeometry();
                    const x=(Math.random()-0.5)*1200,y=200+Math.random()*300,z=-100+Math.random()*200;
                    g.setFromPoints([new THREE.Vector3(x,y,z),new THREE.Vector3(x-80,y-20,z)]);
                    const l=new THREE.Line(g,new THREE.LineBasicMaterial({color:0x00e5ff,transparent:true,opacity:0.9}));
                    scene.add(l);
                    shoots.push({l,vx:-(3+Math.random()*3),vy:-(0.6+Math.random())});
                    setTimeout(()=>{scene.remove(l);const idx=shoots.findIndex(s=>s.l===l);if(idx>-1)shoots.splice(idx,1);},1200);
                }
                setInterval(spawnShoot,4000);

                let mouseX=0,mouseY=0,scrollY=0;
                document.addEventListener('mousemove',e=>{mouseX=(e.clientX/window.innerWidth-0.5)*2;mouseY=(e.clientY/window.innerHeight-0.5)*2;});
                window.addEventListener('scroll',()=>{ scrollY=window.scrollY; });

                let t=0;
                (function animate(){
                    requestAnimationFrame(animate);
                    t+=0.0008;
                    stars.rotation.y=t*0.025+mouseX*0.04;
                    stars.rotation.x=t*0.01+mouseY*0.02;
                    camera.position.y=-scrollY*0.1;
                    r1.rotation.z+=0.001; r2.rotation.z-=0.0007;
                    shoots.forEach(s=>{s.l.position.x+=s.vx;s.l.position.y+=s.vy;s.l.material.opacity-=0.01;});
                    renderer.render(scene,camera);
                })();

                window.addEventListener('resize',()=>{
                    camera.aspect=window.innerWidth/window.innerHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(window.innerWidth,window.innerHeight);
                });
            } catch (e) {
                // Keep core UI interactions working even if 3D background fails.
            }
        })();
    }

    // ── Nav scroll ──
    const mainNav = document.getElementById('main-nav');
    if (mainNav) {
        window.addEventListener('scroll',()=>mainNav.classList.toggle('scrolled',window.scrollY>40));
    }

    // ── Mobile nav ──
    const navToggle = document.getElementById('nav-toggle') || document.querySelector('.nav-toggle');
    const navMenu   = document.getElementById('nav-menu') || document.querySelector('.nav-menu');
    if (navToggle && navMenu && !navToggle.dataset.navBound) {
        navToggle.dataset.navBound = '1';
        navToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('active');
        });
        // Close menu when a nav link is clicked
        navMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
            });
        });
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !navToggle.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }

    // ── Intersection Observer (reveal) ──
    const io = new IntersectionObserver(entries=>{
        entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('visible'); });
    },{threshold:0.1,rootMargin:'0px 0px -60px 0px'});
    document.querySelectorAll('.reveal,.reveal-left,.reveal-right').forEach(el=>io.observe(el));

    // ── Count-up ──
    const countIO = new IntersectionObserver(entries=>{
        entries.forEach(({isIntersecting,target})=>{
            if(!isIntersecting) return;
            const end=parseInt(target.dataset.target);
            const prefix=target.dataset.prefix||'';
            const suffix=target.dataset.suffix||'';
            let cur=0, step=end/55;
            const iv=setInterval(()=>{
                cur=Math.min(cur+step,end);
                target.textContent=prefix+Math.floor(cur)+suffix;
                if(cur>=end) clearInterval(iv);
            },22);
            countIO.unobserve(target);
        });
    },{threshold:0.5});
    document.querySelectorAll('.stat-number[data-target]').forEach(el=>countIO.observe(el));

    // ── 3D card tilt ──
    const card3d=document.getElementById('event3d');
    const inner=document.getElementById('eventCardInner');
    if(card3d&&inner){
        card3d.addEventListener('mousemove',e=>{
            const r=card3d.getBoundingClientRect();
            const x=(e.clientX-r.left)/r.width-0.5;
            const y=(e.clientY-r.top)/r.height-0.5;
            inner.style.transform=`rotateY(${x*8}deg) rotateX(${-y*6}deg)`;
        });
        card3d.addEventListener('mouseleave',()=>{inner.style.transform='rotateY(0) rotateX(0)';});
    }
})();
