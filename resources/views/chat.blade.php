<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lara-Recall | AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Custom scrollbar for a cleaner look */
        #chat-container::-webkit-scrollbar {
            width: 4px;
        }

        #chat-container::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-slate-100 h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl bg-white shadow-2xl rounded-3xl overflow-hidden flex flex-col h-[90vh]"
        x-data="chatHandler()">
        <!-- Header -->
        <div class="bg-blue-600 p-5 text-white flex justify-between items-center shadow-lg">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Lara-Recall</h1>
                <p class="text-xs text-blue-100 opacity-80">Local Knowledge Engine</p>
            </div>
            <div class="text-[10px] bg-blue-500 px-2 py-1 rounded-full uppercase font-bold border border-blue-400">
                Ollama Active
            </div>
        </div>

        <!-- Chat History -->
        <div class="flex-1 p-6 overflow-y-auto bg-slate-50 space-y-4" id="chat-container">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none' : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-tl-none'"
                        class="inline-block p-3 px-4 max-w-[85%] text-sm leading-relaxed shadow-sm whitespace-pre-wrap"
                        x-text="msg.content">
                    </div>
                </div>
            </template>

            <!-- Thinking Indicator -->
            <div x-show="isThinking" class="flex justify-start">
                <div class="bg-white border border-gray-200 p-3 rounded-2xl rounded-tl-none shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce"></div>
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="p-4 bg-white border-t">
            <form @submit.prevent="sendMessage()" class="relative flex items-center">
                <input type="text" x-model="userInput" placeholder="Ask your documents..."
                    class="w-full p-3 pr-12 bg-slate-100 border-none rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                <button type="submit"
                    class="absolute right-2 p-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50"
                    :disabled="isThinking || !userInput.trim()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        function chatHandler() {
            return {
                userInput: '',
                messages: [
                    { role: 'ai', content: 'Hello! I am ready to help you with your documents. What would you like to know?' }
                ],
                isThinking: false,

                // Helper to always keep the chat at the bottom
                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('chat-container');
                        container.scrollTo({
                            top: container.scrollHeight,
                            behavior: 'smooth'
                        });
                    });
                },

                async sendMessage() {
                    if (!this.userInput.trim()) return;

                    const question = this.userInput;
                    this.messages.push({ role: 'user', content: question });
                    this.userInput = '';
                    this.isThinking = true;

                    this.scrollToBottom();

                    // Placeholder for AI response
                    const aiMsgIndex = this.messages.push({ role: 'ai', content: '' }) - 1;

                    try {
                        const response = await fetch('/ask', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ question })
                        });

                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();
                        this.isThinking = false;

                        while (true) {
                            const { value, done } = await reader.read();
                            if (done) break;

                            const text = decoder.decode(value);
                            const lines = text.split('\n');

                            lines.forEach(line => {
                                if (line.trim().startsWith('data: ')) {
                                    try {
                                        const data = JSON.parse(line.substring(6));
                                        this.messages[aiMsgIndex].content += data.chunk;
                                        // Scroll as text appears
                                        this.scrollToBottom();
                                    } catch (e) { }
                                }
                            });
                        }
                    } catch (error) {
                        this.messages[aiMsgIndex].content = "Error: Connection lost.";
                        this.isThinking = false;
                    }
                }
            }
        }
    </script>
</body>

</html>