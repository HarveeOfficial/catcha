<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">AI Consultation</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">
                <form id="aiForm" class="space-y-4">
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

    <script>
        const form = document.getElementById('aiForm');
        const qEl = document.getElementById('question');
        const statusEl = document.getElementById('status');
        const answerBox = document.getElementById('answerBox');
        const answerEl = document.getElementById('answer');

        form.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const question = qEl.value.trim();
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
                    body: JSON.stringify({question})
                });
                const data = await resp.json();
                if(!resp.ok || data.error){
                    statusEl.textContent = data.error || 'Error';
                    return;
                }
                answerEl.textContent = data.answer;
                answerBox.classList.remove('hidden');
                statusEl.textContent = 'Done';
            } catch(err){
                console.error(err);
                statusEl.textContent = 'Request failed';
            }
        });
    </script>
</x-app-layout>
