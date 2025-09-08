const VERSION = 'catcha-offline-v2';
const FORM_QUEUE = 'catcha-form-queue';
const CORE_ASSETS = [
  '/',
  '/favicon.ico',
  '/offline.html',
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(VERSION).then(c => c.addAll(CORE_ASSETS)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', e => {
  e.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter(k => k !== VERSION).map(k => caches.delete(k)));
    await self.clients.claim();
  })());
});

async function queueRequest(data){
  const db = await openDB();
  const tx = db.transaction(FORM_QUEUE, 'readwrite');
  tx.store.add({ id: crypto.randomUUID(), data, ts: Date.now() });
  await tx.done;
}

async function openDB(){
  if(!('indexedDB' in self)) throw new Error('No IDB');
  return await new Promise((resolve, reject) => {
    const req = indexedDB.open('catcha-offline', 1);
    req.onupgradeneeded = () => {
      const db = req.result;
      if(!db.objectStoreNames.contains(FORM_QUEUE)){
        db.createObjectStore(FORM_QUEUE, { keyPath: 'id' });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  // Handle POST catch create (queue offline)
  if(request.method === 'POST' && url.pathname === '/catches'){
    event.respondWith((async () => {
      try {
        const clone = request.clone();
        const body = await clone.formData();
        const plain = Object.fromEntries([...body.entries()]);
        if(!self.navigator || !self.navigator.onLine){
          await queueRequest(plain);
          return new Response(JSON.stringify({ status:'queued-offline'}), { headers: { 'Content-Type':'application/json' }, status: 202 });
        }
        return await fetch(request, { headers: { 'X-Offline-Sync':'1' } });
      } catch(err){
        try {
          const clone = request.clone();
          const body = await clone.formData();
          const plain = Object.fromEntries([...body.entries()]);
          await queueRequest(plain);
        } catch(_e) {}
        return new Response(JSON.stringify({ status:'queued-offline'}), { headers: { 'Content-Type':'application/json' }, status: 202 });
      }
    })());
    return;
  }
  // Navigation requests: offline-first fallback
  if(request.mode === 'navigate'){
    event.respondWith((async () => {
      try {
        const net = await fetch(request);
        const cache = await caches.open(VERSION);
        cache.put(request, net.clone());
        return net;
      } catch(e){
        const cache = await caches.open(VERSION);
        const cached = await cache.match(request);
        if(cached){ return cached; }
        const offline = await cache.match('/offline.html');
        return offline || new Response('<h1>Offline</h1>', { headers: { 'Content-Type':'text/html' } });
      }
    })());
    return;
  }
  // Other GET: try cache, then network, then fallback
  if(request.method === 'GET' && (request.destination === 'script' || request.destination === 'style' || request.destination === 'image')){
    event.respondWith((async () => {
      const cache = await caches.open(VERSION);
      const cached = await cache.match(request);
      if(cached){ return cached; }
      try {
        const net = await fetch(request);
        cache.put(request, net.clone());
        return net;
      } catch(e){
        return cached || Response.error();
      }
    })());
  }
});

self.addEventListener('sync', event => {
  if(event.tag === 'sync-catches'){
    event.waitUntil(flushQueue());
  }
});

async function flushQueue(){
  const db = await openDB();
  const tx = db.transaction(FORM_QUEUE, 'readwrite');
  const store = tx.store;
  const all = await store.getAll();
  for(const item of all){
    try {
      const form = new FormData();
      Object.entries(item.data).forEach(([k,v]) => form.append(k,v));
      const res = await fetch('/catches', { method:'POST', body: form, headers: { 'X-Offline-Sync':'1' } });
      if(res.ok){ store.delete(item.id); }
    } catch(e){ /* keep item */ }
  }
  await tx.done;
}

self.addEventListener('message', e => {
  if(e.data === 'flushQueue'){
    flushQueue();
  }
});

self.addEventListener('online', () => { flushQueue(); });
