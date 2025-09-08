<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catcha - AI chatbot</h2>
    </x-slot>

    <div class="py-6" x-data="chatApp()" x-init="loadConversations()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded flex h-[70vh]">
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
                            <button type="button" @click="switchConversation(c.id)"
                                :class="['w-full text-left px-3 py-2 hover:bg-indigo-50 focus:outline-none focus:bg-indigo-50 border-b truncate', c.id===conversationId ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700']"
                                x-text="truncate(c.title||'Untitled')"></button>
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
                                <button type="button" @click="switchConversation(c.id); mobileSidebar=false"
                                    :class="['w-full text-left px-3 py-2 hover:bg-indigo-50 focus:outline-none focus:bg-indigo-50 border-b truncate', c.id===conversationId ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700']"
                                    x-text="truncate(c.title||'Untitled')"></button>
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
                            <div class="font-semibold">Session</div>
                            <template x-if="conversationId">
                                <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-500">#<span x-text="conversationId"></span></span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="clearChat()" class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300">Clear</button>
                            <span x-text="status" class="text-xs text-gray-500"></span>
                        </div>
                    </div>
                    <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4 text-sm">
                        <template x-for="(m,i) in messages" :key="i">
                            <div :class="m.role==='user' ? 'text-right' : 'text-left'">
                                <div :class="m.role==='user' ? 'inline-block bg-indigo-600 text-white' : 'inline-block bg-gray-100'" class="px-3 py-2 rounded max-w-[80%] whitespace-pre-wrap" x-text="m.content"></div>
                            </div>
                        </template>
                        <template x-if="loading">
                            <div class="text-left">
                                <div class="inline-block bg-gray-100 px-3 py-2 rounded animate-pulse">Thinking…</div>
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
            </div>
        </div>
    </div>

    <script>
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
                            body: JSON.stringify({question, history, save:this.saveConversation, conversation_id:this.conversationId})
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
                clearChat(){ this.messages=[]; }
            }
        }
    </script>
</x-app-layout>