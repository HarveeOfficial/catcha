<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catcha - AI chatbot</h2>
    </x-slot>

    <div class="py-6" x-data="chatApp()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded flex flex-col h-[70vh]">
                <div class="flex items-center justify-between border-b px-4 py-2 text-sm">
                    <div class="font-semibold">Session</div>
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
                            {{-- <label class="flex items-center gap-1">
                                <input type="checkbox" x-model="compact" class="rounded border-gray-300" /> Compact
                            </label> --}}
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
                            if(data.conversation_id){ this.conversationId = data.conversation_id; }
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