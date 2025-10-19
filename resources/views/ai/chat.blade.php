<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catcha - AI Assistant</h2>
    </x-slot>

    <!-- Tabs wrapper -->
    <div class="py-6" x-data="aiTabs()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="bg-white shadow rounded mb-4">
                <div class="border-b px-4">
                    <nav class="flex gap-2" aria-label="AI modes">
                        <button type="button" @click="setTab('chat')"
                            :class="tab==='chat' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="-mb-px inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-8 4h10"/></svg>
                            Chat
                        </button>
                        <button type="button" @click="setTab('consult')"
                            :class="tab==='consult' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="-mb-px inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            Consult
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab: Chat -->
            <div x-show="tab==='chat'" x-cloak class="bg-white shadow rounded flex h-[70vh]" x-data="chatApp()" x-init="loadConversations()">
                <!-- Sidebar -->
                <div class="hidden sm:flex w-56 border-r flex-col" x-show="conversations.length || true">
                    <div class="px-3 py-2 border-b flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600">Conversations</span>
                        <button @click="newConversation()" class="text-xs text-indigo-600 hover:underline">New</button>
                    </div>
                    <div class="flex-1 overflow-y-auto text-xs">
                        <template x-if="!conversations.length">
                            <div class="p-3 text-gray-400">No chats yet.</div>
                        </template>
                        <template x-for="c in conversations" :key="c.id">
                            <div class="group relative">
                                <button type="button" @click="switchConversation(c.id)"
                                    :class="['w-full text-left pr-8 pl-3 py-2 hover:bg-indigo-50 focus:outline-none focus:bg-indigo-50 border-b truncate', c.id===conversationId ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700']"
                                    x-text="truncate(c.title||'Untitled')"></button>
                                <button type="button" @click="deleteConversation(c.id)" title="Delete" class="absolute top-0 right-0 h-full px-2 text-gray-300 hover:text-red-600 opacity-0 group-hover:opacity-100 transition">
                                    &times;
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Mobile sidebar (overlay) -->
                <div class="sm:hidden" x-show="mobileSidebar" x-transition @click.self="mobileSidebar=false" style="display:none;">
                    <div class="fixed inset-0 bg-black/40"></div>
                    <div class="fixed inset-y-0 start-0 w-60 bg-white shadow flex flex-col">
                        <div class="px-3 py-2 border-b flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-600">Conversations</span>
                            <button @click="newConversation(); mobileSidebar=false" class="text-xs text-indigo-600 hover:underline">New</button>
                        </div>
                        <div class="flex-1 overflow-y-auto text-xs">
                            <template x-if="!conversations.length">
                                <div class="p-3 text-gray-400">No chats yet.</div>
                            </template>
                            <template x-for="c in conversations" :key="c.id">
                                <div class="group relative">
                                    <button type="button" @click="switchConversation(c.id); mobileSidebar=false"
                                        :class="['w-full text-left pr-8 pl-3 py-2 hover:bg-indigo-50 focus:outline-none focus:bg-indigo-50 border-b truncate', c.id===conversationId ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700']"
                                        x-text="truncate(c.title||'Untitled')"></button>
                                    <button type="button" @click="deleteConversation(c.id); mobileSidebar=false" title="Delete" class="absolute top-0 right-0 h-full px-2 text-gray-300 hover:text-red-600 opacity-0 group-hover:opacity-100 transition">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button @click="mobileSidebar=false" class="m-3 px-3 py-1 rounded bg-gray-200 text-xs">Close</button>
                    </div>
                </div>

                <!-- Chat area -->
                <div class="flex-1 flex flex-col">
                    <div class="flex items-center justify-between border-b px-4 py-2 text-sm">
                        <div class="flex items-center gap-2">
                            <button class="sm:hidden text-gray-500" @click="mobileSidebar=true" title="Conversations">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M3 12h18M3 19h18"/></svg>
                            </button>
                            <div class="flex items-center gap-2 font-semibold">
                                <img src="{{ asset('logo/catcha_logo.png') }}" alt="Catcha AI" class="h-6 w-6 rounded-sm shadow-sm" />
                                <span>Session</span>
                            </div>
                            <template x-if="conversationId">
                                <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-500">#<span x-text="conversationId"></span></span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <select x-model="provider" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white cursor-pointer">
                                <option value="openai">OpenAI</option>
                                <option value="gemini">Gemini</option>
                            </select>
                            <button @click="confirmClear()" class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300">Clear</button>
                            <span x-text="status" class="text-xs text-gray-500"></span>
                        </div>
                    </div>
                    <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4 text-sm">
                        <template x-for="(m,i) in messages" :key="i">
                            <div class="flex" :class="m.role==='user' ? 'justify-end' : 'justify-start'">
                                <div class="flex items-start gap-2 max-w-[80%]" :class="m.role==='user' ? 'flex-row-reverse text-right' : 'flex-row'">
                                    <template x-if="m.role==='assistant'">
                                        <img src="{{ asset('logo/catcha_logo.png') }}" alt="AI" class="h-6 w-6 rounded-sm shadow-sm mt-0.5" />
                                    </template>
                                    <template x-if="m.role==='user'">
                                        <div class="h-6 w-6 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-[10px] font-semibold mt-0.5 select-none">You</div>
                                    </template>
                                    <div :class="m.role==='user' ? 'bg-indigo-600 text-white' : 'bg-gray-100'" class="px-3 py-2 rounded whitespace-pre-wrap break-words" x-text="m.content"></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="loading">
                            <div class="flex items-start gap-2">
                                <img src="{{ asset('logo/catcha_logo.png') }}" alt="AI" class="h-6 w-6 rounded-sm shadow-sm mt-0.5" />
                                <div class="bg-gray-100 px-3 py-2 rounded animate-pulse">Thinking…</div>
                            </div>
                        </template>
                    </div>
                    <form @submit.prevent="send()" class="border-t p-3 space-y-2">
                        <textarea x-model="input" rows="2" placeholder="Ask a question about fishing, weather, sustainability..." class="w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required></textarea>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3 text-xs text-gray-500 flex-wrap">
                                <label class="flex items-center gap-1">
                                    <input type="checkbox" x-model="saveConversation" class="rounded border-gray-300" /> Save
                                </label>
                                <template x-if="conversationId">
                                    <a :href="`/ai/conversations/${conversationId}`" class="text-indigo-600 hover:underline">View Saved</a>
                                </template>
                            </div>
                            <button :disabled="loading" type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded text-sm">Send</button>
                        </div>
                    </form>
                </div>
                <!-- Confirmation Modal (inside chatApp scope) -->
                <div x-cloak x-show="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none;">
                    <div class="absolute inset-0 bg-black/40" @click="cancelConfirm()"></div>
                    <div class="relative bg-white rounded shadow-lg p-5 w-full max-w-sm border" @keydown.escape.window="cancelConfirm()">
                        <div class="text-sm mb-4" x-text="confirmMessage"></div>
                        <div class="flex justify-end gap-2 text-xs">
                            <button @click="cancelConfirm()" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
                            <button @click="doConfirm()" class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Consult -->
            <div x-show="tab==='consult'" x-cloak class="max-w-4xl mx-auto">
                <div class="bg-white shadow rounded p-6">
                    <form id="aiForm" class="space-y-4">
                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700">AI Provider</label>
                            <select id="provider" name="provider" class="mt-1 w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="openai">OpenAI (GPT-4o)</option>
                                <option value="gemini">Gemini (Google AI)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select which AI service to use for this consultation.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Your Question</label>
                            <textarea id="question" class="mt-1 w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" rows="4" maxlength="2000" placeholder="Ask about fishing conditions, sustainable practices, or interpreting forecast data..." required></textarea>
                        </div>
                        <div class="flex items-center gap-4">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm">Ask AI</button>
                            <span id="status" class="text-sm text-gray-500"></span>
                        </div>
                    </form>
                    <div id="answerBox" class="mt-6 hidden">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Answer</h3>
                        <div id="answer" class="prose max-w-none text-sm whitespace-pre-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function aiTabs(){
            return {
                tab: new URLSearchParams(window.location.search).get('tab') || 'chat',
                init(){ this.wireConsultForm(); },
                setTab(t){
                    this.tab = t;
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', t);
                    history.replaceState({}, '', url);
                    this.$nextTick?.(() => this.wireConsultForm());
                    setTimeout(() => this.wireConsultForm(), 0);
                },
                wireConsultForm(){
                    const form = document.getElementById('aiForm');
                    if(!form || form.dataset.bound==='1'){ return; }
                    const qEl = document.getElementById('question');
                    const providerEl = document.getElementById('provider');
                    const statusEl = document.getElementById('status');
                    const answerBox = document.getElementById('answerBox');
                    const answerEl = document.getElementById('answer');
                    form.addEventListener('submit', async (e)=>{
                        e.preventDefault();
                        const question = qEl.value.trim();
                        const provider = providerEl?.value || 'openai';
                        if(!question){return;}
                        statusEl.textContent = 'Thinking...';
                        answerBox.classList.add('hidden');
                        try {
                            const resp = await fetch("{{ route('ai.consult') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({question, provider})
                            });
                            const data = await resp.json();
                            if(!resp.ok || data.error){
                                statusEl.textContent = data.error || 'Error';
                                return;
                            }
                            answerEl.textContent = data.answer;
                            answerBox.classList.remove('hidden');
                            statusEl.textContent = 'Done ('+data.provider+')';
                        } catch(err){
                            console.error(err);
                            statusEl.textContent = 'Request failed';
                        }
                    });
                    form.dataset.bound='1';
                }
            }
        }

        function chatApp(){
            return {
                messages: [],
                input: '',
                loading: false,
                status: '',
                compact: false,
                saveConversation: true,
                conversationId: null,
                conversations: [],
                mobileSidebar: false,
                provider: 'openai',
                async loadConversations(){
                    try {
                        const resp = await fetch('{{ route('ai.conversations.index') }}', {headers:{'Accept':'application/json'}});
                        if(resp.ok){ this.conversations = await resp.json(); }
                    } catch(e){ console.warn('loadConversations failed', e); }
                },
                async switchConversation(id){
                    if(this.loading) return;
                    if(id === this.conversationId) return;
                    this.loading=true; this.status='Loading...';
                    try {
                        const resp = await fetch(`/ai/conversations/${id}`, {headers:{'Accept':'application/json'}});
                        if(resp.ok){
                            const data = await resp.json();
                            this.conversationId = data.id;
                            this.messages = data.messages;
                            this.status='';
                            this.$nextTick(()=>{const el=document.getElementById('messages'); el.scrollTop=el.scrollHeight;});
                        }
                    } finally { this.loading=false; }
                },
                newConversation(){
                    this.conversationId=null; this.messages=[]; this.input='';
                },
                truncate(str){ return str.length>24 ? str.slice(0,21)+'…' : str; },
                async send(){
                    const question = this.input.trim();
                    if(!question) return;
                    this.messages.push({role:'user', content:question});
                    this.input='';
                    this.loading=true; this.status='Sending...';
                    const history = this.messages.filter(m=>m.role!=='system').slice(-10); // last 10
                    try {
                        const resp = await fetch("{{ route('ai.consult') }}", {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                            body: JSON.stringify({question, history, save:this.saveConversation, conversation_id:this.conversationId, provider:this.provider})
                        });
                        const data = await resp.json();
                        if(!resp.ok){
                            this.messages.push({role:'assistant', content: 'Service error. Please retry.'});
                        } else if(data.notice === 'out_of_scope'){
                            this.messages.push({role:'assistant', content: data.answer});
                        } else if(data.error){
                            this.messages.push({role:'assistant', content: 'Error: '+ (data.error||'Unknown')});
                        } else {
                            this.messages.push({role:'assistant', content:data.answer});
                            if(data.conversation_id){
                                const wasNew = !this.conversationId;
                                this.conversationId = data.conversation_id;
                                if(wasNew){ this.loadConversations(); }
                            }
                        }
                        this.status='';
                    } catch(e){
                        console.error(e);
                        this.messages.push({role:'assistant', content:'Network error'});
                        this.status='Error';
                    } finally {
                        this.loading=false;
                        this.$nextTick(()=>{
                            const el=document.getElementById('messages');
                            el.scrollTop=el.scrollHeight;
                        });
                    }
                },
                clearChat(){ this.messages=[]; },
                // Confirmation modal state
                showConfirm: false,
                confirmMessage: '',
                confirmProceed: null,
                openConfirm(msg, proceed){ this.confirmMessage = msg; this.confirmProceed = proceed; this.showConfirm = true; },
                cancelConfirm(){ this.showConfirm=false; this.confirmMessage=''; this.confirmProceed=null; },
                doConfirm(){ if(this.confirmProceed){ const fn=this.confirmProceed; this.cancelConfirm(); fn(); } },
                confirmClear(){
                    if(!this.messages.length){ return; }
                    this.openConfirm('Clear current unsent chat history? (Saved messages already stored will remain)', ()=> this.clearChat());
                },
                async deleteConversation(id){
                    this.openConfirm('Delete this conversation permanently?', async () => {
                        try {
                            const resp = await fetch(`/ai/conversations/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json'}});
                            if(resp.ok){
                                // capture index before removal
                                const idxBefore = this.conversations.findIndex(c=>c.id===id);
                                this.conversations = this.conversations.filter(c=>c.id!==id);
                                if(this.conversationId === id){
                                    if(this.conversations.length){
                                        const nextIdx = idxBefore >= this.conversations.length ? this.conversations.length - 1 : idxBefore; // pick neighbor
                                        const next = this.conversations[nextIdx];
                                        // set conversationId early to prevent flicker, then load messages
                                        this.conversationId = next.id;
                                        await this.switchConversation(next.id);
                                    } else {
                                        this.newConversation();
                                    }
                                }
                                this.status='Deleted'; setTimeout(()=>{ if(this.status==='Deleted'){ this.status=''; } }, 1500);
                            } else {
                                alert('Failed to delete');
                            }
                        } catch(e){ alert('Network error'); }
                    });
                }
            }
        }
    </script>
</x-app-layout>